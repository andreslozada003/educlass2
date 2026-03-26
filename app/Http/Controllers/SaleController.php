<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user']);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('document_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $sales = $query->paginate(20)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new sale.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $customers = Customer::active()->orderBy('first_name')->get();
        $products = Product::active()->inStock()->orderBy('name')->get();
        $company = Company::getActive();

        return view('sales.create', compact('customers', 'products', 'company'));
    }

    /**
     * Store a newly created sale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,card,transfer,credit,mixed',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'items.required' => 'Debes agregar al menos un producto.',
            'items.min' => 'Debes agregar al menos un producto.',
            'items.*.product_id.required' => 'El producto es obligatorio.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1.',
            'items.*.unit_price.required' => 'El precio es obligatorio.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create sale
            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'notes' => $request->notes,
                'paid_amount' => $request->paid_amount ?? 0,
            ]);

            // Add items
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                // Check stock for physical products
                if (!$product->is_service && $product->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("Stock insuficiente para el producto: {$product->name}");
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'unit_cost' => $product->purchase_price,
                    'discount' => $itemData['discount'] ?? 0,
                ]);
            }

            // Calculate totals
            $sale->calculateTotals();

            // Calculate change
            if ($request->paid_amount > 0) {
                $sale->change_amount = max(0, $request->paid_amount - $sale->total);
                $sale->save();
            }

            DB::commit();

            // If complete immediately
            if ($request->boolean('complete_immediately')) {
                $sale->complete();
                return redirect()->route('sales.show', $sale)
                    ->with('success', 'Venta creada y completada correctamente.');
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear la venta: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified sale.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\View\View
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        $company = Company::getActive();

        return view('sales.show', compact('sale', 'company'));
    }

    /**
     * Complete the specified sale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Solo ventas pendientes pueden ser completadas.');
        }

        try {
            $sale->complete();
            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta completada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al completar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified sale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request, Sale $sale)
    {
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($sale->status === 'cancelled') {
            return redirect()->back()
                ->with('error', 'La venta ya está cancelada.');
        }

        try {
            $sale->cancel($request->cancellation_reason);
            return redirect()->route('sales.show', $sale)
                ->with('success', 'Venta cancelada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download invoice PDF.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function invoice(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        $company = Company::getActive();

        $pdf = PDF::loadView('sales.invoice', compact('sale', 'company'));
        
        return $pdf->download("Factura-{$sale->sale_number}.pdf");
    }

    /**
     * Print receipt.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\View\View
     */
    public function receipt(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);
        $company = Company::getActive();

        return view('sales.receipt', compact('sale', 'company'));
    }

    /**
     * Get sale statistics (AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        $stats = [
            'today' => [
                'count' => Sale::completed()->whereDate('created_at', $today)->count(),
                'amount' => Sale::completed()->whereDate('created_at', $today)->sum('total'),
                'profit' => Sale::completed()->whereDate('created_at', $today)->sum('profit'),
            ],
            'month' => [
                'count' => Sale::completed()->whereBetween('created_at', [$startOfMonth, now()])->count(),
                'amount' => Sale::completed()->whereBetween('created_at', [$startOfMonth, now()])->sum('total'),
                'profit' => Sale::completed()->whereBetween('created_at', [$startOfMonth, now()])->sum('profit'),
            ],
            'pending' => [
                'count' => Sale::pending()->count(),
                'amount' => Sale::pending()->sum('total'),
            ],
        ];

        return response()->json($stats);
    }
}
