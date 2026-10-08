<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Electronics' => [
                ['name' => 'Samsung Galaxy Buds FE', 'description' => 'Wireless earbuds with active noise cancellation, ambient sound mode, and a compact charging case.', 'price' => '9999.00', 'stock' => 18],
                ['name' => 'boAt Rockerz 450 Headphones', 'description' => 'Wireless over-ear headphones with soft ear cushions and up to 15 hours of playback.', 'price' => '1699.00', 'stock' => 24],
            ],
            'Home & Kitchen' => [
                ['name' => 'Milton Aura Thermosteel Bottle 1L', 'description' => 'One litre stainless steel vacuum bottle designed to keep drinks hot or cold while travelling.', 'price' => '761.00', 'stock' => 20],
                ['name' => 'Prestige Svachh Tri-Ply Handi Pressure Cooker 3L', 'description' => 'Three litre tri-ply handi pressure cooker with Prestige Svachh controlled gasket release system.', 'price' => '3275.00', 'stock' => 12],
            ],
            'Books' => [
                ['name' => 'Atomic Habits by James Clear', 'description' => 'A practical guide to building good habits and making small improvements over time.', 'price' => '499.00', 'stock' => 16],
                ['name' => 'The Psychology of Money by Morgan Housel', 'description' => 'Short stories about how people think about money, saving, and investing.', 'price' => '399.00', 'stock' => 14],
            ],
            'Fashion' => [
                ['name' => 'Levi’s Men’s 511 Slim Fit Jeans', 'description' => 'Slim fit denim jeans with a classic five pocket design for everyday wear.', 'price' => '2499.00', 'stock' => 10],
                ['name' => 'Baggit Women’s Everyday Tote Bag', 'description' => 'Lightweight everyday tote bag with a zip closure and room for daily essentials.', 'price' => '1299.00', 'stock' => 13],
            ],
            'Sports' => [
                ['name' => 'Nivia Storm Football Size 5', 'description' => 'Durable machine stitched football for regular outdoor practice and matches.', 'price' => '799.00', 'stock' => 21],
                ['name' => 'Boldfit Yoga Mat 6mm', 'description' => 'Non slip exercise mat with a carrying strap for yoga and home workouts.', 'price' => '699.00', 'stock' => 19],
            ],
        ];

        DB::transaction(function () use ($catalog) {
            $categoryNames = array_keys($catalog);
            $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id');

            Product::whereNotIn('category_id', $categoryIds)->delete();
            Category::whereNotIn('name', $categoryNames)->delete();

            foreach ($catalog as $categoryName => $products) {
                $category = Category::firstOrCreate(['name' => $categoryName]);
                $productNames = array_column($products, 'name');

                $category->products()->whereNotIn('name', $productNames)->delete();

                foreach ($products as $product) {
                    $category->products()->updateOrCreate(['name' => $product['name']], $product);
                }
            }
        });
    }
}
