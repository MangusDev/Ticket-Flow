<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->hasValidRole()) {
            return false;
        }

        return null;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isOperator($user)) {
            return $ticket->operator_id === $user->id
                || $ticket->area_id === $user->area_id;
        }

        return $ticket->user_id === $user->id;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'operador', 'solicitante'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, ['solicitante', 'operador'], true);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->isOperator($user) && $this->view($user, $ticket);
    }

    public function respond(User $user, Ticket $ticket): bool
    {
        if ($this->isOperator($user)) {
            return true;
        }

        return $this->isRequester($user) && $ticket->user_id === $user->id;
    }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return $this->isOperator($user);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $this->isOperator($user);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->role?->name === 'admin';
    }

    private function isOperator(User $user): bool
    {
        return $user->role?->name === 'operador';
    }

    private function isRequester(User $user): bool
    {
        return $user->role?->name === 'solicitante';
    }
}