<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeederTable extends Seeder
{
    public function run(): void
    {
        Category::create([
            'name' => 'Raw Materials',
        ]);

        Category::create([
            'name' => 'Packages',
        ]);

        Category::create([
            'name' => 'Finished',
        ]);
    }
}
