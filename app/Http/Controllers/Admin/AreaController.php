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
        $areas = Area::withCount(['users', 'tickets'])
            ->orderBy('name')
            ->paginate(15);

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
            'is_active' => ['sometimes', 'boolean'],
        ]);

        Area::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.areas.index')->with('success', 'Área criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area): View
    {
        return view('admin.areas.edit', compact('area'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name,' . $area->id,
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $area->update([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? $area->is_active,
        ]);

        return redirect()->route('admin.areas.index')->with('success', 'Área atualizada com sucesso.');
    }

    public function toggle(Area $area): RedirectResponse
    {
        $area->update([
            'is_active' => ! $area->is_active,
        ]);

        $status = $area->fresh()->is_active ? 'ativada' : 'desativada';

        return redirect()->route('admin.areas.index')->with('success', "Área {$status} com sucesso.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area): RedirectResponse
    {
        if ($area->users()->exists() || $area->tickets()->exists()) {
            return redirect()->route('admin.areas.index')
                ->with('error', 'Não é possível excluir uma área vinculada a usuários ou tickets.');
        }

        $area->delete();

        return redirect()->route('admin.areas.index')->with('success', 'Área excluída com sucesso.');
    }
}
