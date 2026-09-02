<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Area;
use Illuminate\View\View;


class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $areas = Area::orderBy('name')->paginate(15);

        return view('admin.areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.areas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
        ]);

        Area::create($validated);

        return redirect()->route('admin.areas.index')->with('success', 'Área criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $Area)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $Area): View
    {
        return view('admin.areas.edit', compact('Area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $Area): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name,' . $Area->id,
        ]);

        $Area->update($validated);

        return redirect()->route('areas.index')->with('success', 'Área atualizada com sucesso.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $Area)
    {
        
    }
}
