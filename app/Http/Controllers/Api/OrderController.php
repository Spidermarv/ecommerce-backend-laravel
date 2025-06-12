<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authenticatedUser = Auth::user();
        $rules = [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];

        $messages = [];

        if (!$authenticatedUser) {
            // For guest users, customer details are required
            $rules['customer_name'] = 'required|string|max:255';
            $rules['customer_email'] = 'required|email|max:255';
            $messages['customer_name.required'] = 'Customer name is required for guest orders.';
            $messages['customer_email.required'] = 'Customer email is required for guest orders.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $orderItemsData = [];

            $userId = null;
            $customerName = $request->customer_name;
            $customerEmail = $request->customer_email;

            if ($authenticatedUser) {
                $userId = $authenticatedUser->id;
                $customerName = $authenticatedUser->name;
                $customerEmail = $authenticatedUser->email;
            }

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    // This should ideally not happen due to 'exists' validation, but as a safeguard:
                    DB::rollBack();
                    return response()->json(['message' => "Product with ID {$item['product_id']} not found."], 404);
                }

                // Check stock
                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    return response()->json(['message' => "Not enough stock for product: {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}."], 400);
                }

                $itemTotal = $product->price * $item['quantity'];
                $totalAmount += $itemTotal;
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price, // Price at the time of purchase
                ];
            }

            // All checks passed, now create order and then decrement stock

            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            $order->items()->createMany($orderItemsData);

            // Decrement stock after order items are successfully created
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']); // Find again to be safe, or use a collection from earlier
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();
            return response()->json($order->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }
    }
}
