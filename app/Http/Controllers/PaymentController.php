<?php

namespace App\Http\Controllers;

use Exception;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\CardException;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.api_keys.secret_key'));
    }

    public function index()
    {
        return view('payment');
    }

    public function payment(Request $request)
{
    \Log::info('Payment Request:', $request->all());

    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'fullName' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 400);
    }

    try {
        $charge = $this->stripe->charges->create([
            'amount' => 2000, // $20 in cents
            'currency' => 'usd',
            'source' => $request->token,
            'description' => 'My first payment',
        ]);

        if ($charge['status'] === 'succeeded') {
            return response()->json(['message' => 'Payment completed successfully!'], 200);
        } else {
            return response()->json(['message' => 'Payment failed.'], 400);
        }
    } catch (\Exception $e) {
        Log::error('Payment Error:', ['error' => $e->getMessage()]);
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

}
