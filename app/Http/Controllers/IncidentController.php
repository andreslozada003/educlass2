<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Incident;
use App\Models\User;

class IncidentController extends Controller
{
    /**
     * Display a listing of incidents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Incident::with(['user', 'assignedUser']);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('incident_code', 'like', "%{$search}%");
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

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $incidents = $query->paginate(20)->withQueryString();
        $users = User::active()->orderBy('name')->get();

        return view('incidents.index', compact('incidents', 'users'));
    }

    /**
     * Show the form for creating a new incident.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $users = User::active()->orderBy('name')->get();
        return view('incidents.create', compact('users'));
    }

    /**
     * Store a newly created incident.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:inventory,sale,repair,customer,system,other',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'related_type' => 'nullable|string',
            'related_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Incident::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'priority' => $request->priority,
            'assigned_to' => $request->assigned_to,
            'related_type' => $request->related_type,
            'related_id' => $request->related_id,
            'notes' => $request->notes,
            'status' => 'open',
        ]);

        return redirect()->route('incidents.index')
            ->with('success', 'Incidencia registrada correctamente.');
    }

    /**
     * Display the specified incident.
     *
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\View\View
     */
    public function show(Incident $incident)
    {
        $incident->load(['user', 'assignedUser', 'resolver']);

        return view('incidents.show', compact('incident'));
    }

    /**
     * Show the form for editing the specified incident.
     *
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\View\View
     */
    public function edit(Incident $incident)
    {
        $users = User::active()->orderBy('name')->get();
        return view('incidents.edit', compact('incident', 'users'));
    }

    /**
     * Update the specified incident.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Incident $incident)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:inventory,sale,repair,customer,system,other',
            'priority' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $incident->update($request->all());

        return redirect()->route('incidents.index')
            ->with('success', 'Incidencia actualizada correctamente.');
    }

    /**
     * Resolve the specified incident.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolve(Request $request, Incident $incident)
    {
        $validator = Validator::make($request->all(), [
            'resolution_notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!$incident->isOpen()) {
            return redirect()->back()
                ->with('error', 'Esta incidencia ya está resuelta o cerrada.');
        }

        $incident->resolve($request->resolution_notes);

        return redirect()->route('incidents.index')
            ->with('success', 'Incidencia resuelta correctamente.');
    }

    /**
     * Remove the specified incident.
     *
     * @param  \App\Models\Incident  $incident
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Incident $incident)
    {
        $incident->delete();

        return redirect()->route('incidents.index')
            ->with('success', 'Incidencia eliminada correctamente.');
    }

    /**
     * Get incident statistics (AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $stats = [
            'open' => Incident::open()->count(),
            'critical' => Incident::ofPriority('critical')->open()->count(),
            'by_type' => Incident::select('type', DB::raw('count(*) as count'))
                ->whereIn('status', ['open', 'in_progress'])
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return response()->json($stats);
    }
}
