<?php

namespace Database\Seeders;
use App\Models\citizens;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        citizens::insert([
    ['Citizen_Purok' => 'Purok 1'],
    ['Citizen_Purok' => 'Purok 2'],
    ['Citizen_Purok' => 'Purok 3'],
]);
    }
}
