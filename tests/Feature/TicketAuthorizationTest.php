<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;

function ticketRole(string $name): Role
{
    return Role::create(['name' => $name]);
}

function ticketArea(string $name): Area
{
    return Area::create(['name' => $name]);
}

test('requesters cannot update another users ticket', function () {
    $role = ticketRole('solicitante');
    $area = ticketArea('TI');
    $user = User::factory()->create(['role_id' => $role->id, 'area_id' => $area->id]);
    $otherUser = User::factory()->create(['role_id' => $role->id, 'area_id' => $area->id]);
    $ticket = Ticket::create([
        'user_id' => $otherUser->id,
        'area_id' => $area->id,
        'title' => 'Original',
        'description' => 'Description',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $response = $this->actingAs($user)->patch(route('tickets.update', $ticket), [
        'area_id' => $area->id,
        'title' => 'Alterado',
        'description' => 'Alteração indevida',
        'status' => 'closed',
        'priority' => 'high',
    ]);

    $response->assertForbidden();
    expect($ticket->fresh()->title)->toBe('Original');
});

test('requesters can update their own ticket without changing its owner', function () {
    $role = ticketRole('solicitante');
    $area = ticketArea('TI');
    $user = User::factory()->create(['role_id' => $role->id, 'area_id' => $area->id]);
    $ticket = Ticket::create([
        'user_id' => $user->id,
        'area_id' => $area->id,
        'title' => 'Original',
        'description' => 'Description',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $response = $this->actingAs($user)->patch(route('tickets.update', $ticket), [
        'user_id' => User::factory()->create()->id,
        'area_id' => $area->id,
        'title' => 'Atualizado',
        'description' => 'Descrição atualizada',
        'status' => 'open',
        'priority' => 'medium',
    ]);

    $response->assertRedirectToRoute('tickets.index');
    expect($ticket->fresh()->user_id)->toBe($user->id)
        ->and($ticket->fresh()->title)->toBe('Atualizado');
});

test('operators can update tickets in their area', function () {
    $role = ticketRole('operador');
    $area = ticketArea('TI');
    $user = User::factory()->create(['role_id' => $role->id, 'area_id' => $area->id]);
    $requester = User::factory()->create();
    $ticket = Ticket::create([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Original',
        'description' => 'Description',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $response = $this->actingAs($user)->patch(route('tickets.update', $ticket), [
        'area_id' => $area->id,
        'title' => 'Atualizado pelo operador',
        'description' => 'Descrição atualizada',
        'status' => 'in_progress',
        'priority' => 'high',
    ]);

    $response->assertRedirectToRoute('tickets.index');
    expect($ticket->fresh()->status)->toBe('in_progress');
});