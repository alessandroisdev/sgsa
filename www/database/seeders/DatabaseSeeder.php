<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\Area;
use App\Models\Service;
use App\Models\Priority;
use App\Models\Counter;
use App\Models\Totem;
use App\Models\Tv;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@sgsa.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Create Unit
        $unit = Unit::firstOrCreate(
            ['name' => 'Hospital Central'],
            ['active' => true]
        );

        // 3. Create Area
        $area = Area::firstOrCreate(
            ['name' => 'Recepção Principal'],
            ['unit_id' => $unit->id, 'active' => true, 'description' => 'Área de triagem e primeiro atendimento']
        );

        // 4. Create Services
        Service::firstOrCreate(
            ['name' => 'Clínico Geral', 'area_id' => $area->id],
            ['prefix' => 'CLI', 'active' => true]
        );
        Service::firstOrCreate(
            ['name' => 'Pediatria', 'area_id' => $area->id],
            ['prefix' => 'PED', 'active' => true]
        );
        Service::firstOrCreate(
            ['name' => 'Exames Laboratoriais', 'area_id' => $area->id],
            ['prefix' => 'EXA', 'active' => true]
        );

        // 5. Create Priorities
        Priority::firstOrCreate(
            ['name' => 'Atendimento Normal'],
            ['weight' => 0, 'active' => true]
        );
        Priority::firstOrCreate(
            ['name' => 'Idosos (+60) / Gestantes'],
            ['weight' => 5, 'active' => true]
        );
        Priority::firstOrCreate(
            ['name' => 'Emergência (Pulseira Vermelha)'],
            ['weight' => 10, 'active' => true]
        );

        // 6. Create Counter
        Counter::firstOrCreate(
            ['name' => 'Guichê 01'],
            ['area_id' => $area->id, 'active' => true]
        );
        Counter::firstOrCreate(
            ['name' => 'Guichê 02'],
            ['area_id' => $area->id, 'active' => true]
        );

        // 7. Create Totem
        Totem::firstOrCreate(
            ['device_identifier' => 'TOTEM-RECEP-01'],
            ['name' => 'Totem Entrada', 'area_id' => $area->id, 'active' => true]
        );

        // 8. Create TV
        Tv::firstOrCreate(
            ['device_identifier' => 'TV-RECEP-01'],
            ['name' => 'TV Sala de Espera', 'area_id' => $area->id, 'active' => true]
        );
    }
}
