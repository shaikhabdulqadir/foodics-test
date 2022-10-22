<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\User;
use App\Notifications\StockAlert;
use Database\Seeders\AddProductsSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_create_order_api_error_without_payload()
    {
        $this->login();
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post('/api/order/create');
        $response->assertStatus(422);
    }

    public function test_create_order_api()
    {
        $this->login();

        $product = Product::first();

        $data = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post('/api/order/create', $data);

        $response->assertStatus(200);
    }

    public function test_check_stock_updated_correctly()
    {
        $product = Product::first();
        $ingredients = $product->ingredients;
        $product = Product::first();

        $data = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/api/order/create', $data);

        $response->assertStatus(200);

        // $this->assertTrue(false);

        foreach ($ingredients as  $ingredient) {
            $ingredientItem = ProductIngredient::where('ingredient_id', $ingredient->id)->where('product_id', $product->id)->first();

            $this->assertDatabaseHas('ingredients', [
                'id' => $ingredient->id,
                'available' => $ingredient->available - $ingredientItem->amount_required
            ]);
        }
    }

    public function test_notification_sent_on_stock_less_than_50_percent()
    {
        $product = Product::first();
        $ingredientItem = $product->ingredientItems()->where('amount_required', '>', 0)->first();

        $ingredient = $ingredientItem->ingredient;
        $ingredient->available = $ingredient->total / 2;
        $ingredient->save();

        Notification::fake();

        $product = Product::first();

        $data = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post('/api/order/create', $data);

        Notification::assertSentTo(
            [$product->merchant],
            StockAlert::class
        );
    }

    public function login($attributes = [])
    {
        $user = User::factory()->create($attributes);
        auth()->login($user);
    }
}
