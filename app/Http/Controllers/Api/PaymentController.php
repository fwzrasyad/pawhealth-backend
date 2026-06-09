<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Veterinarian;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'vet_id' => 'required|string|exists:veterinarians,vet_id',
            'clinic_id' => 'required|string|exists:clinics,clinic_id',
            'pet_id' => 'required|string|exists:pets,pet_id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|date',
        ]);

        // Check for double booking
        $isDoubleBooked = Appointment::where('vet_id', $request->vet_id)
            ->where('time_slot', $request->time_slot)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($isDoubleBooked) {
            return response()->json(['message' => 'This time slot is no longer available.'], 409);
        }

        $vet = Veterinarian::findOrFail($request->vet_id);
        
        if (empty($vet->consultation_fee) || $vet->consultation_fee <= 0) {
            return response()->json(['message' => 'Veterinarian has not set a valid consultation fee.'], 400);
        }

        // Setup Stripe
        Stripe::setApiKey(config('stripe.secret_key'));

        try {
            // Amount is in the smallest currency unit (MYR -> Sen, so multiply by 100)
            $amountInSen = (int) round($vet->consultation_fee * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInSen,
                'currency' => 'myr',
                'payment_method_types' => ['card', 'fpx', 'grabpay'],
                'metadata' => [
                    'vet_id' => $vet->vet_id,
                    'pet_id' => $request->pet_id,
                    'clinic_id' => $request->clinic_id,
                    'time_slot' => $request->time_slot,
                ],
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $vet->consultation_fee,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to initialize payment.'], 500);
        }
    }

    public function confirmBooking(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'clinic_id' => 'required|string|exists:clinics,clinic_id',
            'pet_id' => 'required|string|exists:pets,pet_id',
            'pet_name' => 'required|string',
            'vet_id' => 'required|string|exists:veterinarians,vet_id',
            'vet_name' => 'required|string',
            'reason' => 'nullable|string',
            'appointment_date' => 'required|date',
            'time_slot' => 'required|date',
            'consultation_type' => 'nullable|string|in:in_person,virtual',
        ]);

        // Setup Stripe
        Stripe::setApiKey(config('stripe.secret_key'));

        try {
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json(['message' => 'Payment not successful. Please try again.'], 400);
            }

            // Create appointment
            $appointment = Appointment::create([
                ...$validated,
                'status' => 'pending',
                'amount' => $paymentIntent->amount / 100,
                'payment_intent_id' => $validated['payment_intent_id'],
                'payment_status' => 'paid',
            ]);

            return response()->json([
                'message' => 'Booking confirmed successfully',
                'appointment' => new \App\Http\Resources\AppointmentResource($appointment),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Stripe Retrieve PaymentIntent Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to confirm booking.'], 500);
        }
    }
}
