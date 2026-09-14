<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;

function dashboardUser(string $roleName): User
{
    $role = Role::create(['name' => $roleName]);
    $area = Area::create(['name' => $roleName . '-area']);

    return User::factory()->create([
        'role_id' => $role->id,
        'area_id' => $area->id,
    ]);
}

test('dashboard redirects operators to their area', function () {
    $user = dashboardUser('operador');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('operador.dashboard');
});

test('dashboard redirects requesters to their area', function () {
    $user = dashboardUser('solicitante');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('solicitante.dashboard');
});

test('users cannot access another roles dashboard', function () {
    $user = dashboardUser('solicitante');

    $this->actingAs($user)
        ->get(route('operador.dashboard'))
        ->assertForbidden();
});

test('operator dashboard is rendered for operators', function () {
    $user = dashboardUser('operador');

    $this->actingAs($user)
        ->get(route('operador.dashboard'))
        ->assertOk()
        ->assertSee('Área do operador')
        ->assertSee('Criar ticket');
});

test('requester dashboard shows the create ticket action', function () {
    $user = dashboardUser('solicitante');

    $this->actingAs($user)
        ->get(route('solicitante.dashboard'))
        ->assertOk()
        ->assertSee('Criar ticket');
});

test('operator dashboard shows tickets from their area', function () {
    [$operator, $area] = [dashboardUser('operador'), null];
    $area = $operator->area;
    $requester = User::factory()->create();
    Ticket::create([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Ticket da área do operador',
        'description' => 'Descrição',
        'status' => 'open',
        'priority' => 'high',
    ]);

    $this->actingAs($operator)
        ->get(route('operador.dashboard'))
        ->assertOk()
        ->assertSee('Tickets da minha área')
        ->assertSee('1');
});

test('operator can filter the ticket index to assigned tickets', function () {
    $operator = dashboardUser('operador');
    $area = $operator->area;
    $requester = User::factory()->create();
    $assigned = Ticket::create([
        'user_id' => $requester->id,
        'operator_id' => $operator->id,
        'area_id' => $area->id,
        'title' => 'Ticket atribuído',
        'description' => 'Descrição',
        'status' => 'open',
        'priority' => 'high',
    ]);
    Ticket::create([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Ticket não atribuído',
        'description' => 'Descrição',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $this->actingAs($operator)
        ->get(route('tickets.index', ['assigned' => 1]))
        ->assertOk()
        ->assertSee($assigned->title)
        ->assertDontSee('Ticket não atribuído');
});

test('operators can access and create tickets', function () {
    $operator = dashboardUser('operador');
    $area = Area::create(['name' => 'Financeiro']);

    $this->actingAs($operator)
        ->get(route('tickets.create'))
        ->assertOk()
        ->assertSee('Abrir ticket')
        ->assertSee($area->name);

    $this->actingAs($operator)
        ->post(route('tickets.store'), [
            'area_id' => $area->id,
            'title' => 'Ticket criado pelo operador',
            'description' => 'Descrição do ticket',
            'status' => 'open',
            'priority' => 'medium',
        ])
        ->assertRedirectToRoute('tickets.index');

    expect(Ticket::where('title', 'Ticket criado pelo operador')->value('user_id'))
        ->toBe($operator->id);
});