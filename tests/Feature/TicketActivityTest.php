<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAudit;
use App\Models\User;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Support\Facades\Notification;

function activityTicket(): array
{
    $operatorRole = Role::create(['name' => 'operador']);
    $requesterRole = Role::create(['name' => 'solicitante']);
    $area = Area::create(['name' => 'TI']);
    $operator = User::factory()->create(['role_id' => $operatorRole->id, 'area_id' => $area->id]);
    $requester = User::factory()->create(['role_id' => $requesterRole->id, 'area_id' => $area->id]);
    $ticket = Ticket::create([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Acesso bloqueado',
        'description' => 'Não consigo entrar.',
        'status' => 'open',
        'priority' => 'high',
    ]);

    return [$operator, $requester, $ticket];
}

test('operator response and status change create audit and notify requester', function () {
    [$operator, $requester, $ticket] = activityTicket();

    $this->actingAs($operator)
        ->put(route('tickets.update', $ticket), [
            'area_id' => $ticket->area_id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => 'in_progress',
            'priority' => $ticket->priority,
            'comment' => 'Estamos analisando o problema.',
        ])
        ->assertRedirectToRoute('tickets.index');

    $audit = TicketAudit::firstOrFail();
    expect($audit->ticket_id)->toBe($ticket->id)
        ->and($audit->user_id)->toBe($operator->id)
        ->and($audit->previous_status)->toBe('open')
        ->and($audit->new_status)->toBe('in_progress')
        ->and($audit->comment)->toBe('Estamos analisando o problema.');

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $requester->id,
        'type' => TicketUpdatedNotification::class,
    ]);
});

test('operator can add a response without changing status', function () {
    [$operator, $requester, $ticket] = activityTicket();

    $this->actingAs($operator)->put(route('tickets.update', $ticket), [
        'area_id' => $ticket->area_id,
        'title' => $ticket->title,
        'description' => $ticket->description,
        'status' => 'open',
        'priority' => $ticket->priority,
        'comment' => 'Recebemos sua solicitação.',
    ]);

    expect(TicketAudit::first()->previous_status)->toBe('open')
        ->and(TicketAudit::first()->new_status)->toBe('open')
        ->and(TicketAudit::first()->comment)->toBe('Recebemos sua solicitação.');
});

test('requester cannot change status or add an operator response', function () {
    [$operator, $requester, $ticket] = activityTicket();
    Notification::fake();

    $this->actingAs($requester)
        ->put(route('tickets.update', $ticket), [
            'area_id' => $ticket->area_id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => 'closed',
            'priority' => $ticket->priority,
            'comment' => 'Tentativa indevida.',
        ])
        ->assertForbidden();

    expect($ticket->fresh()->status)->toBe('open')
        ->and(TicketAudit::count())->toBe(0);
    Notification::assertNothingSent();
});

test('operator can assign a ticket to another operator', function () {
    [$operator, $requester, $ticket] = activityTicket();
    $otherOperatorRole = Role::where('name', 'operador')->firstOrFail();
    $otherOperator = User::factory()->create([
        'role_id' => $otherOperatorRole->id,
        'area_id' => $ticket->area_id,
    ]);

    $this->actingAs($operator)
        ->put(route('tickets.update', $ticket), [
            'area_id' => $ticket->area_id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'operator_id' => $otherOperator->id,
        ])
        ->assertRedirectToRoute('tickets.index');

    expect($ticket->fresh()->operator_id)->toBe($otherOperator->id);
});

test('requester cannot assign a ticket to an operator', function () {
    [$operator, $requester, $ticket] = activityTicket();

    $this->actingAs($requester)
        ->put(route('tickets.update', $ticket), [
            'area_id' => $ticket->area_id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'operator_id' => $operator->id,
        ])
        ->assertRedirectToRoute('tickets.index');

    expect($ticket->fresh()->operator_id)->toBeNull();
});