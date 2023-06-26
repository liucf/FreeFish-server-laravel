<?php

namespace Database\Seeders;

use App\Models\Rootcategory;
use Illuminate\Database\Seeder;

class RootcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rootcategory::factory()->count(5)->create();
    }
}
