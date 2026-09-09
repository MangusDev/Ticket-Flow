<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;

function requesterViewUser(): array
{
    $role = Role::create(['name' => 'solicitante']);
    $area = Area::create(['name' => 'TI']);
    $user = User::factory()->create([
        'role_id' => $role->id,
        'area_id' => $area->id,
    ]);

    return [$user, $area];
}

test('requester can render the ticket creation view', function () {
    [$user, $area] = requesterViewUser();

    $this->actingAs($user)
        ->get(route('tickets.create'))
        ->assertOk()
        ->assertSee('Abrir ticket')
        ->assertSee($area->name);
});

test('requester can create a ticket from the creation view', function () {
    [$user, $area] = requesterViewUser();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'area_id' => $area->id,
            'title' => 'Não consigo acessar o sistema',
            'description' => 'A tela retorna um erro ao entrar.',
            'status' => 'open',
            'priority' => 'high',
        ])
        ->assertRedirectToRoute('tickets.index');

    $ticket = Ticket::firstOrFail();
    expect($ticket->user_id)->toBe($user->id)
        ->and($ticket->status)->toBe('open')
        ->and($ticket->title)->toBe('Não consigo acessar o sistema');
});

test('requester can render their ticket editing view', function () {
    [$user, $area] = requesterViewUser();
    $ticket = Ticket::create([
        'user_id' => $user->id,
        'area_id' => $area->id,
        'title' => 'Título original',
        'description' => 'Descrição original',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $this->actingAs($user)
        ->get(route('tickets.edit', $ticket))
        ->assertOk()
        ->assertSee('Editar ticket')
        ->assertSee('Título original');
});

test('requester can update their ticket from the editing view', function () {
    [$user, $area] = requesterViewUser();
    $ticket = Ticket::create([
        'user_id' => $user->id,
        'area_id' => $area->id,
        'title' => 'Título original',
        'description' => 'Descrição original',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $this->actingAs($user)
        ->put(route('tickets.update', $ticket), [
            'area_id' => $area->id,
            'title' => 'Título atualizado',
            'description' => 'Descrição atualizada',
            'status' => 'open',
            'priority' => 'medium',
        ])
        ->assertRedirectToRoute('tickets.index');

    expect($ticket->fresh()->title)->toBe('Título atualizado')
        ->and($ticket->fresh()->priority)->toBe('medium');
});