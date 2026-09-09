<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Role;
use App\Models\TicketAudit;
use App\Models\User;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\Ticket;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = request()->user();
        $assignedOnly = request()->boolean('assigned');
        $ticketsQuery = Ticket::with(['requester', 'area'])
            ->orderBy('created_at', 'desc')
            ->when($user->role?->name === 'solicitante', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($user->role?->name === 'operador', function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('operator_id', $user->id)
                        ->orWhere('area_id', $user->area_id);
                });
            })
            ->when($user->role?->name === 'operador' && $assignedOnly, function ($query) use ($user) {
                $query->where('operator_id', $user->id);
            });

        $tickets = $ticketsQuery->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $areas = Area::orderBy('name')->get();

        Gate::authorize('create', Ticket::class);

        return view('tickets.create', compact('areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:open,closed,in_progress',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        $data['user_id'] = $user->id;

        Ticket::create($data);

        return redirect()->route('tickets.index')->with('success', 'Ticket criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket): View
    {
        Gate::authorize('view', $ticket);
        $ticket->load(['requester', 'operator', 'area']);
        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);
        $areas = Area::orderBy('name')->get();
        $operatorRoleId = Role::where('name', 'operador')->value('id');
        $operators = User::when($operatorRoleId, function ($query) use ($operatorRoleId) {
            $query->where('role_id', $operatorRoleId);
        }, function ($query) {
            $query->whereKey(0);
        })->orderBy('name')->get();
        $ticket->load('audits.user');

        return view('tickets.edit', compact('ticket', 'areas', 'operators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:open,closed,in_progress',
            'priority' => 'required|string|in:low,medium,high',
            'operator_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role_id', Role::where('name', 'operador')->pluck('id'));
                }),
            ],
            'comment' => 'nullable|string|max:5000',
        ]);

        $canAssign = Gate::allows('assign', $ticket);
        if (!$canAssign) {
            unset($validated['operator_id']);
        }

        $comment = trim((string) ($validated['comment'] ?? '')) ?: null;
        unset($validated['comment']);
        $previousStatus = $ticket->status;
        $statusChanged = $previousStatus !== $validated['status'];

        if ($comment !== null) {
            Gate::authorize('respond', $ticket);
        }

        if ($statusChanged) {
            Gate::authorize('changeStatus', $ticket);
        }

        DB::transaction(function () use ($ticket, $validated, $comment, $previousStatus, $statusChanged, $request): void {
            $ticket->update($validated);

            if ($statusChanged || $comment !== null) {
                TicketAudit::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $request->user()->id,
                    'previous_status' => $previousStatus,
                    'new_status' => $ticket->status,
                    'comment' => $comment,
                ]);

                $ticket->requester->notify(new TicketUpdatedNotification($ticket, $comment, $statusChanged));
            }
        });

        return redirect()->route('tickets.index')->with('success', 'Ticket atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket excluído com sucesso.');
    }
}
