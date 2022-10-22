<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'products' => 'required',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required',
        ]);

        try {
            $user = auth()->user();
            $user = User::find(1);

            DB::beginTransaction();

            foreach ($request->products as $p) {

                $product = Product::findOrFail($p['product_id']);

                $order = Order::create(['user_id' => $user->id]);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $p['quantity'],
                ]);

                foreach ($product->ingredientItems as $ingredientItem) {
                    $ingredient = $ingredientItem->ingredient;

                    $availableStockBefore = $ingredient->availableStockPercentage();

                    if ($availableStockBefore <= 0) {
                        return response(['message' => "{$product->name} ingredients stock not available"], 406);
                    }

                    $ingredient->available -= $ingredientItem->amount_required;
                    $ingredient->save();

                    $availableStockAfter = $ingredient->availableStockPercentage();
                    if ($availableStockAfter < 50 && $availableStockBefore >= 50) {
                        $ingredientItem->product->merchant->notify(new StockAlert($ingredient));
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Order created successfully']);
        } catch (\Exception $ex) {
            return response()->json(['message' => "Something went wrong", 'ex' => $ex], 500);
        }
    }
}
