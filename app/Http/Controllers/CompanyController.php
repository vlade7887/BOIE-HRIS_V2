<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::latest()->get();

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        $company = new Company();

        return view('companies.create', compact('company'));
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validated());

        return redirect()->route('companies.index')->with('status', 'Company created successfully.');
    }

    public function show(Company $company): View
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('companies.index')->with('status', 'Company updated successfully.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('companies.index')->with('status', 'Company archived successfully.');
    }

    public function archive(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('companies.index')->with('status', 'Company archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $company = Company::withTrashed()->findOrFail($id);
        $company->restore();

        return redirect()->route('companies.index')->with('status', 'Company restored successfully.');
    }
}
