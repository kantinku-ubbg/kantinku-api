<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Makanan Berat', 'Nasi Goreng Kampung', 18000, 30, 'Nasi goreng dengan telur dan ayam suwir'],
            ['Makanan Berat', 'Ayam Geprek Sambal Ijo', 20000, 25, 'Ayam crispy dengan sambal ijo'],
            ['Makanan Berat', 'Mie Ayam Bakso', 15000, 40, 'Mie ayam dengan dua butir bakso'],
            ['Makanan Ringan', 'Pisang Goreng Keju', 10000, 50, 'Lima potong pisang goreng topping keju'],
            ['Makanan Ringan', 'Tahu Crispy', 8000, 60, 'Tahu goreng kering dengan bumbu tabur'],
            ['Makanan Ringan', 'Kentang Goreng', 12000, 45, 'Kentang goreng saus mayo'],
            ['Minuman Dingin', 'Es Teh Manis', 5000, 100, 'Teh manis dengan es batu'],
            ['Minuman Dingin', 'Es Jeruk Peras', 8000, 70, 'Jeruk peras segar tanpa pemanis buatan'],
            ['Minuman Dingin', 'Jus Alpukat', 15000, 30, 'Alpukat segar dengan susu kental manis'],
            ['Minuman Panas', 'Kopi Hitam', 7000, 80, 'Kopi tubruk tanpa gula'],
            ['Minuman Panas', 'Teh Tarik', 10000, 60, 'Teh susu khas kantin'],
            ['Paket Hemat', 'Paket Nasi + Es Teh', 21000, 40, 'Nasi goreng kampung dan es teh manis'],
            ['Paket Hemat', 'Paket Geprek + Jeruk', 25000, 35, 'Ayam geprek dan es jeruk peras'],
            ['Paket Hemat', 'Paket Mie + Teh Tarik', 22000, 30, 'Mie ayam bakso dan teh tarik'],
        ];

        foreach ($rows as [$category, $name, $price, $stock, $description]) {
            $categoryModel = Category::where('slug', Category::makeSlug($category))->firstOrFail();

            Menu::updateOrCreate(
                ['slug' => Menu::makeSlug($name)],
                [
                    'category_id' => $categoryModel->id,
                    'name' => $name,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => $description,
                    'is_available' => true,
                ],
            );
        }
    }
}
