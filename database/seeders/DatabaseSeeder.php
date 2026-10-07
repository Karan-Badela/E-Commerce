<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Electronics', 'Home & Kitchen', 'Books', 'Fashion', 'Sports', 'Beauty', 'Toys', 'Grocery', 'Office', 'Automotive', 'Garden', 'Pet Supplies'];
        foreach ($categories as $name) {
            $category = Category::firstOrCreate(['name' => $name]);
            Product::factory(3)->create(['category_id' => $category->id]);
        }
    }
}
