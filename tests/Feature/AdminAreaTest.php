<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;

function adminAreaUser(string $roleName = 'admin'): User
{
    $role = Role::create(['name' => $roleName]);
    $area = Area::create(['name' => 'TI']);

    return User::factory()->create([
        'role_id' => $role->id,
        'area_id' => $area->id,
    ]);
}

test('only administrators can access area management', function () {
    $user = adminAreaUser('solicitante');

    $this->actingAs($user)
        ->get(route('admin.areas.index'))
        ->assertForbidden();
});

test('administrator can create and update an area', function () {
    $admin = adminAreaUser();

    $this->actingAs($admin)
        ->post(route('admin.areas.store'), ['name' => 'Financeiro', 'is_active' => true])
        ->assertRedirectToRoute('admin.areas.index');

    $area = Area::where('name', 'Financeiro')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.areas.update', $area), ['name' => 'Finance', 'is_active' => false])
        ->assertRedirectToRoute('admin.areas.index');

    expect(Area::whereKey($area->id)->value('name'))->toBe('Finance');
    expect(Area::whereKey($area->id)->value('is_active'))->toBeFalse();
});

test('administrator can disable and reactivate an area', function () {
    $admin = adminAreaUser();
    $area = Area::create(['name' => 'Suporte', 'is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.areas.toggle', $area))
        ->assertRedirectToRoute('admin.areas.index');

    expect($area->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->patch(route('admin.areas.toggle', $area))
        ->assertRedirectToRoute('admin.areas.index');

    expect($area->fresh()->is_active)->toBeTrue();
});

test('administrator can delete an unused area', function () {
    $admin = adminAreaUser();
    $area = Area::create(['name' => 'Temporária']);

    $this->actingAs($admin)
        ->delete(route('admin.areas.destroy', $area))
        ->assertRedirectToRoute('admin.areas.index');

    expect(Area::find($area->id))->toBeNull();
});

test('area with users or tickets cannot be deleted', function () {
    $admin = adminAreaUser();
    $area = Area::create(['name' => 'Suporte']);
    $user = User::factory()->create(['area_id' => $area->id]);
    Ticket::create([
        'user_id' => $user->id,
        'area_id' => $area->id,
        'title' => 'Ticket relacionado',
        'description' => 'Descrição',
        'status' => 'open',
        'priority' => 'low',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.areas.destroy', $area))
        ->assertRedirectToRoute('admin.areas.index')
        ->assertSessionHas('error');

    expect(Area::find($area->id))->not->toBeNull();
});