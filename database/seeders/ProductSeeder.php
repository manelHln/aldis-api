<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\ProductType;
use App\Models\ProductCategory;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all product types and categories
        $productTypes = ProductType::all();
        $productCategories = ProductCategory::all();

        Product::factory()->count(50)->create(function () use ($productTypes, $productCategories) {
            return [
                'product_type_id' => $productTypes->random()->id,
                'category_id' => $productCategories->random()->id,
            ];
        });
    }
}
