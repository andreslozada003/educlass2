<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Filter by search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $suppliers = $query->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:100',
            'nit_rut' => 'nullable|string|max:20|unique:suppliers',
            'email' => 'nullable|email|max:100|unique:suppliers',
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Display the specified supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\View\View
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['inventoryMovements' => function ($query) {
            $query->with('product')->latest()->limit(20);
        }]);

        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\View\View
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:100',
            'nit_rut' => 'nullable|string|max:20|unique:suppliers,nit_rut,' . $supplier->id,
            'email' => 'nullable|email|max:100|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('is_active');
        $data['is_active'] = $request->boolean('is_active', true);

        $supplier->update($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Remove the specified supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has inventory movements
        if ($supplier->inventoryMovements()->count() > 0) {
            return redirect()->route('suppliers.index')
                ->with('error', 'No se puede eliminar el proveedor porque tiene movimientos de inventario asociados.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    /**
     * Search suppliers (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $suppliers = Supplier::active()
            ->when($search, function ($query) use ($search) {
                $query->search($search);
            })
            ->limit(10)
            ->get(['id', 'name', 'contact_name', 'nit_rut', 'phone']);

        return response()->json($suppliers);
    }
}
