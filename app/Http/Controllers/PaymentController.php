<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        try {
            $user = request()->user();
            $paymentMethodId = request()->input('paymentMethodID');
            $amount = request()->input('amount');
            $productId = request()->input('product');
            $fillInfo = request()->input('fillInfo');

            $stripeCharge = $user->charge(
                (int)$amount * 100,
                $paymentMethodId
            );

            // Add address to database
            $addressInsert = [
                'user_id' => $user->id,
                'name' => $fillInfo['address']['name'],
                'email' => $fillInfo['email'],
                'phone' => $fillInfo['address']['phone'] ?? '',
                'state' => $fillInfo['address']['state'],
                'city' => $fillInfo['address']['city'],
                'zip' => $fillInfo['address']['postal_code'],
                'address' => $fillInfo['address']['apartment'] . ' ' . $fillInfo['address']['address'],
            ];
            $address = $user->addresses()->create($addressInsert);
            // Add order to database

            $product = Product::find($productId);
            $user->orders()->create([
                'user_id' => $user->id,
                'seller_id' => $product->user_id,
                'product_id' => $productId,
                'amount' => $amount,
                'address_id' => $address->id,
                'payment_method' => 'stripe',
                'payment_id' => $stripeCharge->id,
                'payment_status' => $stripeCharge->status,
                'is_paid' => true,
                'is_delivered' => false,
                'is_reviewed' => false,
                'status' => 'paid'
            ]);

            $product->update([
                'status' => 'sold'
            ]);

            return response()->json([
                'success' => true,
                'data' => $stripeCharge
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function intent()
    {
        $payment = request()->user()->pay(
            request()->get('amount')
        );

        return $payment->client_secret;
    }

    public function app_buy()
    {

        // required int product,
        // required double amount,
        // required String email,
        // required String mobilephone,
        // required String address,

        try {
            $user = request()->user();
            $amount = request()->input('amount');
            $productId = request()->input('product');
            $email = request()->input('email');
            $mobilephone = request()->input('mobilephone');
            $address = request()->input('address');

            // Add address to database
            $addressInsert = [
                'user_id' => $user->id,
                'name' => 'App User-Check address',
                'email' => $email,
                'phone' => $mobilephone,
                'state' => 'App State-Check address',
                'city' => 'App User-Check address',
                'zip' => 'App User-Check address',
                'address' => $address,
            ];
            logger($addressInsert);
            $address = $user->addresses()->create($addressInsert);
            // Add order to database

            $product = Product::find($productId);
            $user->orders()->create([
                'user_id' => $user->id,
                'seller_id' => $product->user_id,
                'product_id' => $productId,
                'amount' => $amount,
                'address_id' => $address->id,
                'payment_method' => 'app',
                'payment_id' => '',
                'payment_status' => 'succeeded',
                'is_paid' => true,
                'is_delivered' => false,
                'is_reviewed' => false,
                'status' => 'paid'
            ]);

            $product->update([
                'status' => 'sold'
            ]);

            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
