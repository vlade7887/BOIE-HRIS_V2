<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        $sections = Section::with('department')->latest()->get();

        return view('sections.index', compact('sections'));
    }

    public function create(): View
    {
        $section = new Section();

        return view('sections.create', compact('section'));
    }

    public function store(StoreSectionRequest $request): RedirectResponse
    {
        Section::create($request->validated());

        return redirect()->route('sections.index')->with('status', 'Section created successfully.');
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

        return redirect()->route('sections.index')->with('status', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        $section->delete();

        return redirect()->route('sections.index')->with('status', 'Section archived successfully.');
    }
}
