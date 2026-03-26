<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ExpenseCategory;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of expense categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ExpenseCategory::query();

        // Filter by search
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('expense-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new expense category.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('expense-categories.create');
    }

    /**
     * Store a newly created expense category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|size:7',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ExpenseCategory::create($request->all());

        return redirect()->route('expense-categories.index')
            ->with('success', 'Categoría de gasto creada correctamente.');
    }

    /**
     * Show the form for editing the specified expense category.
     *
     * @param  \App\Models\ExpenseCategory  $expenseCategory
     * @return \Illuminate\View\View
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense-categories.edit', compact('expenseCategory'));
    }

    /**
     * Update the specified expense category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpenseCategory  $expenseCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|size:7',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('is_active');
        $data['is_active'] = $request->boolean('is_active', true);

        $expenseCategory->update($data);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Categoría de gasto actualizada correctamente.');
    }

    /**
     * Remove the specified expense category.
     *
     * @param  \App\Models\ExpenseCategory  $expenseCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Check if category has expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return redirect()->route('expense-categories.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene gastos asociados.');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')
            ->with('success', 'Categoría de gasto eliminada correctamente.');
    }
}
