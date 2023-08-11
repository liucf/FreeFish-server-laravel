<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function delivery()
    {
        $user = request()->user();
        $productId = request()->input('productId');
        $orderId = request()->input('orderId');
        $delivery = request()->input('delivery');

        $order = Order::find($orderId);
        $order->update([
            'is_delivered' => true,
            'status' => 'shipped',
            'delivered_by' => $delivery['delivered_by'],
            'tracking_number' => $delivery['tracking_number'],
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function confirm()
    {
        $user = request()->user();
        $orderId = request()->input('orderId');
        $order = Order::find($orderId);
        $order->update([
            'is_confirmed' => true,
            'status' => 'delivered',
        ]);

        $seller = User::find($order->seller_id);
        $seller->update([
            'balance' => $seller->balance + $order->amount,
        ]);
        return response()->json([
            'success' => true,
        ]); 
    }

    public function review()
    {
        $user = request()->user();
        $orderId = request()->input('orderId');
        $review = request()->input('review');
        $order = Order::find($orderId);
        $order->update([
            'is_reviewed' => true,
            'status' => 'reviewed',
        ]);

        Review::create([
            'author_id' => $user->id,
            'seller_id' => $order->seller_id,
            'product_id' => $order->product_id,
            'order_id' => $order->id,
            'rating' => $review['rating'],
            'content' => $review['content'],
        ]);

        $seller = User::find($order->seller_id);
        $seller->update([
            'rating' => (int)( ($seller->rating) * ($seller->rating_count /  ($seller->rating_count+1 ) )) + ($review['rating'] * (1 / ($seller->rating_count+1))),
            'rating_count' => $seller->rating_count + 1,
        ]);

        return response()->json([
            'success' => true,
        ]); 
    }

  
}
