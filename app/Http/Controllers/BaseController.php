<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBaseRequest;
use App\Http\Requests\UpdateBaseRequest;
use App\Models\Base;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BaseController extends Controller
{
    public function index(): View
    {
        $bases = Base::latest()->get();

        return view('bases.index', compact('bases'));
    }

    public function create(): View
    {
        $base = new Base();

        return view('bases.create', compact('base'));
    }

    public function store(StoreBaseRequest $request): RedirectResponse
    {
        Base::create($request->validated());

        return redirect()->route('bases.index')->with('status', 'Base created successfully.');
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

        return redirect()->route('bases.index')->with('status', 'Base updated successfully.');
    }

    public function destroy(Base $base): RedirectResponse
    {
        $base->delete();

        return redirect()->route('bases.index')->with('status', 'Base archived successfully.');
    }
}
