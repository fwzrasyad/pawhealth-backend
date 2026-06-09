<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecoveryPlan;
use App\Models\RecoveryLog;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RecoveryController extends Controller
{
    public function getPlans($petId)
    {
        $plans = RecoveryPlan::where('pet_id', $petId)
            ->with('recoveryLogs')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $plans]);
    }

    public function storePlan(Request $request, $petId)
    {
        $validator = Validator::make($request->all(), [
            'instructions' => 'required|string',
            'duration_days' => 'required|integer|min:1',
            'appointment_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pet = Pet::where('pet_id', $petId)->first();
        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        $user = $request->user();
        if (!$user->veterinarian) {
            return response()->json(['message' => 'Only veterinarians can create recovery plans'], 403);
        }

        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays($request->duration_days);

        $plan = RecoveryPlan::create([
            'pet_id' => $petId,
            'vet_id' => $user->veterinarian->vet_id,
            'appointment_id' => $request->appointment_id,
            'instructions' => $request->instructions,
            'duration_days' => $request->duration_days,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'active',
        ]);

        return response()->json(['data' => $plan], 201);
    }

    public function storeLog(Request $request, $planId)
    {
        $plan = RecoveryPlan::where('id', $planId)->first();
        if (!$plan) {
            return response()->json(['message' => 'Recovery plan not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'symptom_status' => 'nullable|array',
            'owner_notes' => 'nullable|string',
            'photo_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $log = RecoveryLog::create([
            'recovery_plan_id' => $planId,
            'date' => Carbon::now()->toDateString(),
            'symptom_status' => $request->symptom_status,
            'owner_notes' => $request->owner_notes,
            'photo_url' => $request->photo_url,
        ]);

        return response()->json(['data' => $log], 201);
    }
}
