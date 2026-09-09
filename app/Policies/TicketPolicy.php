<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
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

    public function create(User $user): bool
    {
        return $user->role?->name !== null;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function respond(User $user, Ticket $ticket): bool
    {
        return $this->isAdmin($user) || $this->isOperator($user);
    }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return $this->respond($user, $ticket);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $this->isAdmin($user) || $this->isOperator($user);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->isAdmin($user) || $ticket->user_id === $user->id;
    }

    private function isAdmin(User $user): bool
    {
        return $user->role?->name === 'admin';
    }

    private function isOperator(User $user): bool
    {
        return $user->role?->name === 'operador';
    }
}