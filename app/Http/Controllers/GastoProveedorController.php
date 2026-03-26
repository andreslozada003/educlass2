<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GastoProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver gastos')->only(['index', 'show']);
        $this->middleware('can:gestionar proveedores gastos')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = Supplier::with('frequentCategory');

        if ($request->filled('search')) {
            $query->search(trim((string) $request->search));
        }

        if ($request->filled('estado')) {
            $query->where('is_active', $request->estado === 'activo');
        }

        if ($request->filled('frequent_category_id')) {
            $query->where('frequent_category_id', $request->integer('frequent_category_id'));
        }

        $proveedores = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('gastos.proveedores.index', [
            'proveedores' => $proveedores,
            'categorias' => ExpenseCategory::active()->main()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('gastos.proveedores.create', [
            'proveedor' => null,
            'categorias' => ExpenseCategory::active()->main()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProveedor($request);
        $validated['is_active'] = $request->boolean('is_active', true);

        $proveedor = Supplier::create($validated);

        return redirect()->route('gastos.proveedores.show', $proveedor)
            ->with('success', 'Proveedor registrado correctamente.');
    }

    public function show(Supplier $proveedor)
    {
        $proveedor->load('frequentCategory');

        $gastos = Expense::with(['category', 'responsibleUser'])
            ->active()
            ->where('supplier_id', $proveedor->id)
            ->latest('expense_date')
            ->paginate(15);

        $resumen = [
            'total_gastado' => (float) Expense::active()
                ->where('supplier_id', $proveedor->id)
                ->where('payment_status', '!=', 'cancelled')
                ->sum('amount'),
            'por_pagar' => (float) Expense::active()
                ->where('supplier_id', $proveedor->id)
                ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                ->get()
                ->sum('pending_balance'),
            'pendientes' => Expense::active()
                ->where('supplier_id', $proveedor->id)
                ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                ->count(),
        ];

        return view('gastos.proveedores.show', compact('proveedor', 'gastos', 'resumen'));
    }

    public function edit(Supplier $proveedor)
    {
        return view('gastos.proveedores.edit', [
            'proveedor' => $proveedor,
            'categorias' => ExpenseCategory::active()->main()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Supplier $proveedor)
    {
        $validated = $this->validateProveedor($request, $proveedor);
        $validated['is_active'] = $request->boolean('is_active', true);

        $proveedor->update($validated);

        return redirect()->route('gastos.proveedores.show', $proveedor)
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $proveedor)
    {
        if ($proveedor->expenses()->exists()) {
            return back()->with('error', 'No puedes eliminar un proveedor con gastos asociados.');
        }

        $proveedor->delete();

        return redirect()->route('gastos.proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    protected function validateProveedor(Request $request, ?Supplier $proveedor = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:100',
            'nit_rut' => ['nullable', 'string', 'max:20', Rule::unique('suppliers', 'nit_rut')->ignore($proveedor?->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('suppliers', 'email')->ignore($proveedor?->id)],
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'frequent_category_id' => 'nullable|exists:expense_categories,id',
            'website' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
    }
}
