<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'user', 'supplier']);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('expense_number', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->get('sort', 'expense_date');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $expenses = $query->paginate(20)->withQueryString();
        $categories = ExpenseCategory::active()->orderBy('name')->get();

        // Calculate totals
        $totalAmount = $query->sum('amount');

        return view('expenses.index', compact('expenses', 'categories', 'totalAmount'));
    }

    /**
     * Show the form for creating a new expense.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('expenses.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created expense.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer,check,other',
            'reference_number' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:50',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'expense_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_period' => 'nullable|in:daily,weekly,monthly,yearly',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['receipt_image', 'is_recurring']);
        $data['user_id'] = auth()->id();
        $data['is_recurring'] = $request->boolean('is_recurring');

        // Handle receipt image upload
        if ($request->hasFile('receipt_image')) {
            $image = $request->file('receipt_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('expenses', $imageName, 'public');
            $data['receipt_image'] = 'expenses/' . $imageName;
        }

        Expense::create($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto registrado correctamente.');
    }

    /**
     * Display the specified expense.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\View\View
     */
    public function show(Expense $expense)
    {
        $expense->load(['category', 'user', 'supplier']);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\View\View
     */
    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories', 'suppliers'));
    }

    /**
     * Update the specified expense.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer,check,other',
            'reference_number' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:50',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'expense_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_period' => 'nullable|in:daily,weekly,monthly,yearly',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['receipt_image', 'is_recurring', 'is_active']);
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle receipt image upload
        if ($request->hasFile('receipt_image')) {
            // Delete old image if exists
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            
            $image = $request->file('receipt_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('expenses', $imageName, 'public');
            $data['receipt_image'] = 'expenses/' . $imageName;
        }

        $expense->update($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto actualizado correctamente.');
    }

    /**
     * Remove the specified expense.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Expense $expense)
    {
        // Delete receipt image if exists
        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Gasto eliminado correctamente.');
    }

    /**
     * Get expense statistics (AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        $stats = [
            'today' => Expense::whereDate('expense_date', $today)->sum('amount'),
            'month' => Expense::whereBetween('expense_date', [$startOfMonth, now()])->sum('amount'),
            'by_category' => Expense::select('expense_categories.name', DB::raw('SUM(expenses.amount) as total'))
                ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
                ->whereBetween('expenses.expense_date', [$startOfMonth, now()])
                ->groupBy('expense_categories.name')
                ->pluck('total', 'name')
                ->toArray(),
        ];

        return response()->json($stats);
    }
}
