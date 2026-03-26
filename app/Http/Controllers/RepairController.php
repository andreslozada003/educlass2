<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Repair;
use App\Models\Customer;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class RepairController extends Controller
{
    /**
     * Display a listing of repairs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Repair::with(['customer', 'technician']);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('repair_code', 'like', "%{$search}%")
                  ->orWhere('imei', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
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

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by technician
        if ($request->filled('technician')) {
            $query->where('technician_id', $request->technician);
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $repairs = $query->paginate(20)->withQueryString();
        $technicians = User::role('technician')->active()->get();

        return view('repairs.index', compact('repairs', 'technicians'));
    }

    /**
     * Show the form for creating a new repair.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $customers = Customer::active()->orderBy('first_name')->get();
        $technicians = User::role('technician')->active()->get();

        return view('repairs.create', compact('customers', 'technicians'));
    }

    /**
     * Store a newly created repair.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'technician_id' => 'nullable|exists:users,id',
            'device_type' => 'required|in:iphone,android,tablet,other',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'storage' => 'nullable|string|max:50',
            'reported_issue' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_delivery_date' => 'nullable|date',
            'accessories_received' => 'nullable|string',
            'device_condition' => 'nullable|array',
            'customer_notes' => 'nullable|string',
            'has_warranty' => 'boolean',
            'warranty_days' => 'nullable|integer|min:0',
        ], [
            'customer_id.required' => 'El cliente es obligatorio.',
            'device_type.required' => 'El tipo de dispositivo es obligatorio.',
            'brand.required' => 'La marca es obligatoria.',
            'model.required' => 'El modelo es obligatorio.',
            'reported_issue.required' => 'El problema reportado es obligatorio.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $repair = Repair::create([
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'technician_id' => $request->technician_id,
                'device_type' => $request->device_type,
                'brand' => $request->brand,
                'model' => $request->model,
                'imei' => $request->imei,
                'serial_number' => $request->serial_number,
                'color' => $request->color,
                'storage' => $request->storage,
                'reported_issue' => $request->reported_issue,
                'priority' => $request->priority,
                'estimated_cost' => $request->estimated_cost,
                'estimated_delivery_date' => $request->estimated_delivery_date,
                'accessories_received' => $request->accessories_received,
                'device_condition' => $request->device_condition,
                'customer_notes' => $request->customer_notes,
                'has_warranty' => $request->boolean('has_warranty'),
                'warranty_days' => $request->warranty_days ?? 30,
            ]);

            DB::commit();

            return redirect()->route('repairs.show', $repair)
                ->with('success', 'Reparación registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar la reparación: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified repair.
     *
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\View\View
     */
    public function show(Repair $repair)
    {
        $repair->load(['customer', 'user', 'technician']);

        return view('repairs.show', compact('repair'));
    }

    /**
     * Show the form for editing the specified repair.
     *
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\View\View
     */
    public function edit(Repair $repair)
    {
        $customers = Customer::active()->orderBy('first_name')->get();
        $technicians = User::role('technician')->active()->get();

        return view('repairs.edit', compact('repair', 'customers', 'technicians'));
    }

    /**
     * Update the specified repair.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Repair $repair)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'technician_id' => 'nullable|exists:users,id',
            'device_type' => 'required|in:iphone,android,tablet,other',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'imei' => 'nullable|string|max:50',
            'serial_number' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'storage' => 'nullable|string|max:50',
            'reported_issue' => 'required|string',
            'diagnosis' => 'nullable|string',
            'solution' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'estimated_cost' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'estimated_delivery_date' => 'nullable|date',
            'accessories_received' => 'nullable|string',
            'device_condition' => 'nullable|array',
            'customer_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'has_warranty' => 'boolean',
            'warranty_days' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $repair->update([
                'customer_id' => $request->customer_id,
                'technician_id' => $request->technician_id,
                'device_type' => $request->device_type,
                'brand' => $request->brand,
                'model' => $request->model,
                'imei' => $request->imei,
                'serial_number' => $request->serial_number,
                'color' => $request->color,
                'storage' => $request->storage,
                'reported_issue' => $request->reported_issue,
                'diagnosis' => $request->diagnosis,
                'solution' => $request->solution,
                'priority' => $request->priority,
                'estimated_cost' => $request->estimated_cost,
                'parts_cost' => $request->parts_cost ?? 0,
                'labor_cost' => $request->labor_cost ?? 0,
                'estimated_delivery_date' => $request->estimated_delivery_date,
                'accessories_received' => $request->accessories_received,
                'device_condition' => $request->device_condition,
                'customer_notes' => $request->customer_notes,
                'notes' => $request->notes,
                'has_warranty' => $request->boolean('has_warranty'),
                'warranty_days' => $request->warranty_days ?? 30,
            ]);

            // Recalculate total cost
            $repair->calculateTotalCost();

            DB::commit();

            return redirect()->route('repairs.show', $repair)
                ->with('success', 'Reparación actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la reparación: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update repair status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Repair $repair)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:received,diagnosing,waiting_parts,in_repair,repaired,ready,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $oldStatus = $repair->status;
        $newStatus = $request->status;

        // Validate status transitions
        $validTransitions = [
            'received' => ['diagnosing', 'cancelled'],
            'diagnosing' => ['waiting_parts', 'in_repair', 'cancelled'],
            'waiting_parts' => ['in_repair', 'cancelled'],
            'in_repair' => ['repaired', 'cancelled'],
            'repaired' => ['ready', 'cancelled'],
            'ready' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if ($oldStatus !== $newStatus && !in_array($newStatus, $validTransitions[$oldStatus] ?? [])) {
            return redirect()->back()
                ->with('error', 'Transición de estado no válida.');
        }

        $repair->status = $newStatus;
        $repair->save();

        return redirect()->route('repairs.show', $repair)
            ->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Add advance payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addPayment(Request $request, Repair $repair)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $repair->addAdvancePayment($request->amount);

        return redirect()->route('repairs.show', $repair)
            ->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Generate and download repair order PDF.
     *
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\Response
     */
    public function order(Repair $repair)
    {
        $repair->load(['customer', 'user', 'technician']);

        $pdf = PDF::loadView('repairs.order', compact('repair'));
        
        return $pdf->download("Orden-{$repair->repair_code}.pdf");
    }

    /**
     * Generate and download warranty certificate PDF.
     *
     * @param  \App\Models\Repair  $repair
     * @return \Illuminate\Http\Response
     */
    public function warranty(Repair $repair)
    {
        if (!$repair->has_warranty || empty($repair->warranty_code)) {
            return redirect()->back()
                ->with('error', 'Esta reparación no tiene garantía.');
        }

        $repair->load(['customer']);

        $pdf = PDF::loadView('repairs.warranty', compact('repair'));
        
        return $pdf->download("Garantia-{$repair->warranty_code}.pdf");
    }

    /**
     * Get repair statistics (AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $stats = [
            'pending' => Repair::pending()->count(),
            'ready' => Repair::where('status', 'ready')->count(),
            'overdue' => Repair::overdue()->count(),
            'today' => Repair::whereDate('created_at', today())->count(),
            'by_status' => Repair::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];

        return response()->json($stats);
    }
}
