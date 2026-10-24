<?php

use App\Models\Area;
use App\Models\Role;
use App\Models\User;

function adminUserForManagement(): User
{
    $role = Role::create(['name' => 'admin']);

    return User::factory()->create([
        'role_id' => $role->id,
        'area_id' => null,
    ]);
}

test('administrator user form only lists active areas', function () {
    $admin = adminUserForManagement();
    Area::create(['name' => 'Ativa', 'is_active' => true]);
    Area::create(['name' => 'Inativa', 'is_active' => false]);

    $this->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('Ativa')
        ->assertDontSee('Inativa');
});

test('administrator accounts are created without an area', function () {
    $admin = adminUserForManagement();
    $adminRole = Role::where('name', 'admin')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Novo administrador',
            'email' => 'novo-admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $adminRole->id,
            'area_id' => null,
        ])
        ->assertRedirectToRoute('admin.users.index');

    expect(User::where('email', 'novo-admin@example.com')->firstOrFail()->area_id)->toBeNull();
});

test('administrator accounts cannot be assigned an area', function () {
    $admin = adminUserForManagement();
    $adminRole = Role::where('name', 'admin')->firstOrFail();
    $area = Area::create(['name' => 'Ativa']);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Administrador com área',
            'email' => 'admin-area@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role_id' => $adminRole->id,
            'area_id' => $area->id,
        ])
        ->assertSessionHasErrors('area_id');
});

test('non-administrator accounts require an active area', function () {
    $admin = adminUserForManagement();
    $requesterRole = Role::create(['name' => 'solicitante']);
    $inactiveArea = Area::create(['name' => 'Inativa', 'is_active' => false]);
    $data = [
        'name' => 'Solicitante',
        'email' => 'solicitante@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role_id' => $requesterRole->id,
    ];

    $this->actingAs($admin)
        ->post(route('admin.users.store'), $data)
        ->assertSessionHasErrors('area_id');

    $this->actingAs($admin)
        ->post(route('admin.users.store'), $data + ['area_id' => $inactiveArea->id])
        ->assertSessionHasErrors('area_id');
});
