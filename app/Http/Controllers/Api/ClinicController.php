<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * GET /api/clinics
     *
     * Return a list of clinics, with optional filtering:
     *   - ?city=Jakarta        → filter by city
     *   - ?state=DKI+Jakarta   → filter by state
     *   - ?lat=...&lng=...&radius=10  → radius search (km)
     */
    public function index(Request $request)
    {
        $query = Clinic::query()->where('status', 'approved');

        // ── City / State filtering ──
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->query('city') . '%');
        }

        if ($request->filled('state')) {
            $query->where('state', 'like', '%' . $request->query('state') . '%');
        }

        // ── Radius search using Haversine formula ──
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat    = (float) $request->query('lat');
            $lng    = (float) $request->query('lng');
            $radius = (float) ($request->query('radius', 25)); // default 25 km

            $query->selectRaw('
                clinics.*,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude))
                )) AS distance
            ', [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        } else {
            $query->orderBy('name');
        }

        $clinics = $query->get();

        return response()->json([
            'data' => $clinics->map(function ($clinic) {
                $result = [
                    'clinic_id'   => $clinic->clinic_id,
                    'name'        => $clinic->name,
                    'address'     => $clinic->address,
                    'city'        => $clinic->city,
                    'state'       => $clinic->state,
                    'latitude'    => $clinic->latitude,
                    'longitude'   => $clinic->longitude,
                    'phone'       => $clinic->phone,
                    'description' => $clinic->description,
                    'created_at'  => $clinic->created_at?->toIso8601String(),
                    'updated_at'  => $clinic->updated_at?->toIso8601String(),
                ];

                // Include distance if radius search was performed
                if (isset($clinic->distance)) {
                    $result['distance_km'] = round($clinic->distance, 2);
                }

                return $result;
            }),
        ]);
    }

    /**
     * GET /api/clinics/{clinicId}/vets
     *
     * Return all approved veterinarians working at this clinic.
     * Joins users + veterinarians tables to provide full vet profile data.
     */
    public function getVets(string $clinicId)
    {
        $clinic = Clinic::where('clinic_id', $clinicId)->firstOrFail();

        // Get user_ids for vets at this clinic
        $vetUserIds = User::where('clinic_id', $clinicId)
            ->where('role', 'vet')
            ->pluck('user_id');

        // Fetch full vet profiles (veterinarians table is keyed by vet_id = user_id)
        $vets = Veterinarian::whereIn('vet_id', $vetUserIds)
            ->where('status', 'approved')
            ->with(['user', 'appointments' => function ($query) {
                $query->whereIn('status', ['pending', 'confirmed']);
            }])
            ->get();

        return response()->json([
            'clinic' => [
                'clinic_id' => $clinic->clinic_id,
                'name'      => $clinic->name,
            ],
            'data' => $vets->map(function ($vet) {
                return [
                    'vet_id'            => $vet->vet_id,
                    'name'              => $vet->name,
                    'email'             => $vet->user?->email ?? '',
                    'phone_number'      => $vet->user?->phone_number ?? '',
                    'profile_image_url' => $vet->profile_image_url,
                    'working_hours'     => $vet->working_hours,
                    'specialties'       => $vet->specialties ?? [],
                    'bio'               => $vet->bio,
                    'status'            => $vet->status,
                    'weekly_schedule'   => $vet->weekly_schedule ?? [],
                    'booked_slots'      => $vet->appointments->pluck('time_slot')->map(fn ($dt) => \Carbon\Carbon::parse($dt)->format('Y-m-d H:i:s'))->values()->all(),
                ];
            }),
        ]);
    }
}
