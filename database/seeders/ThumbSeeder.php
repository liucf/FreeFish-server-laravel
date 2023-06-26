<?php

namespace Database\Seeders;

use App\Models\Thumb;
use Illuminate\Database\Seeder;

class ThumbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Thumb::factory()->count(5)->create();
    }
}
