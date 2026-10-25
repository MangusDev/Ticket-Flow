<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Role;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    // Injeção do serviço pelo construtor
    public function __construct(private TicketService $ticketService) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Ticket::class);

        $tickets = $this->ticketService->getTicketsForUser(
            $request->user(), 
            $request->boolean('assigned')
        );

        return view('tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        Gate::authorize('create', Ticket::class);
        $areas = $this->ticketService->getActiveAreas();

        return view('tickets.create', compact('areas'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Ticket::class);

        $validated = $request->validate([
            'area_id' => ['required', Rule::exists('areas', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', 'string', Rule::in(TicketPriority::values())],
        ]);

        $this->ticketService->createTicket($validated, $request->user());

        return redirect()->route('tickets.index')->with('success', 'Ticket criado com sucesso.');
    }

    public function show(Ticket $ticket): View
    {
        Gate::authorize('view', $ticket);
        $ticket->load(['requester', 'operator', 'area']);
        
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);
        
        $areas = $this->ticketService->getActiveAreas();
        $operators = $this->ticketService->getOperators();
        $ticket->load('audits.user');

        return view('tickets.edit', compact('ticket', 'areas', 'operators'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = $request->user();
        $isOperator = $user->role?->name === 'operador';

        if ($isOperator) {
            Gate::authorize('update', $ticket);

            if ($ticket->status === TicketStatus::CLOSED->value) {
                return redirect()->route('tickets.show', $ticket)
                    ->with('error', 'Este ticket está fechado. Reabra-o antes de fazer alterações.');
            }

            if (! $request->exists('operator_id')) {
                $request->merge(['operator_id' => $ticket->operator_id]);
            }

            $validated = $request->validate([
                'area_id' => [
                    'required',
                    Rule::exists('areas', 'id')->where(fn ($query) => $query->where('is_active', true)),
                ],
                'status' => ['required', 'string', Rule::in(TicketStatus::values())],
                'priority' => ['required', 'string', Rule::in(TicketPriority::values())],
                'operator_id' => [
                    'nullable',
                    Rule::exists('users', 'id')->where(function ($query) use ($request) {
                        $query->where('is_active', true)
                            ->where('area_id', $request->input('area_id'))
                            ->whereIn('role_id', Role::where('name', 'operador')->pluck('id'));
                    }),
                ],
                'comment' => 'nullable|string|max:5000',
            ]);

            if (filled($validated['comment'] ?? null)) {
                Gate::authorize('respond', $ticket);
            }

            if (!Gate::allows('assign', $ticket)) {
                unset($validated['operator_id']);
            }

            if ($ticket->status !== $validated['status'] || $ticket->status === TicketStatus::CLOSED->value) {
                Gate::authorize('changeStatus', [$ticket, $validated['status']]);
            }

        } else {
            if (
                $user->role?->name === 'solicitante'
                && $ticket->user_id === $user->id
                && $ticket->status === TicketStatus::CLOSED->value
            ) {
                return redirect()->route('tickets.show', $ticket)
                    ->with('error', 'Este ticket está fechado e não pode receber novas respostas.');
            }

            Gate::authorize('respond', $ticket);

            if ($request->hasAny(['area_id', 'title', 'description', 'status', 'priority', 'operator_id'])) {
                abort(403, 'Solicitante só pode responder ao ticket.');
            }

            $validated = $request->validate([
                'comment' => 'nullable|string|max:5000',
            ]);
        }

        $comment = trim((string) ($validated['comment'] ?? '')) ?: null;
        unset($validated['comment']);

        $this->ticketService->updateTicket($ticket, $validated, $user, $comment);

        return redirect()->route('tickets.index')->with('success', 'Ticket atualizado com sucesso.');
    }

    public function reopen(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('reopen', $ticket);

        $this->ticketService->updateTicket($ticket, ['status' => TicketStatus::OPEN->value], $request->user());

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket reaberto com sucesso.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);
        
        $this->ticketService->deleteTicket($ticket);

        return redirect()->route('tickets.index')->with('success', 'Ticket excluído com sucesso.');
    }
}