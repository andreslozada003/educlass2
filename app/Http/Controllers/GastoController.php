<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Reparacion;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Venta;
use App\Support\GastosCatalogos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GastoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ver gastos')->only(['index', 'lista', 'show', 'recurrentes', 'reportes', 'aprobaciones']);
        $this->middleware('can:crear gastos')->only(['create', 'store', 'duplicar', 'generarRecurrente']);
        $this->middleware('can:editar gastos')->only(['edit', 'update', 'marcarPagado']);
        $this->middleware('can:eliminar gastos')->only(['destroy']);
        $this->middleware('can:aprobar gastos')->only(['aprobar', 'rechazar']);
    }

    public function index()
    {
        $this->procesarRecurrentesPendientes();

        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        $gastosMes = $this->expenseQuery()
            ->whereBetween('expense_date', [$inicioMes, $finMes])
            ->get();

        $gastosMesAnterior = (float) $this->expenseQuery()
            ->whereBetween('expense_date', [$inicioMesAnterior, $finMesAnterior])
            ->sum('amount');

        $totalMes = (float) $gastosMes->sum('amount');
        $comparativo = [
            'monto_anterior' => $gastosMesAnterior,
            'diferencia' => $totalMes - $gastosMesAnterior,
            'porcentaje' => $gastosMesAnterior > 0
                ? (($totalMes - $gastosMesAnterior) / $gastosMesAnterior) * 100
                : 0,
        ];

        $gastosHoy = (float) $this->expenseQuery()
            ->whereDate('expense_date', today())
            ->sum('amount');

        $pendientes = $this->expenseQuery()->pendingPayment()->count();
        $totalPorPagar = (float) $this->expenseQuery()->pendingPayment()->get()->sum('pending_balance');

        $recurrentesProximos = Expense::with(['category', 'supplier'])
            ->active()
            ->templates()
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '>=', today())
            ->whereDate('next_due_date', '<=', today()->copy()->addDays(15))
            ->orderBy('next_due_date')
            ->get();

        $gastosPorCategoria = $gastosMes
            ->groupBy(fn ($gasto) => $gasto->category?->name ?? 'Sin categoria')
            ->map(fn ($items, $nombre) => [
                'nombre' => $nombre,
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $gastosPorMetodo = $gastosMes
            ->groupBy(fn ($gasto) => $gasto->payment_method_label)
            ->map(fn ($items, $nombre) => [
                'nombre' => $nombre,
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $gastosPorSucursal = $gastosMes
            ->groupBy(fn ($gasto) => $gasto->branch_name ?: 'Principal')
            ->map(fn ($items, $nombre) => [
                'nombre' => $nombre,
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $gastosDiarios = collect();
        $cursor = $inicioMes->copy();
        while ($cursor->lte($finMes)) {
            $clave = $cursor->format('Y-m-d');
            $gastosDiarios->push([
                'fecha' => $clave,
                'total' => (float) $gastosMes
                    ->filter(fn ($gasto) => optional($gasto->expense_date)->format('Y-m-d') === $clave)
                    ->sum('amount'),
            ]);
            $cursor->addDay();
        }

        $alertas = [
            'por_vencer' => $this->expenseQuery()
                ->pendingPayment()
                ->whereNotNull('due_date')
                ->whereDate('due_date', '>=', today())
                ->whereDate('due_date', '<=', today()->copy()->addDays(5))
                ->count(),
            'sin_comprobante' => $this->expenseQuery()
                ->whereNull('receipt_image')
                ->whereNull('invoice_number')
                ->count(),
            'sin_aprobar' => Expense::active()->where('approval_status', 'pending')->count(),
            'fuera_presupuesto' => $this->categoriasFueraPresupuesto($inicioMes, $finMes)->count(),
        ];

        $ventasMes = (float) Venta::where('estado', 'pagada')
            ->whereBetween('fecha_venta', [$inicioMes, $finMes])
            ->sum('total');

        $reparacionesMes = (float) Reparacion::where('estado', 'entregado')
            ->whereBetween('fecha_entrega', [$inicioMes, $finMes])
            ->sum('costo_final');

        $resumenOperacion = [
            'ventas' => $ventasMes,
            'reparaciones' => $reparacionesMes,
            'gastos' => $totalMes,
            'utilidad' => ($ventasMes + $reparacionesMes) - $totalMes,
        ];

        $categoriasFueraPresupuesto = $this->categoriasFueraPresupuesto($inicioMes, $finMes);

        $ultimosGastos = $this->expenseQuery(true)
            ->latest('expense_date')
            ->limit(8)
            ->get();

        return view('gastos.index', compact(
            'gastosHoy',
            'totalMes',
            'pendientes',
            'totalPorPagar',
            'comparativo',
            'recurrentesProximos',
            'gastosPorCategoria',
            'gastosPorMetodo',
            'gastosPorSucursal',
            'gastosDiarios',
            'alertas',
            'resumenOperacion',
            'categoriasFueraPresupuesto',
            'ultimosGastos'
        ));
    }

    public function lista(Request $request)
    {
        $this->procesarRecurrentesPendientes();

        $query = $this->expenseQuery($request->filled('payment_status') && $request->payment_status === 'cancelled');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhere('expense_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->integer('subcategory_id'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        if ($request->filled('responsible_user_id')) {
            $query->where('responsible_user_id', $request->integer('responsible_user_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        } else {
            $query->where('payment_status', '!=', 'cancelled');
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('payment_source')) {
            $query->where('payment_source', $request->payment_source);
        }

        if ($request->filled('branch_name')) {
            $query->where('branch_name', $request->branch_name);
        }

        if ($request->boolean('solo_recurrentes')) {
            $query->where('is_recurring', true);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('expense_date', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('expense_date', '<=', $request->fecha_fin);
        }

        $gastosFiltrados = (clone $query)->get();
        $gastos = (clone $query)
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        $resumen = [
            'registros' => $gastosFiltrados->count(),
            'total' => (float) $gastosFiltrados->sum('amount'),
            'pagado' => (float) $gastosFiltrados->sum('paid_amount'),
            'pendiente' => (float) $gastosFiltrados->sum('pending_balance'),
        ];

        $categorias = ExpenseCategory::active()->main()->with('children')->orderBy('name')->get();
        $subcategorias = ExpenseCategory::active()->whereNotNull('parent_id')->orderBy('name')->get();
        $proveedores = Supplier::active()->orderBy('name')->get();
        $responsables = User::active()->orderBy('name')->get();
        $sucursales = Expense::query()
            ->whereNotNull('branch_name')
            ->where('branch_name', '!=', '')
            ->orderBy('branch_name')
            ->distinct()
            ->pluck('branch_name');

        return view('gastos.lista', compact(
            'gastos',
            'resumen',
            'categorias',
            'subcategorias',
            'proveedores',
            'responsables',
            'sucursales'
        ));
    }

    public function create()
    {
        return view('gastos.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateExpense($request);

        $gasto = DB::transaction(function () use ($request, $validated) {
            $data = $this->buildExpenseData($request, $validated);

            if ($request->hasFile('receipt_image')) {
                $data['receipt_image'] = $request->file('receipt_image')->store('gastos', 'public');
            }

            return Expense::create($data);
        });

        return redirect()->route('gastos.show', $gasto)
            ->with('success', 'Gasto registrado correctamente.');
    }

    public function show(Expense $gasto)
    {
        $gasto->load([
            'category.parent',
            'subcategory',
            'supplier.frequentCategory',
            'user',
            'responsibleUser',
            'approver',
            'recurringSource',
            'generatedExpenses' => function ($query) {
                $query->latest('expense_date')->limit(12);
            },
        ]);

        $historialProveedor = collect();
        if ($gasto->supplier_id) {
            $historialProveedor = Expense::with(['category', 'responsibleUser'])
                ->active()
                ->where('supplier_id', $gasto->supplier_id)
                ->where('id', '!=', $gasto->id)
                ->where('payment_status', '!=', 'cancelled')
                ->latest('expense_date')
                ->limit(8)
                ->get();
        }

        return view('gastos.show', compact('gasto', 'historialProveedor'));
    }

    public function edit(Expense $gasto)
    {
        return view('gastos.edit', $this->formData($gasto));
    }

    public function update(Request $request, Expense $gasto)
    {
        $validated = $this->validateExpense($request, $gasto);

        DB::transaction(function () use ($request, $validated, $gasto) {
            $data = $this->buildExpenseData($request, $validated, $gasto);

            if ($request->hasFile('receipt_image')) {
                if ($gasto->receipt_image) {
                    Storage::disk('public')->delete($gasto->receipt_image);
                }

                $data['receipt_image'] = $request->file('receipt_image')->store('gastos', 'public');
            }

            $gasto->update($data);
        });

        return redirect()->route('gastos.show', $gasto)
            ->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Expense $gasto)
    {
        $gasto->update([
            'payment_status' => 'cancelled',
            'paid_amount' => 0,
            'paid_date' => null,
        ]);

        return redirect()->route('gastos.lista')
            ->with('warning', 'El gasto fue anulado y quedo en historial.');
    }

    public function marcarPagado(Request $request, Expense $gasto)
    {
        $request->validate([
            'paid_date' => 'nullable|date',
            'payment_source' => 'nullable|in:' . implode(',', array_keys(GastosCatalogos::fuentesPago())),
            'reference_number' => 'nullable|string|max:100',
        ]);

        if ($gasto->approval_status === 'rejected') {
            return back()->with('error', 'No puedes pagar un gasto rechazado.');
        }

        if ($gasto->approval_status === 'pending' && ! auth()->user()->can('aprobar gastos')) {
            return back()->with('error', 'Este gasto aun esta pendiente de aprobacion.');
        }

        $gasto->update([
            'payment_status' => 'paid',
            'paid_amount' => $gasto->amount,
            'paid_date' => $request->input('paid_date', now()->toDateString()),
            'payment_source' => $request->input('payment_source', $gasto->payment_source),
            'reference_number' => $request->input('reference_number', $gasto->reference_number),
        ]);

        return back()->with('success', 'El gasto fue marcado como pagado.');
    }

    public function duplicar(Expense $gasto)
    {
        $nuevoGasto = $gasto->replicate([
            'expense_number',
            'receipt_image',
            'invoice_number',
            'receipt_number',
            'reference_number',
            'paid_amount',
            'paid_date',
            'approved_by',
            'approved_at',
            'recurring_source_id',
        ]);

        $nuevoGasto->expense_number = null;
        $nuevoGasto->expense_date = today();
        $nuevoGasto->due_date = null;
        $nuevoGasto->payment_status = 'pending';
        $nuevoGasto->paid_amount = 0;
        $nuevoGasto->paid_date = null;
        $nuevoGasto->receipt_image = null;
        $nuevoGasto->approval_status = $gasto->category?->requires_approval ? 'pending' : 'not_required';
        $nuevoGasto->is_recurring = false;
        $nuevoGasto->recurring_period = null;
        $nuevoGasto->next_due_date = null;
        $nuevoGasto->save();

        return redirect()->route('gastos.edit', $nuevoGasto)
            ->with('info', 'Se creo una copia del gasto para que la ajustes antes de guardar.');
    }

    public function recurrentes()
    {
        $this->procesarRecurrentesPendientes();

        $recurrentes = Expense::with(['category', 'supplier', 'responsibleUser'])
            ->active()
            ->templates()
            ->orderByRaw('CASE WHEN next_due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('next_due_date')
            ->paginate(20);

        $proximos = Expense::with(['category', 'supplier'])
            ->active()
            ->templates()
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '>=', today())
            ->whereDate('next_due_date', '<=', today()->copy()->addDays(30))
            ->orderBy('next_due_date')
            ->get();

        $ultimosGenerados = Expense::with(['category', 'supplier', 'recurringSource'])
            ->active()
            ->whereNotNull('recurring_source_id')
            ->latest('expense_date')
            ->limit(12)
            ->get();

        return view('gastos.recurrentes', compact('recurrentes', 'proximos', 'ultimosGenerados'));
    }

    public function generarRecurrente(Expense $gasto)
    {
        if (! $gasto->is_recurring || ! $gasto->recurring_period) {
            return back()->with('error', 'Este gasto no esta configurado como recurrente.');
        }

        $fecha = $gasto->next_due_date?->copy()
            ?? $this->siguienteFechaRecurrente(
                $gasto->due_date?->copy() ?? $gasto->expense_date?->copy() ?? now(),
                $gasto->recurring_period
            );

        $nuevoGasto = $this->generarDesdePlantilla($gasto, $fecha);
        $gasto->update([
            'next_due_date' => $this->siguienteFechaRecurrente($fecha, $gasto->recurring_period),
        ]);

        return redirect()->route('gastos.show', $nuevoGasto)
            ->with('success', 'Se genero el gasto recurrente correctamente.');
    }

    public function reportes(Request $request)
    {
        $this->procesarRecurrentesPendientes();

        [$fechaInicio, $fechaFin] = $this->rangoFechas($request);

        $query = $this->expenseQuery()
            ->whereBetween('expense_date', [$fechaInicio, $fechaFin]);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        if ($request->filled('branch_name')) {
            $query->where('branch_name', $request->branch_name);
        }

        $gastos = $query->get();

        $stats = [
            'total' => (float) $gastos->sum('amount'),
            'pagado' => (float) $gastos->sum('paid_amount'),
            'pendiente' => (float) $gastos->sum('pending_balance'),
            'fijos' => (float) $gastos->where('expense_type', 'fixed')->sum('amount'),
            'variables' => (float) $gastos->where('expense_type', 'variable')->sum('amount'),
            'promedio' => (float) ($gastos->avg('amount') ?? 0),
        ];

        $porCategoria = $gastos
            ->groupBy(fn ($gasto) => $gasto->category?->name ?? 'Sin categoria')
            ->map(fn ($items, $nombre) => (object) [
                'nombre' => $nombre,
                'total' => (float) $items->sum('amount'),
                'cantidad' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $porProveedor = $gastos
            ->filter(fn ($gasto) => $gasto->supplier)
            ->groupBy('supplier_id')
            ->map(fn ($items) => (object) [
                'nombre' => $items->first()->supplier->name,
                'total' => (float) $items->sum('amount'),
                'cantidad' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $porSucursal = $gastos
            ->groupBy(fn ($gasto) => $gasto->branch_name ?: 'Principal')
            ->map(fn ($items, $nombre) => (object) [
                'nombre' => $nombre,
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $porResponsable = $gastos
            ->filter(fn ($gasto) => $gasto->responsibleUser)
            ->groupBy('responsible_user_id')
            ->map(fn ($items) => (object) [
                'nombre' => $items->first()->responsibleUser->name,
                'total' => (float) $items->sum('amount'),
                'cantidad' => $items->count(),
            ])
            ->sortByDesc('total')
            ->values();

        $porMetodoPago = $gastos
            ->groupBy(fn ($gasto) => $gasto->payment_source_label)
            ->map(fn ($items, $nombre) => (object) [
                'nombre' => $nombre,
                'total' => (float) $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $flujoDiario = collect();
        $cursor = $fechaInicio->copy();
        while ($cursor->lte($fechaFin)) {
            $clave = $cursor->format('Y-m-d');
            $flujoDiario->push((object) [
                'fecha' => $clave,
                'total' => (float) $gastos
                    ->filter(fn ($gasto) => optional($gasto->expense_date)->format('Y-m-d') === $clave)
                    ->sum('amount'),
            ]);
            $cursor->addDay();
        }

        $topGastos = $gastos->sortByDesc('amount')->take(10)->values();

        $ventasPeriodo = (float) Venta::where('estado', 'pagada')
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->sum('total');

        $reparacionesPeriodo = (float) Reparacion::where('estado', 'entregado')
            ->whereBetween('fecha_entrega', [$fechaInicio, $fechaFin])
            ->sum('costo_final');

        $utilidadAproximada = ($ventasPeriodo + $reparacionesPeriodo) - $stats['total'];

        $categorias = ExpenseCategory::active()->main()->orderBy('name')->get();
        $proveedores = Supplier::active()->orderBy('name')->get();
        $sucursales = Expense::query()
            ->whereNotNull('branch_name')
            ->where('branch_name', '!=', '')
            ->orderBy('branch_name')
            ->distinct()
            ->pluck('branch_name');

        return view('gastos.reportes', compact(
            'fechaInicio',
            'fechaFin',
            'stats',
            'porCategoria',
            'porProveedor',
            'porSucursal',
            'porResponsable',
            'porMetodoPago',
            'flujoDiario',
            'topGastos',
            'ventasPeriodo',
            'reparacionesPeriodo',
            'utilidadAproximada',
            'categorias',
            'proveedores',
            'sucursales'
        ));
    }

    public function aprobaciones(Request $request)
    {
        $query = Expense::with(['category', 'supplier', 'responsibleUser', 'user', 'approver'])
            ->active()
            ->whereIn('approval_status', ['pending', 'approved', 'rejected']);

        if ($request->filled('estado')) {
            $query->where('approval_status', $request->estado);
        }

        $gastos = $query
            ->orderByRaw("CASE approval_status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        $resumen = [
            'pendientes' => Expense::active()->where('approval_status', 'pending')->count(),
            'aprobados' => Expense::active()->where('approval_status', 'approved')->count(),
            'rechazados' => Expense::active()->where('approval_status', 'rejected')->count(),
        ];

        return view('gastos.aprobaciones', compact('gastos', 'resumen'));
    }

    public function aprobar(Expense $gasto)
    {
        $gasto->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Gasto aprobado correctamente.');
    }

    public function rechazar(Request $request, Expense $gasto)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        $notaRechazo = '[' . now()->format('d/m/Y H:i') . '] Rechazado: ' . trim($request->motivo);

        $gasto->update([
            'approval_status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => trim(($gasto->notes ? $gasto->notes . PHP_EOL : '') . $notaRechazo),
        ]);

        return back()->with('warning', 'Gasto rechazado.');
    }

    protected function expenseQuery(bool $includeCancelled = false)
    {
        return Expense::with([
            'category.parent',
            'subcategory',
            'supplier',
            'responsibleUser',
            'user',
            'approver',
            'recurringSource',
        ])
            ->active()
            ->when(! $includeCancelled, function ($query) {
                $query->where('payment_status', '!=', 'cancelled');
            });
    }

    protected function formData(?Expense $gasto = null): array
    {
        $categorias = ExpenseCategory::active()
            ->main()
            ->with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return [
            'gasto' => $gasto,
            'categorias' => $categorias,
            'proveedores' => Supplier::active()->with('frequentCategory')->orderBy('name')->get(),
            'responsables' => User::active()->orderBy('name')->get(),
            'metodosPago' => GastosCatalogos::metodosPago(),
            'fuentesPago' => GastosCatalogos::fuentesPago(),
            'estadosPago' => GastosCatalogos::estadosPago(),
            'tiposGasto' => GastosCatalogos::tiposGasto(),
            'periodosRecurrentes' => GastosCatalogos::periodosRecurrentes(),
        ];
    }

    protected function validateExpense(Request $request, ?Expense $gasto = null): array
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'subcategory_id' => 'nullable|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'expense_type' => 'required|in:' . implode(',', array_keys(GastosCatalogos::tiposGasto())),
            'amount' => 'required|numeric|min:0.01',
            'payment_status' => 'required|in:' . implode(',', array_keys(GastosCatalogos::estadosPago())),
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:' . implode(',', array_keys(GastosCatalogos::metodosPago())),
            'payment_source' => 'nullable|in:' . implode(',', array_keys(GastosCatalogos::fuentesPago())),
            'reference_number' => 'nullable|string|max:100',
            'receipt_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'expense_date' => 'required|date',
            'due_date' => 'nullable|date',
            'paid_date' => 'nullable|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'branch_name' => 'nullable|string|max:120',
            'responsible_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
            'recurring_period' => 'nullable|in:' . implode(',', array_keys(GastosCatalogos::periodosRecurrentes())),
            'next_due_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        if (! empty($validated['subcategory_id'])) {
            $subcategory = ExpenseCategory::find($validated['subcategory_id']);

            if (! $subcategory || (int) $subcategory->parent_id !== (int) $validated['category_id']) {
                throw ValidationException::withMessages([
                    'subcategory_id' => 'La subcategoria no pertenece a la categoria seleccionada.',
                ]);
            }
        }

        if ($request->boolean('is_recurring') && empty($validated['recurring_period'])) {
            throw ValidationException::withMessages([
                'recurring_period' => 'Selecciona la frecuencia del gasto recurrente.',
            ]);
        }

        $paidAmount = (float) ($validated['paid_amount'] ?? 0);
        $amount = (float) $validated['amount'];

        if (($validated['payment_status'] ?? null) === 'partial' && ($paidAmount <= 0 || $paidAmount >= $amount)) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Para un gasto parcial debes ingresar un valor pagado mayor que cero y menor al monto total.',
            ]);
        }

        if (($validated['payment_status'] ?? null) === 'paid' && $amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'El monto del gasto debe ser mayor que cero.',
            ]);
        }

        return $validated;
    }

    protected function buildExpenseData(Request $request, array $validated, ?Expense $gasto = null): array
    {
        $data = $validated;
        $data['user_id'] = $gasto?->user_id ?? auth()->id();
        $data['responsible_user_id'] = $validated['responsible_user_id'] ?? auth()->id();
        $data['supplier_id'] = $validated['supplier_id'] ?? null;
        $data['subcategory_id'] = $validated['subcategory_id'] ?? null;
        $data['payment_source'] = $validated['payment_source'] ?? null;
        $data['reference_number'] = $validated['reference_number'] ?? null;
        $data['receipt_number'] = $validated['receipt_number'] ?? null;
        $data['invoice_number'] = $validated['invoice_number'] ?? null;
        $data['branch_name'] = $validated['branch_name'] ?? null;
        $data['paid_amount'] = (float) ($validated['paid_amount'] ?? 0);
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['is_active'] = $request->boolean('is_active', $gasto?->is_active ?? true);
        $data['recurring_source_id'] = $gasto?->recurring_source_id;

        if ($data['payment_status'] === 'paid') {
            $data['paid_amount'] = (float) $data['amount'];
            $data['paid_date'] = $validated['paid_date'] ?? $validated['expense_date'];
        } elseif ($data['payment_status'] === 'partial') {
            $data['paid_date'] = $validated['paid_date'] ?? null;
        } else {
            $data['paid_amount'] = 0;
            $data['paid_date'] = null;
        }

        if (in_array($data['payment_status'], ['pending', 'partial'], true)
            && ! empty($validated['due_date'])
            && Carbon::parse($validated['due_date'])->lt(today())
            && ((float) $data['amount'] - (float) $data['paid_amount']) > 0
        ) {
            $data['payment_status'] = 'overdue';
        }

        $category = ExpenseCategory::findOrFail($data['category_id']);
        if ($category->requires_approval) {
            $data['approval_status'] = auth()->user()->can('aprobar gastos') ? 'approved' : 'pending';
            $data['approved_by'] = $data['approval_status'] === 'approved' ? auth()->id() : null;
            $data['approved_at'] = $data['approval_status'] === 'approved' ? now() : null;
        } else {
            $data['approval_status'] = 'not_required';
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        if ($data['is_recurring']) {
            $data['recurring_period'] = $validated['recurring_period'];
            $data['next_due_date'] = $validated['next_due_date']
                ?? $this->siguienteFechaRecurrente(
                    Carbon::parse($validated['due_date'] ?? $validated['expense_date']),
                    $validated['recurring_period']
                )?->toDateString();
        } else {
            $data['recurring_period'] = null;
            $data['next_due_date'] = null;
        }

        return $data;
    }

    protected function rangoFechas(Request $request): array
    {
        $fechaInicio = Carbon::parse($request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay();
        $fechaFin = Carbon::parse($request->input('fecha_fin', now()->format('Y-m-d')))->endOfDay();

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        return [$fechaInicio, $fechaFin];
    }

    protected function categoriasFueraPresupuesto(Carbon $fechaInicio, Carbon $fechaFin)
    {
        return ExpenseCategory::active()
            ->main()
            ->get()
            ->map(function ($categoria) use ($fechaInicio, $fechaFin) {
                $total = Expense::query()
                    ->active()
                    ->where('payment_status', '!=', 'cancelled')
                    ->whereBetween('expense_date', [$fechaInicio, $fechaFin])
                    ->where(function ($query) use ($categoria) {
                        $query->where('category_id', $categoria->id)
                            ->orWhereIn('subcategory_id', $categoria->children()->pluck('id'));
                    })
                    ->sum('amount');

                if (! $categoria->monthly_budget || $total <= $categoria->monthly_budget) {
                    return null;
                }

                return (object) [
                    'nombre' => $categoria->name,
                    'presupuesto' => (float) $categoria->monthly_budget,
                    'total' => (float) $total,
                ];
            })
            ->filter()
            ->values();
    }

    protected function procesarRecurrentesPendientes(): void
    {
        $plantillas = Expense::query()
            ->active()
            ->templates()
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', today())
            ->get();

        foreach ($plantillas as $plantilla) {
            if (! $plantilla->recurring_period) {
                continue;
            }

            $fecha = $plantilla->next_due_date?->copy();
            while ($fecha && $fecha->lte(today())) {
                $existe = Expense::withTrashed()
                    ->where('recurring_source_id', $plantilla->id)
                    ->whereDate('expense_date', $fecha)
                    ->exists();

                if (! $existe) {
                    $this->generarDesdePlantilla($plantilla, $fecha);
                }

                $fecha = $this->siguienteFechaRecurrente($fecha, $plantilla->recurring_period);
                $plantilla->next_due_date = $fecha;
                $plantilla->save();
            }
        }
    }

    protected function generarDesdePlantilla(Expense $plantilla, Carbon $fecha): Expense
    {
        $approvalStatus = $plantilla->category?->requires_approval ? 'pending' : 'not_required';

        return Expense::create([
            'user_id' => $plantilla->user_id,
            'category_id' => $plantilla->category_id,
            'subcategory_id' => $plantilla->subcategory_id,
            'description' => $plantilla->description,
            'expense_type' => $plantilla->expense_type,
            'amount' => $plantilla->amount,
            'payment_status' => 'pending',
            'paid_amount' => 0,
            'payment_method' => $plantilla->payment_method,
            'payment_source' => $plantilla->payment_source,
            'expense_date' => $fecha->toDateString(),
            'due_date' => $fecha->toDateString(),
            'supplier_id' => $plantilla->supplier_id,
            'branch_name' => $plantilla->branch_name,
            'responsible_user_id' => $plantilla->responsible_user_id,
            'approval_status' => $approvalStatus,
            'notes' => trim(($plantilla->notes ? $plantilla->notes . PHP_EOL : '') . 'Generado automaticamente desde la plantilla ' . $plantilla->expense_number . '.'),
            'is_recurring' => false,
            'recurring_period' => null,
            'next_due_date' => null,
            'recurring_source_id' => $plantilla->id,
            'is_active' => true,
        ]);
    }

    protected function siguienteFechaRecurrente(Carbon $fecha, string $periodo): ?Carbon
    {
        return match ($periodo) {
            'daily' => $fecha->copy()->addDay(),
            'weekly' => $fecha->copy()->addWeek(),
            'monthly' => $fecha->copy()->addMonthNoOverflow(),
            'yearly' => $fecha->copy()->addYear(),
            default => null,
        };
    }
}
