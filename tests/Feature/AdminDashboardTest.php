<?php

use App\Enums\TicketStatus;
use App\Models\AdminAudit;
use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAudit;
use App\Models\User;

function reportingAdmin(): User
{
    return User::factory()->create([
        'role_id' => Role::create(['name' => 'admin'])->id,
        'area_id' => null,
    ]);
}

function reportingTicket(User $requester, Area $area, array $attributes = []): Ticket
{
    return Ticket::create(array_merge([
        'user_id' => $requester->id,
        'area_id' => $area->id,
        'title' => 'Ticket de relatório',
        'description' => 'Descrição',
        'status' => TicketStatus::OPEN->value,
        'priority' => 'medium',
    ], $attributes));
}

test('admin dashboard displays period metrics, distribution, users and admin activity', function () {
    $admin = reportingAdmin();
    $area = Area::create(['name' => 'Suporte']);
    $operator = User::factory()->create([
        'role_id' => Role::create(['name' => 'operador'])->id,
        'area_id' => $area->id,
    ]);
    $requester = User::factory()->create([
        'role_id' => Role::create(['name' => 'solicitante'])->id,
        'area_id' => $area->id,
    ]);
    $closedTicket = reportingTicket($requester, $area, [
        'operator_id' => $operator->id,
        'status' => TicketStatus::CLOSED->value,
    ]);
    $closedTicket->forceFill(['created_at' => now()->subDays(3)])->save();
    TicketAudit::create([
        'ticket_id' => $closedTicket->id,
        'user_id' => $operator->id,
        'previous_status' => TicketStatus::IN_PROGRESS->value,
        'new_status' => TicketStatus::CLOSED->value,
        'comment' => 'Resolvido',
    ])->forceFill(['created_at' => now()->subDay()])->save();
    reportingTicket($requester, $area, ['operator_id' => $operator->id]);
    AdminAudit::create([
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'area.created',
        'subject_type' => 'Area',
        'subject_id' => $area->id,
        'subject_label' => $area->name,
        'changes' => ['name' => $area->name],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Tickets no período')
        ->assertSee('Tickets por área')
        ->assertSee('Tickets por operador')
        ->assertSee('Usuários ativos / inativos')
        ->assertSee('Trilha de auditoria administrativa')
        ->assertSee('Suporte')
        ->assertSee($admin->name)
        ->assertSee('2')
        ->assertSee('48 h');
});

test('admin dashboard filters report data by selected period', function () {
    $admin = reportingAdmin();
    $area = Area::create(['name' => 'Suporte']);
    $requester = User::factory()->create(['area_id' => $area->id]);
    $recent = reportingTicket($requester, $area, ['title' => 'Dentro do período']);
    $recent->forceFill(['created_at' => now()->startOfDay()])->save();
    $old = reportingTicket($requester, $area, ['title' => 'Fora do período']);
    $old->forceFill(['created_at' => now()->subDays(60)])->save();

    $this->actingAs($admin)
        ->get(route('admin.dashboard', [
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('<p class="mt-3 text-2xl font-bold text-ink-950">1</p>', false)
        ->assertSee('Tickets no período');
});

test('admin can export a date-filtered CSV report with spreadsheet-safe cells', function () {
    $admin = reportingAdmin();
    $area = Area::create(['name' => 'Suporte']);
    $requester = User::factory()->create(['area_id' => $area->id]);
    reportingTicket($requester, $area, ['title' => '=HYPERLINK("https://example.test")']);

    $response = $this->actingAs($admin)
        ->get(route('admin.reports.export', [
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();
    $header = str_getcsv(ltrim(strtok($csv, "\n"), "\xEF\xBB\xBF\r"));

    expect($header)->toBe(['ID', 'Título', 'Solicitante', 'Área', 'Operador', 'Status', 'Prioridade', 'Criado em', 'Fechado em'])
        ->and($csv)->toContain('\'=HYPERLINK');
});

test('admin user and area changes are added to administrative audit trail', function () {
    $admin = reportingAdmin();

    $this->actingAs($admin)
        ->post(route('admin.areas.store'), ['name' => 'Suporte'])
        ->assertRedirectToRoute('admin.areas.index');

    $area = Area::where('name', 'Suporte')->firstOrFail();
    expect(AdminAudit::where('action', 'area.created')->where('subject_id', $area->id)->exists())->toBeTrue();

    $role = Role::create(['name' => 'solicitante']);
    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Pessoa solicitante',
            'email' => 'report-requester@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $role->id,
            'area_id' => $area->id,
        ])
        ->assertRedirectToRoute('admin.users.index');

    $user = User::where('email', 'report-requester@example.test')->firstOrFail();
    expect(AdminAudit::where('action', 'user.created')->where('subject_id', $user->id)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirectToRoute('admin.users.index');

    expect(AdminAudit::where('action', 'user.deactivated')->where('subject_id', $user->id)->exists())->toBeTrue();
});
