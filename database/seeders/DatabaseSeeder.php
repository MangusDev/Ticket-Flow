<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cria os grupos na tabela 'roles' do banco de dados
        $roleAdmin       = Role::create(['name' => 'admin']);
        $roleOperador    = Role::create(['name' => 'operador']);
        $roleSolicitante = Role::create(['name' => 'solicitante']);

        // 2. Cria o seu usuário Admin inicial
        $admin = User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@sistema.com',
            'password' => Hash::make('senha123'), // Altere para sua senha desejada
        ]);

        // 3. Atribui a role de admin para esse usuário
        $admin->assignRole($roleAdmin);
    }
}
