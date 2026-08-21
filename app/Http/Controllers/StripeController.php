<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function generatePaymentLink($business_id, Request $request)
    {
        try {
            if (!$request->user()->hasRole('superadmin')) {
                return response()->json(["message" => "Unauthorized"], 401);
            }

            $business = Business::findOrFail($business_id);
            
            // Return the frontend link where the business can pay via the implemented Stripe elements
            $frontendUrl = env('FRONTEND_URL') . '/payment?business_id=' . $business->id;

            return response()->json(['url' => $frontendUrl], 200);

        } catch (\Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    public function createIntent($business_id, Request $request)
    {
        try {
            $business = Business::findOrFail($business_id);
            
            // Check authorization: Must be superadmin or the owner of the business
            if (!$request->user()->hasRole('superadmin') && $business->owner_id !== $request->user()->id) {
                return response()->json(["message" => "Unauthorized"], 401);
            }

            $stripe = new StripeClient(env('STRIPE_SECRET'));

            // Create or retrieve Stripe Customer
            if (!$business->stripe_customer_id) {
                $customer = $stripe->customers->create([
                    'email' => $business->email,
                    'name' => $business->name,
                    'metadata' => [
                        'business_id' => $business->id
                    ]
                ]);
                $business->stripe_customer_id = $customer->id;
                $business->save();
            }

            // Create a SetupIntent to save the card for future usage without immediately charging
            $setupIntent = $stripe->setupIntents->create([
                'customer' => $business->stripe_customer_id,
                'payment_method_types' => ['card'],
                'usage' => 'off_session',
                'metadata' => [
                    'business_id' => $business->id
                ]
            ]);

            return response()->json(['client_secret' => $setupIntent->client_secret], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request)
    {
        // Implementation for webhook to update business subscription status
        return response()->json(['status' => 'success']);
    }
}
