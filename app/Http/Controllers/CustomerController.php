<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Customer::query();

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
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $customers = $query->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'document_type' => 'required|in:CC,CE,NIT,PP,RUT',
            'document_number' => 'nullable|string|max:20|unique:customers',
            'email' => 'nullable|email|max:100|unique:customers',
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Customer::create($request->all());

        return redirect()->route('customers.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    /**
     * Display the specified customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\View\View
     */
    public function show(Customer $customer)
    {
        $customer->load(['sales' => function ($query) {
            $query->latest()->limit(10);
        }, 'repairs' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\View\View
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'document_type' => 'required|in:CC,CE,NIT,PP,RUT',
            'document_number' => 'nullable|string|max:20|unique:customers,document_number,' . $customer->id,
            'email' => 'nullable|email|max:100|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('is_active');
        $data['is_active'] = $request->boolean('is_active', true);

        $customer->update($data);

        return redirect()->route('customers.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has sales or repairs
        if ($customer->sales()->count() > 0 || $customer->repairs()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene ventas o reparaciones asociadas.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    /**
     * Search customers (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $customers = Customer::active()
            ->when($search, function ($query) use ($search) {
                $query->search($search);
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'document_number', 'phone', 'email']);

        return response()->json($customers);
    }
}
