<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');
        $query = $view === 'archived' ? Holiday::onlyTrashed() : Holiday::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('holiday_type', 'like', "%{$search}%")
                    ->orWhereDate('holiday_date', $search);
            });
        }

        $holidays = $query->orderBy('holiday_date')->paginate(10)->withQueryString();

        return view('holidays.index', compact('holidays', 'search', 'view'));
    }

    public function create(): View
    {
        return view('holidays.create', ['holiday' => new Holiday(['is_active' => true])]);
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        Holiday::create($request->validated());

        return Redirect::route('holidays.index')->with('success', 'Holiday created successfully.');
    }

    public function show(Holiday $holiday): View
    {
        return view('holidays.show', compact('holiday'));
    }

    public function edit(Holiday $holiday): View
    {
        return view('holidays.edit', compact('holiday'));
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $holiday->update($request->validated());

        return Redirect::route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function archive(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return Redirect::route('holidays.index')->with('success', 'Holiday archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        Holiday::withTrashed()->findOrFail($id)->restore();

        return Redirect::route('holidays.index')->with('success', 'Holiday restored successfully.');
    }
}
