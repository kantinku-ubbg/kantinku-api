<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Makanan Berat', 'description' => 'Menu utama porsi kenyang', 'is_active' => true],
            ['name' => 'Makanan Ringan', 'description' => 'Camilan dan gorengan', 'is_active' => true],
            ['name' => 'Minuman Dingin', 'description' => 'Es teh, jus, dan minuman segar', 'is_active' => true],
            ['name' => 'Minuman Panas', 'description' => 'Kopi, teh, dan wedang', 'is_active' => true],
            ['name' => 'Paket Hemat', 'description' => 'Kombinasi makanan dan minuman', 'is_active' => true],
        ];

        foreach ($rows as $row) {
            Category::updateOrCreate(
                ['slug' => Category::makeSlug($row['name'])],
                $row + ['slug' => Category::makeSlug($row['name'])],
            );
        }
    }
}
