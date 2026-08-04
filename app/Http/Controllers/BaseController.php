<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBaseRequest;
use App\Http\Requests\UpdateBaseRequest;
use App\Models\Base;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class BaseController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = Base::query();

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('base_code', 'like', "%{$search}%")
                    ->orWhere('base_name', 'like', "%{$search}%");
            });
        }

        $bases = $query->latest()->paginate(10);

        return view('bases.index', compact('bases', 'search', 'view'));
    }

    public function create(): View
    {
        $base = new Base();

        return view('bases.create', compact('base'));
    }

    public function store(StoreBaseRequest $request): RedirectResponse
    {
        Base::create($request->validated());

        return Redirect::route('bases.index')->with('success', 'Base created successfully.');
    }

    public function show(Base $base): View
    {
        return view('bases.show', compact('base'));
    }

    public function edit(Base $base): View
    {
        return view('bases.edit', compact('base'));
    }

    public function update(UpdateBaseRequest $request, Base $base): RedirectResponse
    {
        $base->update($request->validated());

        return Redirect::route('bases.index')->with('success', 'Base updated successfully.');
    }


    public function archive(Base $base): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('base_id', $base->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['base' => 'Cannot archive this base because active employees still reference it.']);
        }

        $base->delete();

        return Redirect::route('bases.index')->with('success', 'Base archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $base = Base::withTrashed()->findOrFail($id);
        $base->restore();

        return Redirect::route('bases.index')->with('success', 'Base restored successfully.');
    }
}
