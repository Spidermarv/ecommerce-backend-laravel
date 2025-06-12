<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $products = Product::all();

        if ($orders->isEmpty() || $products->isEmpty()) {
            $this->command->info('No orders or products found to seed order items.');
            return;
        }

        foreach ($orders as $order) {
            $product1 = $products->random();
            $product2 = $products->where('id', '!=', $product1->id)->random();

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product1->id,
                'quantity' => rand(1, 3),
                'price' => $product1->price,
            ]);

            if ($product2) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product2->id,
                    'quantity' => rand(1, 2),
                    'price' => $product2->price,
                ]);
            }

            // Recalculate order total
            $order->total_amount = $order->items()->sum(\DB::raw('price * quantity'));
            $order->save();
        }
    }
}
