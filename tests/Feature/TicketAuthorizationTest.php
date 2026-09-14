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

test('requesters cannot update their own ticket', function () {
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

    $response->assertForbidden();
    expect($ticket->fresh()->user_id)->toBe($user->id)
        ->and($ticket->fresh()->title)->toBe('Original');
});

test('admins cannot update tickets', function () {
    $adminRole = ticketRole('admin');
    $requesterRole = ticketRole('solicitante');
    $area = ticketArea('TI');
    $admin = User::factory()->create(['role_id' => $adminRole->id, 'area_id' => $area->id]);
    $requester = User::factory()->create(['role_id' => $requesterRole->id, 'area_id' => $area->id]);
    $ticket = Ticket::create([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Original',
        'description' => 'Description',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $response = $this->actingAs($admin)->patch(route('tickets.update', $ticket), [
        'area_id' => $area->id,
        'title' => 'Tentativa admin',
        'description' => 'Descrição admin',
        'status' => 'closed',
        'priority' => 'high',
    ]);

    $response->assertForbidden();
    expect($ticket->fresh()->title)->toBe('Original');
});

test('admins cannot create tickets', function () {
    $adminRole = ticketRole('admin');
    $area = ticketArea('TI');
    $admin = User::factory()->create(['role_id' => $adminRole->id, 'area_id' => $area->id]);

    $this->actingAs($admin)
        ->get(route('tickets.create'))
        ->assertForbidden();
});

test('operators cannot change title or description, but can update allowed fields', function () {
    $role = ticketRole('operador');
    $area = ticketArea('TI');
    $user = User::factory()->create(['role_id' => $role->id, 'area_id' => $area->id]);
    $requester = User::factory()->create();
    $otherOperator = User::factory()->create(['role_id' => $role->id, 'area_id' => $area->id]);
    $ticket = Ticket::create([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Original',
        'description' => 'Description',
        'status' => 'open',
        'priority' => 'low',
        'operator_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->patch(route('tickets.update', $ticket), [
        'area_id' => $area->id,
        'title' => 'Titulo proibido',
        'description' => 'Descrição proibida',
        'status' => 'in_progress',
        'priority' => 'high',
        'operator_id' => $otherOperator->id,
        'comment' => 'Resposta do operador',
    ]);

    $response->assertRedirectToRoute('tickets.index');
    expect($ticket->fresh()->title)->toBe('Original')
        ->and($ticket->fresh()->description)->toBe('Description')
        ->and($ticket->fresh()->status)->toBe('in_progress')
        ->and($ticket->fresh()->priority)->toBe('high')
        ->and($ticket->fresh()->operator_id)->toBe($otherOperator->id);
});