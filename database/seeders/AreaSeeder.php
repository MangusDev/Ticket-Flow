<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Area::create(['name' => 'TI']);
        Area::create(['name' => 'RH']);
        Area::create(['name' => 'Financeiro']);
        Area::create(['name' => 'Administrativo']);
    }
}
