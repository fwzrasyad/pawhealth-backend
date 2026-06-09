<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * GET /api/admin/pending-clinics
     * Returns all clinics with status === 'pending', including their license file path.
     */
    public function pendingClinics()
    {
        $clinics = Clinic::where('status', 'pending')
            ->with(['users' => function ($q) {
                $q->where('role', 'manager');
            }])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $clinics->map(function ($clinic) {
                $manager = $clinic->users->first();
                return [
                    'clinic_id'         => $clinic->clinic_id,
                    'name'              => $clinic->name,
                    'address'           => $clinic->address,
                    'city'              => $clinic->city,
                    'state'             => $clinic->state,
                    'phone'             => $clinic->phone,
                    'status'            => $clinic->status,
                    'license_file_path' => $clinic->license_file_path,
                    'license_file_url'  => $clinic->license_file_path,
                    'manager_name'      => $manager?->name ?? '—',
                    'manager_email'     => $manager?->email ?? '—',
                    'created_at'        => $clinic->created_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * PATCH /api/admin/clinics/{clinic}/approve
     * Updates the clinic status to 'approved'.
     */
    public function approve(string $clinicId)
    {
        $clinic = Clinic::where('clinic_id', $clinicId)->firstOrFail();

        $clinic->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Clinic approved successfully.',
            'data'    => [
                'clinic_id' => $clinic->clinic_id,
                'name'      => $clinic->name,
                'status'    => $clinic->status,
            ],
        ]);
    }

    /**
     * PATCH /api/admin/clinics/{clinic}/reject
     * Updates the clinic status to 'rejected'.
     */
    public function reject(string $clinicId)
    {
        $clinic = Clinic::where('clinic_id', $clinicId)->firstOrFail();

        $clinic->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Clinic rejected.',
            'data'    => [
                'clinic_id' => $clinic->clinic_id,
                'name'      => $clinic->name,
                'status'    => $clinic->status,
            ],
        ]);
    }
}
