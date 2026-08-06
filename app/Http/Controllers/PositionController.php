<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = Position::query();

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('position_code', 'like', "%{$search}%")
                    ->orWhere('position_name', 'like', "%{$search}%");
            });
        }

        $positions = $query->latest()->paginate(10);

        return view('positions.index', compact('positions', 'search', 'view'));
    }

    public function create(): View
    {
        $position = new Position();

        return view('positions.create', compact('position'));
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        Position::create($request->validated());

        return Redirect::route('positions.index')->with('success', 'Position created successfully.');
    }

    public function show(Position $position): View
    {
        return view('positions.show', compact('position'));
    }

    public function edit(Position $position): View
    {
        return view('positions.edit', compact('position'));
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return Redirect::route('positions.index')->with('success', 'Position updated successfully.');
    }

    public function archive(Position $position): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('position_id', $position->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['position' => 'Cannot archive this position because active employees still reference it.']);
        }

        $position->delete();

        return Redirect::route('positions.index')->with('success', 'Position archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $position = Position::withTrashed()->findOrFail($id);
        $position->restore();

        return Redirect::route('positions.index')->with('success', 'Position restored successfully.');
    }
}
