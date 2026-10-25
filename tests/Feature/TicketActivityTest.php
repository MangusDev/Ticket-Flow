<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAudit;
use App\Models\User;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
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

test('ticket update notifications are queued after transaction commit', function () {
    expect(new TicketUpdatedNotification(Ticket::make()))
        ->toBeInstanceOf(ShouldQueueAfterCommit::class);
});

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

test('requester can add a response and notify assigned operator and area', function () {
    [$operator, $requester, $ticket] = activityTicket();
    $ticket->update(['operator_id' => $operator->id]);
    $areaOperator = User::factory()->create([
        'role_id' => Role::where('name', 'operador')->firstOrFail()->id,
        'area_id' => $ticket->area_id,
    ]);
    Notification::fake();

    $this->actingAs($requester)
        ->put(route('tickets.update', $ticket), [
            'comment' => 'Recebi sua resposta e vou confirmar.',
        ])
        ->assertRedirectToRoute('tickets.index');

    expect(TicketAudit::first()->comment)->toBe('Recebi sua resposta e vou confirmar.')
        ->and(TicketAudit::first()->previous_status)->toBe('open')
        ->and(TicketAudit::first()->new_status)->toBe('open');

    Notification::assertSentTo($operator, TicketUpdatedNotification::class,
        fn (TicketUpdatedNotification $notification) => $notification->comment === 'Recebi sua resposta e vou confirmar.'
    );
    Notification::assertSentTo($areaOperator, TicketUpdatedNotification::class,
        fn (TicketUpdatedNotification $notification) => $notification->comment === 'Recebi sua resposta e vou confirmar.'
    );
});

test('requesters cannot respond to closed tickets', function () {
    [, $requester, $ticket] = activityTicket();
    $ticket->update(['status' => 'closed']);
    Notification::fake();

    $this->actingAs($requester)
        ->put(route('tickets.update', $ticket), [
            'comment' => 'Resposta depois do encerramento.',
        ])
        ->assertRedirectToRoute('tickets.show', $ticket)
        ->assertSessionHas('error', 'Este ticket está fechado e não pode receber novas respostas.');

    expect(TicketAudit::count())->toBe(0);
    Notification::assertNothingSent();
});

test('operators must explicitly reopen closed tickets before responding', function () {
    [$operator, , $ticket] = activityTicket();
    $ticket->update(['status' => 'closed']);
    Notification::fake();

    $this->actingAs($operator)
        ->put(route('tickets.update', $ticket), [
            'area_id' => $ticket->area_id,
            'status' => 'closed',
            'priority' => $ticket->priority,
            'comment' => 'Resposta em ticket fechado.',
        ])
        ->assertRedirectToRoute('tickets.show', $ticket)
        ->assertSessionHas('error', 'Este ticket está fechado. Reabra-o antes de fazer alterações.');

    expect($ticket->fresh()->status)->toBe('closed')
        ->and(TicketAudit::count())->toBe(0);
    Notification::assertNothingSent();

    $this->actingAs($operator)
        ->put(route('tickets.update', $ticket), [
            'area_id' => $ticket->area_id,
            'status' => 'in_progress',
            'priority' => $ticket->priority,
        ])
        ->assertRedirectToRoute('tickets.show', $ticket);

    expect($ticket->fresh()->status)->toBe('closed');
});

test('operators can explicitly reopen a closed ticket', function () {
    [$operator, $requester, $ticket] = activityTicket();
    $ticket->update(['status' => 'closed']);
    Notification::fake();

    $this->actingAs($operator)
        ->patch(route('tickets.reopen', $ticket))
        ->assertRedirectToRoute('tickets.show', $ticket)
        ->assertSessionHas('success', 'Ticket reaberto com sucesso.');

    expect($ticket->fresh()->status)->toBe('open')
        ->and(TicketAudit::firstOrFail()->previous_status)->toBe('closed')
        ->and(TicketAudit::firstOrFail()->new_status)->toBe('open');

    Notification::assertSentTo($requester, TicketUpdatedNotification::class);
});

test('requesters cannot reopen a closed ticket', function () {
    [, $requester, $ticket] = activityTicket();
    $ticket->update(['status' => 'closed']);

    $this->actingAs($requester)
        ->patch(route('tickets.reopen', $ticket))
        ->assertForbidden();

    expect($ticket->fresh()->status)->toBe('closed')
        ->and(TicketAudit::count())->toBe(0);
});

test('requester responses do not notify inactive operators', function () {
    [, $requester, $ticket] = activityTicket();
    $operatorRole = Role::where('name', 'operador')->firstOrFail();
    $inactiveAreaOperator = User::factory()->create([
        'role_id' => $operatorRole->id,
        'area_id' => $ticket->area_id,
        'is_active' => false,
    ]);
    $assignedInactiveOperator = User::factory()->create([
        'role_id' => $operatorRole->id,
        'area_id' => Area::create(['name' => 'Outra área'])->id,
        'is_active' => false,
    ]);
    $ticket->update(['operator_id' => $assignedInactiveOperator->id]);
    Notification::fake();

    $this->actingAs($requester)
        ->put(route('tickets.update', $ticket), [
            'comment' => 'Atualização do solicitante.',
        ])
        ->assertRedirectToRoute('tickets.index');

    Notification::assertNotSentTo($inactiveAreaOperator, TicketUpdatedNotification::class);
    Notification::assertNotSentTo($assignedInactiveOperator, TicketUpdatedNotification::class);
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
        ->assertForbidden();

    expect($ticket->fresh()->operator_id)->toBeNull();
});