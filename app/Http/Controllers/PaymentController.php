<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $paymentMethods = $user->paymentMethods();
        dd($paymentMethods);
        return view('payment.index');
    }
}
