<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\InventoryMovement;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
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

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'low_stock') {
                $query->active()->lowStock();
            } elseif ($request->status === 'out_of_stock') {
                $query->active()->where('stock_quantity', 0)->where('is_service', false);
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'service') {
                $query->services();
            } elseif ($request->type === 'product') {
                $query->physical();
            }
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();

        return view('products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:50|unique:products',
            'barcode' => 'nullable|string|max:50|unique:products',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'model' => 'nullable|string|max:100',
            'imei' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|gte:purchase_price',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'has_warranty' => 'boolean',
            'warranty_days' => 'nullable|integer|min:0',
            'is_service' => 'boolean',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'purchase_price.required' => 'El precio de compra es obligatorio.',
            'sale_price.required' => 'El precio de venta es obligatorio.',
            'sale_price.gte' => 'El precio de venta debe ser mayor o igual al precio de compra.',
            'sku.unique' => 'El SKU ya está en uso.',
            'barcode.unique' => 'El código de barras ya está en uso.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['image', 'has_warranty', 'is_service', 'is_active']);
        
        // Handle boolean fields
        $data['has_warranty'] = $request->boolean('has_warranty');
        $data['is_service'] = $request->boolean('is_service');
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('products', $imageName, 'public');
            $data['image'] = 'products/' . $imageName;
        }

        $product = Product::create($data);

        // Create initial inventory movement if stock > 0
        if ($product->stock_quantity > 0) {
            InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'entry',
                'quantity' => $product->stock_quantity,
                'stock_before' => 0,
                'stock_after' => $product->stock_quantity,
                'unit_cost' => $product->purchase_price,
                'total_cost' => $product->purchase_price * $product->stock_quantity,
                'reason' => 'Stock inicial',
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    /**
     * Display the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand']);
        
        // Get inventory movements
        $movements = InventoryMovement::with(['user', 'supplier'])
            ->where('product_id', $product->id)
            ->latest()
            ->paginate(10);
        
        // Get sales history
        $sales = $product->saleItems()
            ->with(['sale.customer'])
            ->latest()
            ->paginate(10);

        return view('products.show', compact('product', 'movements', 'sales'));
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'model' => 'nullable|string|max:100',
            'imei' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0|gte:purchase_price',
            'wholesale_price' => 'nullable|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'unit' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'has_warranty' => 'boolean',
            'warranty_days' => 'nullable|integer|min:0',
            'is_service' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['image', 'has_warranty', 'is_service', 'is_active']);
        
        // Handle boolean fields
        $data['has_warranty'] = $request->boolean('has_warranty');
        $data['is_service'] = $request->boolean('is_service');
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('products', $imageName, 'public');
            $data['image'] = 'products/' . $imageName;
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Product $product)
    {
        // Check if product has sales
        if ($product->saleItems()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'No se puede eliminar el producto porque tiene ventas asociadas.');
        }

        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Adjust product stock.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adjustStock(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $newQuantity = $request->quantity;
        $oldQuantity = $product->stock_quantity;
        
        if ($newQuantity != $oldQuantity) {
            $product->updateStock($newQuantity, 'adjustment', $request->reason);
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Stock ajustado correctamente.');
    }

    /**
     * Get product by barcode or SKU (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByBarcode(Request $request)
    {
        $search = $request->get('search');
        
        $product = Product::active()
            ->where(function ($query) use ($search) {
                $query->where('barcode', $search)
                      ->orWhere('sku', $search);
            })
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'sale_price' => $product->sale_price,
            'stock_quantity' => $product->stock_quantity,
            'is_service' => $product->is_service,
            'image_url' => $product->image_url,
        ]);
    }

    /**
     * Search products (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $products = Product::active()
            ->when($search, function ($query) use ($search) {
                $query->search($search);
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'sale_price', 'stock_quantity', 'is_service', 'image']);

        return response()->json($products);
    }
}
