<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Models\Employee;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = Section::query();

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('section_code', 'like', "%{$search}%")
                    ->orWhere('section_name', 'like', "%{$search}%");
            });
        }

        $sections = $query->latest()->paginate(10);

        return view('sections.index', compact('sections', 'search', 'view'));
    }

    public function create(): View
    {
        $section = new Section();

        return view('sections.create', compact('section'));
    }

    public function store(StoreSectionRequest $request): RedirectResponse
    {
        Section::create($request->validated());

        return Redirect::route('sections.index')->with('success', 'Section created successfully.');
    }

    public function show(Section $section): View
    {
        return view('sections.show', compact('section'));
    }

    public function edit(Section $section): View
    {
        return view('sections.edit', compact('section'));
    }

    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        return Redirect::route('sections.index')->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        return $this->archive($section);
    }

    public function archive(Section $section): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('section_id', $section->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['section' => 'Cannot archive this section because active employees still reference it.']);
        }

        $section->delete();

        return Redirect::route('sections.index')->with('success', 'Section archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $section = Section::withTrashed()->findOrFail($id);
        $section->restore();

        return Redirect::route('sections.index')->with('success', 'Section restored successfully.');
    }
}
