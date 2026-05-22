<?php

use App\Http\Controllers\Api\AIScanController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\HealthJournalController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\Api\SuperAdminController;
use App\Http\Controllers\Api\VeterinarianController;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | All routes are prefixed with /api automatically by Laravel. | Protected by Firebase token authentication middleware. | */



// Public manager registration
Route::post('/manager/register', [ManagerController::class, 'registerClinic']);

Route::middleware('firebase.auth')->group(function () {

    // ── Auth ──
    Route::post('/auth/sync', [AuthController::class , 'sync']);

    // ── Clinics ──
    Route::get('/clinics', [ClinicController::class, 'index']);
    Route::get('/clinics/{clinicId}/vets', [ClinicController::class, 'getVets']);

    // ── Pets (CRUD) ──
    Route::get('/pets', [PetController::class , 'index']);
    Route::post('/pets', [PetController::class , 'store']);
    Route::get('/pets/{petId}', [PetController::class , 'show']);
    Route::put('/pets/{petId}', [PetController::class , 'update']);
    Route::delete('/pets/{petId}', [PetController::class , 'destroy']);

    // ── Health Journals (nested under pets — targeted acute recovery) ──
    Route::get('/pets/{petId}/health-journals', [HealthJournalController::class , 'index']);
    Route::post('/pets/{petId}/health-journals', [HealthJournalController::class , 'store']);

    // ── Medical Records (nested index under pets, standalone store/update) ──
    Route::get('/pets/{petId}/medical-records', [MedicalRecordController::class , 'index']);
    Route::post('/medical-records', [MedicalRecordController::class , 'store']);
    Route::put('/medical-records/{id}', [MedicalRecordController::class , 'update']);

    // ── AI Scans (nested under pets) ──
    Route::get('/pets/{petId}/ai-scans', [AIScanController::class , 'index']);
    Route::post('/pets/{petId}/ai-scans', [AIScanController::class , 'store']);

    // ── Veterinarians ──
    Route::get('/veterinarians', [VeterinarianController::class , 'index']);
    Route::post('/veterinarians', [VeterinarianController::class , 'store']);
    Route::get('/veterinarians/me', [VeterinarianController::class , 'me']);
    Route::put('/veterinarians/{id}', [VeterinarianController::class , 'update']);
    Route::get('/veterinarians/{vetId}', [VeterinarianController::class , 'show']);

    // ── Appointments ──
    Route::post('/appointments', [AppointmentController::class , 'store']);
    Route::get('/appointments', [AppointmentController::class , 'index']);
    Route::put('/appointments/{id}', [AppointmentController::class , 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class , 'destroy']);

    // ── Manager Portal ──
    Route::prefix('manager')->group(function () {
        Route::get('/stats', [ManagerController::class, 'dashboardStats']);
        Route::get('/users', [ManagerController::class, 'listUsers']);
        Route::delete('/users/{id}', [ManagerController::class, 'deleteUser']);
        Route::get('/users/{id}/pets', [ManagerController::class, 'userPets']);
        Route::get('/veterinarians', [ManagerController::class, 'listVeterinarians']);
        Route::post('/veterinarians', [ManagerController::class, 'storeVeterinarian']);
        Route::put('/veterinarians/{id}', [ManagerController::class, 'updateVeterinarian']);
        Route::put('/appointments/{id}/assign', [ManagerController::class, 'assignVet']);
        Route::get('/appointments', [ManagerController::class, 'listAppointments']);
    });

    // ── Super Admin Portal ──
    Route::prefix('admin')->middleware('super_admin')->group(function () {
        Route::get('/pending-clinics', [SuperAdminController::class, 'pendingClinics']);
        Route::patch('/clinics/{clinic}/approve', [SuperAdminController::class, 'approve']);
        Route::patch('/clinics/{clinic}/reject', [SuperAdminController::class, 'reject']);
    });
});
