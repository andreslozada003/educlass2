<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Supplier;

class InventoryController extends Controller
{
    /**
     * Display inventory overview.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        // Filter by search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', 0)->where('is_service', false);
            } elseif ($request->stock_status === 'in') {
                $query->where('stock_quantity', '>', 0);
            }
        }

        $products = $query->active()->physical()->paginate(20)->withQueryString();
        
        // Statistics
        $totalProducts = Product::active()->physical()->count();
        $lowStockCount = Product::active()->lowStock()->count();
        $outOfStockCount = Product::active()->where('stock_quantity', 0)->where('is_service', false)->count();
        $totalInventoryValue = Product::active()->physical()
            ->selectRaw('SUM(stock_quantity * purchase_price) as total_value')
            ->first()
            ->total_value ?? 0;

        return view('inventory.index', compact(
            'products',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalInventoryValue'
        ));
    }

    /**
     * Display inventory movements.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function movements(Request $request)
    {
        $query = InventoryMovement::with(['product', 'user', 'supplier']);

        // Filter by product
        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(20)->withQueryString();
        $products = Product::active()->orderBy('name')->get();

        return view('inventory.movements', compact('movements', 'products'));
    }

    /**
     * Show the form for creating an inventory entry.
     *
     * @return \Illuminate\View\View
     */
    public function createEntry()
    {
        $products = Product::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('inventory.create-entry', compact('products', 'suppliers'));
    }

    /**
     * Store an inventory entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeEntry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'document_number' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = Product::findOrFail($request->product_id);
        
        $product->updateStock(
            $request->quantity,
            'entry',
            $request->reason ?? 'Entrada de inventario'
        );

        // Update the movement with additional info
        $movement = InventoryMovement::where('product_id', $product->id)
            ->where('type', 'entry')
            ->latest()
            ->first();
        
        if ($movement) {
            $movement->update([
                'supplier_id' => $request->supplier_id,
                'document_number' => $request->document_number,
                'unit_cost' => $request->unit_cost,
                'total_cost' => $request->unit_cost * $request->quantity,
                'notes' => $request->notes,
            ]);
        }

        return redirect()->route('inventory.movements')
            ->with('success', 'Entrada de inventario registrada correctamente.');
    }

    /**
     * Show the form for creating an inventory exit.
     *
     * @return \Illuminate\View\View
     */
    public function createExit()
    {
        $products = Product::active()->inStock()->orderBy('name')->get();

        return view('inventory.create-exit', compact('products'));
    }

    /**
     * Store an inventory exit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeExit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = Product::findOrFail($request->product_id);

        // Check stock
        if ($product->stock_quantity < $request->quantity) {
            return redirect()->back()
                ->with('error', 'Stock insuficiente. Stock actual: ' . $product->stock_quantity)
                ->withInput();
        }

        $product->updateStock(
            $request->quantity,
            'exit',
            $request->reason
        );

        return redirect()->route('inventory.movements')
            ->with('success', 'Salida de inventario registrada correctamente.');
    }

    /**
     * Show the form for adjusting inventory.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function adjust(Product $product)
    {
        return view('inventory.adjust', compact('product'));
    }

    /**
     * Store inventory adjustment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAdjustment(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product->updateStock(
            $request->quantity,
            'adjustment',
            $request->reason
        );

        return redirect()->route('inventory.index')
            ->with('success', 'Ajuste de inventario registrado correctamente.');
    }

    /**
     * Display the kardex for a product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function kardex(Product $product)
    {
        $movements = InventoryMovement::with(['user', 'supplier'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('inventory.kardex', compact('product', 'movements'));
    }

    /**
     * Get low stock products (AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lowStock()
    {
        $products = Product::active()
            ->lowStock()
            ->with('category')
            ->get(['id', 'name', 'sku', 'stock_quantity', 'min_stock']);

        return response()->json($products);
    }
}
