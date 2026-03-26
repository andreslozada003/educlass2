<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Repair;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;
use App\Exports\ProductsExport;
use App\Exports\RepairsExport;

class ReportController extends Controller
{
    /**
     * Show reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Sales report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function sales(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query = Sale::with(['customer', 'user'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Summary statistics
        $summary = [
            'total_sales' => $sales->where('status', 'completed')->count(),
            'total_amount' => $sales->where('status', 'completed')->sum('total'),
            'total_profit' => $sales->where('status', 'completed')->sum('profit'),
            'total_tax' => $sales->where('status', 'completed')->sum('tax_amount'),
            'total_discount' => $sales->where('status', 'completed')->sum('discount_amount'),
            'average_sale' => $sales->where('status', 'completed')->avg('total') ?? 0,
        ];

        // Sales by payment method
        $salesByPaymentMethod = $sales->where('status', 'completed')
            ->groupBy('payment_method')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total'),
                ];
            });

        // Sales by day
        $salesByDay = $sales->where('status', 'completed')
            ->groupBy(function ($sale) {
                return $sale->created_at->format('Y-m-d');
            })
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total'),
                    'profit' => $group->sum('profit'),
                ];
            });

        // Export to PDF
        if ($request->get('export') === 'pdf') {
            $pdf = PDF::loadView('reports.sales-pdf', compact('sales', 'summary', 'salesByPaymentMethod', 'salesByDay', 'dateFrom', 'dateTo'));
            return $pdf->download("Reporte-Ventas-{$dateFrom}-al-{$dateTo}.pdf");
        }

        // Export to Excel
        if ($request->get('export') === 'excel') {
            return Excel::download(new SalesExport($sales), "Reporte-Ventas-{$dateFrom}-al-{$dateTo}.xlsx");
        }

        return view('reports.sales', compact('sales', 'summary', 'salesByPaymentMethod', 'salesByDay', 'dateFrom', 'dateTo'));
    }

    /**
     * Products report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function products(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Best selling products
        $bestSellingProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total) as total_revenue'),
                DB::raw('SUM(sale_items.profit) as total_profit')
            )
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->get();

        // Low stock products
        $lowStockProducts = Product::active()
            ->lowStock()
            ->with('category')
            ->get();

        // Inventory valuation
        $inventoryValuation = Product::active()
            ->physical()
            ->select(
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(stock_quantity) as total_units'),
                DB::raw('SUM(stock_quantity * purchase_price) as total_cost'),
                DB::raw('SUM(stock_quantity * sale_price) as total_value')
            )
            ->first();

        // Export to PDF
        if ($request->get('export') === 'pdf') {
            $pdf = PDF::loadView('reports.products-pdf', compact('bestSellingProducts', 'lowStockProducts', 'inventoryValuation', 'dateFrom', 'dateTo'));
            return $pdf->download("Reporte-Productos-{$dateFrom}-al-{$dateTo}.pdf");
        }

        // Export to Excel
        if ($request->get('export') === 'excel') {
            return Excel::download(new ProductsExport($bestSellingProducts), "Reporte-Productos-{$dateFrom}-al-{$dateTo}.xlsx");
        }

        return view('reports.products', compact('bestSellingProducts', 'lowStockProducts', 'inventoryValuation', 'dateFrom', 'dateTo'));
    }

    /**
     * Repairs report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function repairs(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $query = Repair::with(['customer', 'technician'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('technician')) {
            $query->where('technician_id', $request->technician);
        }

        $repairs = $query->orderBy('created_at', 'desc')->get();

        // Summary statistics
        $summary = [
            'total_repairs' => $repairs->count(),
            'delivered' => $repairs->where('status', 'delivered')->count(),
            'total_revenue' => $repairs->where('status', 'delivered')->sum('total_cost'),
            'total_profit' => $repairs->where('status', 'delivered')->sum('total_cost') - $repairs->where('status', 'delivered')->sum('parts_cost'),
            'average_cost' => $repairs->where('status', 'delivered')->avg('total_cost') ?? 0,
        ];

        // Repairs by status
        $repairsByStatus = $repairs->groupBy('status')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_cost'),
                ];
            });

        // Repairs by technician
        $repairsByTechnician = $repairs->whereNotNull('technician_id')
            ->groupBy('technician_id')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->technician->name,
                    'count' => $group->count(),
                    'amount' => $group->sum('total_cost'),
                ];
            });

        // Export to PDF
        if ($request->get('export') === 'pdf') {
            $pdf = PDF::loadView('reports.repairs-pdf', compact('repairs', 'summary', 'repairsByStatus', 'repairsByTechnician', 'dateFrom', 'dateTo'));
            return $pdf->download("Reporte-Reparaciones-{$dateFrom}-al-{$dateTo}.pdf");
        }

        // Export to Excel
        if ($request->get('export') === 'excel') {
            return Excel::download(new RepairsExport($repairs), "Reporte-Reparaciones-{$dateFrom}-al-{$dateTo}.xlsx");
        }

        return view('reports.repairs', compact('repairs', 'summary', 'repairsByStatus', 'repairsByTechnician', 'dateFrom', 'dateTo'));
    }

    /**
     * Financial report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function financial(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Sales
        $sales = Sale::completed()
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $totalSales = $sales->sum('total');
        $totalProfit = $sales->sum('profit');

        // Expenses
        $expenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->where('is_active', true)
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // Repairs
        $repairs = Repair::where('status', 'delivered')
            ->whereBetween('delivered_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        $totalRepairs = $repairs->sum('total_cost');

        // Net profit
        $netProfit = $totalProfit + ($totalRepairs - $repairs->sum('parts_cost')) - $totalExpenses;

        // Expenses by category
        $expensesByCategory = $expenses->groupBy('category_id')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->category->name,
                    'amount' => $group->sum('amount'),
                ];
            });

        // Daily summary
        $dailySummary = [];
        $currentDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            
            $daySales = $sales->whereBetween('created_at', [$dateStr . ' 00:00:00', $dateStr . ' 23:59:59']);
            $dayExpenses = $expenses->where('expense_date', $dateStr);
            
            $dailySummary[$dateStr] = [
                'sales' => $daySales->sum('total'),
                'profit' => $daySales->sum('profit'),
                'expenses' => $dayExpenses->sum('amount'),
                'net' => $daySales->sum('profit') - $dayExpenses->sum('amount'),
            ];
            
            $currentDate->addDay();
        }

        // Export to PDF
        if ($request->get('export') === 'pdf') {
            $pdf = PDF::loadView('reports.financial-pdf', compact(
                'totalSales', 'totalProfit', 'totalExpenses', 'totalRepairs', 'netProfit',
                'expensesByCategory', 'dailySummary', 'dateFrom', 'dateTo'
            ));
            return $pdf->download("Reporte-Financiero-{$dateFrom}-al-{$dateTo}.pdf");
        }

        return view('reports.financial', compact(
            'totalSales', 'totalProfit', 'totalExpenses', 'totalRepairs', 'netProfit',
            'expensesByCategory', 'dailySummary', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Customers report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function customers(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Top customers
        $topCustomers = Customer::withCount(['sales' => function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'completed')
                      ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            }])
            ->withSum(['sales' => function ($query) use ($dateFrom, $dateTo) {
                $query->where('status', 'completed')
                      ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            }], 'total')
            ->having('sales_count', '>', 0)
            ->orderByDesc('sales_sum_total')
            ->limit(50)
            ->get();

        // New customers
        $newCustomers = Customer::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->count();

        return view('reports.customers', compact('topCustomers', 'newCustomers', 'dateFrom', 'dateTo'));
    }
}
