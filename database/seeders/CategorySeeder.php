<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Makanan Utama', 'description' => 'Menu makanan utama'],
            ['name' => 'Minuman', 'description' => 'Menu minuman'],
            ['name' => 'Dessert', 'description' => 'Menu makanan penutup'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
