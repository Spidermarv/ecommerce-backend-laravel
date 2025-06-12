<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@example.com')->first();
        $guestName = 'Guest User';
        $guestEmail = 'guest@example.com';

        if (!$adminUser) {
            $adminUser = User::factory()->create(['is_admin' => false, 'name' => 'Test User', 'email' => 'test@example.com']);
        }

        $products = Product::take(5)->get();

        if ($products->count() >= 2) {
            // Order for registered user
            $order1 = Order::create([
                'user_id' => $adminUser->id,
                'customer_name' => $adminUser->name,
                'customer_email' => $adminUser->email,
                'total_amount' => 0, // Will be updated by OrderItemSeeder or manually
                'status' => 'completed',
            ]);

            // Order for guest user
            $order2 = Order::create([
                'user_id' => null, // Guest
                'customer_name' => $guestName,
                'customer_email' => $guestEmail,
                'total_amount' => 0, // Will be updated
                'status' => 'pending',
            ]);
        }
    }
}
