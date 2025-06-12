<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Gadgets, devices, and more.'],
            ['name' => 'Books', 'description' => 'All genres of books.'],
            ['name' => 'Clothing', 'description' => 'Apparel for all occasions.'],
            ['name' => 'Home & Kitchen', 'description' => 'Items for your home and kitchen.'],
            ['name' => 'Toys & Games', 'description' => 'Fun for all ages.'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'slug' => Str::slug($categoryData['name']),
                    'description' => $categoryData['description'],
                ]
            );
        }
    }
}
