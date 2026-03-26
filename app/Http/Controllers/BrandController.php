<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;

class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Brand::query();

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

        $brands = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('brands.index', compact('brands'));
    }

    /**
     * Show the form for creating a new brand.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('brands.create');
    }

    /**
     * Store a newly created brand.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'website' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('logo');

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logo->storeAs('brands', $logoName, 'public');
            $data['logo'] = 'brands/' . $logoName;
        }

        Brand::create($data);

        return redirect()->route('brands.index')
            ->with('success', 'Marca creada correctamente.');
    }

    /**
     * Show the form for editing the specified brand.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\View\View
     */
    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    /**
     * Update the specified brand.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'website' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['logo', 'is_active']);
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            
            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logo->storeAs('brands', $logoName, 'public');
            $data['logo'] = 'brands/' . $logoName;
        }

        $brand->update($data);

        return redirect()->route('brands.index')
            ->with('success', 'Marca actualizada correctamente.');
    }

    /**
     * Remove the specified brand.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Brand $brand)
    {
        // Check if brand has products
        if ($brand->products()->count() > 0) {
            return redirect()->route('brands.index')
                ->with('error', 'No se puede eliminar la marca porque tiene productos asociados.');
        }

        // Delete logo if exists
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'Marca eliminada correctamente.');
    }
}
