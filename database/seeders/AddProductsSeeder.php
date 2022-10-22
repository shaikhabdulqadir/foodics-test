<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $merchant = Merchant::first();
        $products = [
            [
                'name' => 'Burger',
                'ingredients' => [
                    [
                        'name' => "Beef",
                        'amount_required' => 150,
                        'total' => 20000,
                    ],
                    [
                        'name' => "Cheese",
                        'amount_required' => 30,
                        'total' => 5000,
                    ],
                    [
                        'name' => "Onion",
                        'amount_required' => 20,
                        'total' => 1000,
                    ]
                ]
            ]
        ];

        foreach ($products as $p) {

            $product = Product::firstOrCreate([
                'name' => $p['name'],
                'merchant_id' => $merchant->id
            ]);

            foreach ($p['ingredients'] as $ing) {
                $ingredient = Ingredient::firstOrCreate(
                    [
                        'name' => $ing['name'],
                    ],
                    [
                        'total' => $ing['total'],
                        'available' => $ing['total']
                    ]
                );
                $product->ingredients()->detach($ingredient->id);
                $product->ingredients()->attach($ingredient->id, ['amount_required' => $ing['amount_required']]);
            }
        }
    }
}
