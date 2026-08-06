<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = Company::query();

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('company_code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $companies = $query->latest()->paginate(10);

        return view('companies.index', compact('companies', 'search', 'view'));
    }

    public function create(): View
    {
        $company = new Company();

        return view('companies.create', compact('company'));
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validated());

        return Redirect::route('companies.index')->with('success', 'Company created successfully.');
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

        return Redirect::route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function archive(Company $company): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['company' => 'Cannot archive this company because active employees still reference it.']);
        }

        $company->delete();

        return Redirect::route('companies.index')->with('success', 'Company archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $company = Company::withTrashed()->findOrFail($id);
        $company->restore();

        return Redirect::route('companies.index')->with('success', 'Company restored successfully.');
    }
}
