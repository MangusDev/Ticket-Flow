<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAudit;
use App\Models\User;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function getTicketsForUser(User $user, bool $assignedOnly = false, int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::with(['requester', 'area'])
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
            })
            ->when(! in_array($user->role?->name, ['admin', 'operador', 'solicitante'], true), function ($query) {
                $query->whereKey(0);
            })
            ->paginate($perPage);
    }

    public function getActiveAreas(): Collection
    {
        return Area::where('is_active', true)->orderBy('name')->get();
    }

    public function getOperators(): Collection
    {
        $operatorRoleId = Role::where('name', 'operador')->value('id');
        
        return User::when($operatorRoleId, function ($query) use ($operatorRoleId) {
            $query->where('role_id', $operatorRoleId)
                ->where('is_active', true);
        }, function ($query) {
            $query->whereKey(0); // Força retorno vazio se a role não existir
        })->orderBy('name')->get();
    }

    public function createTicket(array $data, User $user): Ticket
    {
        $data['user_id'] = $user->id;
        return Ticket::create($data);
    }

    public function updateTicket(Ticket $ticket, array $data, User $user, ?string $comment = null): Ticket
    {
        $isOperator = $user->role?->name === 'operador';
        $previousStatus = $ticket->status;
        $statusChanged = $isOperator && isset($data['status']) && $previousStatus !== $data['status'];

        DB::transaction(function () use ($ticket, $data, $comment, $previousStatus, $statusChanged, $user, $isOperator) {
            
            // Atualiza os dados do ticket caso seja operador
            if ($isOperator && !empty($data)) {
                $ticket->update($data);
            }

            // Audita e notifica apenas se houver comentário ou mudança de status
            if ($statusChanged || $comment !== null) {
                TicketAudit::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'previous_status' => $previousStatus,
                    'new_status' => $ticket->status, // Pega o status atualizado
                    'comment' => $comment,
                ]);

                $this->sendNotifications($ticket, $comment, $statusChanged, $isOperator);
            }
        });

        return $ticket;
    }

    public function deleteTicket(Ticket $ticket): void
    {
        $ticket->delete();
    }

    private function sendNotifications(Ticket $ticket, ?string $comment, bool $statusChanged, bool $isOperator): void
    {
        $notification = new TicketUpdatedNotification($ticket, $comment, $statusChanged);

        if ($isOperator) {
            $ticket->requester->notify($notification);
        } else {
            $operatorRoleId = Role::where('name', 'operador')->value('id');
            $notifiableUsers = User::query()
                ->when($operatorRoleId, fn ($query) => $query->where('role_id', $operatorRoleId))
                ->where('area_id', $ticket->area_id)
                ->get();

            if ($ticket->operator_id) {
                $notifiableUsers->push($ticket->operator);
            }

            $notifiableUsers = $notifiableUsers->filter()->unique('id');
            
            foreach ($notifiableUsers as $notifiableUser) {
                $notifiableUser->notify($notification);
            }
        }
    }
}