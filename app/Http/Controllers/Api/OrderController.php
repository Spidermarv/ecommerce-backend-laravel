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
        // This is a simplified example. Add authentication and more robust validation.
        // For a real application, ensure the user is authenticated:
        // $user = Auth::user();
        // if (!$user) {
        //     return response()->json(['message' => 'Unauthenticated.'], 401);
        // }

        $validator = Validator::make($request->all(), [
            // 'user_id' is optional, if provided, it means a logged-in user is placing the order
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required_without:user_id|string|max:255',
            'customer_email' => 'required_without:user_id|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'customer_name.required_without' => 'Customer name is required for guest orders.',
            'customer_email.required_without' => 'Customer email is required for guest orders.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $orderItemsData = [];

            $customerName = $request->customer_name;
            $customerEmail = $request->customer_email;
            $userId = $request->user_id;

            if ($userId && $user = \App\Models\User::find($userId)) {
                $customerName = $user->name;
                $customerEmail = $user->email;
            }

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $itemTotal = $product->price * $item['quantity'];
                $totalAmount += $itemTotal;
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ];
            }

            $order = Order::create([
                'user_id' => $userId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            $order->items()->createMany($orderItemsData);

            DB::commit();
            return response()->json($order->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }
    }
}
