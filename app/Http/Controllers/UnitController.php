<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = Unit::query();

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('unit_code', 'like', "%{$search}%")
                    ->orWhere('unit_name', 'like', "%{$search}%");
            });
        }

        $units = $query->latest()->paginate(10);

        return view('units.index', compact('units', 'search', 'view'));
    }

    public function create(): View
    {
        $unit = new Unit();

        return view('units.create', compact('unit'));
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return Redirect::route('units.index')->with('success', 'Unit created successfully.');
    }

    public function show(Unit $unit): View
    {
        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return Redirect::route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        return $this->archive($unit);
    }

    public function archive(Unit $unit): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('unit_id', $unit->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['unit' => 'Cannot archive this unit because active employees still reference it.']);
        }

        $unit->delete();

        return Redirect::route('units.index')->with('success', 'Unit archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        $unit->restore();

        return Redirect::route('units.index')->with('success', 'Unit restored successfully.');
    }
}
