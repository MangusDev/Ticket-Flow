<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Ticket;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tickets = Ticket::with(['user', 'area'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $areas = Area::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('tickets.create', compact('roles', 'areas', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'area_id' => 'required|exists:areas,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:open,closed,in_progress',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        Ticket::create($validated);

        return redirect()->route('tickets.index')->with('success', 'Ticket criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket): View
    {
        $ticket->load(['requester', 'operator', 'area']);
        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket): View
    {
        $areas = Area::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'areas', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'area_id' => 'required|exists:areas,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:open,closed,in_progress',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        $ticket->update($validated);
        $ticket->save();

        return redirect()->route('tickets.index')->with('success', 'Ticket atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
