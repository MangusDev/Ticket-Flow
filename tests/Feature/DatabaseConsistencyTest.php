<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAudit;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function createTicketForDatabaseConsistency(User $requester, Area $area): Ticket
{
    return Ticket::create([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Ticket histórico',
        'description' => 'Solicitação de teste',
        'status' => 'open',
        'priority' => 'low',
    ]);
}

test('database prevents deleting a requester with tickets', function () {
    $area = Area::create(['name' => 'Suporte']);
    $requester = User::factory()->create([
        'role_id' => Role::create(['name' => 'solicitante'])->id,
        'area_id' => $area->id,
    ]);
    $ticket = createTicketForDatabaseConsistency($requester, $area);

    expect(fn () => $requester->delete())->toThrow(QueryException::class);
    expect(Ticket::find($ticket->id))->not->toBeNull();
});

test('database prevents deleting an area referenced by users or tickets', function () {
    $area = Area::create(['name' => 'Suporte']);
    $requester = User::factory()->create([
        'role_id' => Role::create(['name' => 'solicitante'])->id,
        'area_id' => $area->id,
    ]);
    $ticket = createTicketForDatabaseConsistency($requester, $area);

    expect(fn () => $area->delete())->toThrow(QueryException::class);
    expect(Area::find($area->id))->not->toBeNull()
        ->and(Ticket::find($ticket->id))->not->toBeNull();
});

test('database preserves ticket audits when their author is deleted', function () {
    $area = Area::create(['name' => 'Suporte']);
    $author = User::factory()->create([
        'role_id' => Role::create(['name' => 'operador'])->id,
        'area_id' => $area->id,
    ]);
    $requester = User::factory()->create([
        'role_id' => Role::create(['name' => 'solicitante'])->id,
        'area_id' => $area->id,
    ]);
    $ticket = createTicketForDatabaseConsistency($requester, $area);
    $audit = TicketAudit::create([
        'ticket_id' => $ticket->id,
        'user_id' => $author->id,
        'previous_status' => 'open',
        'new_status' => 'in_progress',
        'comment' => 'Em atendimento',
    ]);

    expect(fn () => $author->delete())->toThrow(QueryException::class);
    expect(TicketAudit::find($audit->id))->not->toBeNull()
        ->and(User::find($author->id))->not->toBeNull();
});

test('database rejects unsupported ticket status and priority values', function () {
    $area = Area::create(['name' => 'Suporte']);
    $requester = User::factory()->create([
        'role_id' => Role::create(['name' => 'solicitante'])->id,
        'area_id' => $area->id,
    ]);
    $ticket = createTicketForDatabaseConsistency($requester, $area);

    expect(fn () => DB::table('tickets')->where('id', $ticket->id)->update([
        'status' => 'resolved',
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('tickets')->where('id', $ticket->id)->update([
        'priority' => 'urgent',
    ]))->toThrow(QueryException::class);

    expect(fn () => TicketAudit::create([
        'ticket_id' => $ticket->id,
        'user_id' => $requester->id,
        'previous_status' => 'invalid',
        'new_status' => 'open',
    ]))->toThrow(QueryException::class);
});
