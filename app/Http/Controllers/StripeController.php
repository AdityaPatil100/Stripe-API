<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function checkout()
    {
        return view('checkout');
    }

    public function createPaymentIntent(Request $request)
    {
        $amount = $request->input('amount') * 100;

        Stripe::setApiKey(config('stripe.test.sk'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
