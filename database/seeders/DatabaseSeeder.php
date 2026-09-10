<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            RoleSeeder::class,
            LandlordSeeder::class,
            DiagnosisSeeder::class,
            ExerciseSeeder::class,
        ];

        if (! app()->environment('production')) {
            $seeders[] = DevSeeder::class;
        }

        $this->call($seeders);
    }
}
