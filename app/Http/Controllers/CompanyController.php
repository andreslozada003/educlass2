<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * Show the form for editing company settings.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $company = Company::first();
        
        if (!$company) {
            $company = new Company();
        }

        return view('company.edit', compact('company'));
    }

    /**
     * Update company settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'nit_rut' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100',
            'currency' => 'required|string|size:3',
            'currency_symbol' => 'required|string|max:5',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_name' => 'required|string|max:50',
            'invoice_footer' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $company = Company::first();

        $data = $request->except('logo');

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company && $company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            
            $logo = $request->file('logo');
            $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->storeAs('company', $logoName, 'public');
            $data['logo'] = 'company/' . $logoName;
        }

        if ($company) {
            $company->update($data);
        } else {
            $data['is_active'] = true;
            Company::create($data);
        }

        return redirect()->route('company.edit')
            ->with('success', 'Configuración de la empresa actualizada correctamente.');
    }

    /**
     * Get company info (AJAX).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        $company = Company::getActive();

        if (!$company) {
            return response()->json(['error' => 'No company configured'], 404);
        }

        return response()->json([
            'name' => $company->name,
            'nit_rut' => $company->nit_rut,
            'address' => $company->address,
            'phone' => $company->phone,
            'email' => $company->email,
            'currency' => $company->currency,
            'currency_symbol' => $company->currency_symbol,
            'tax_rate' => $company->tax_rate,
            'tax_name' => $company->tax_name,
        ]);
    }
}
