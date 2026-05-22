<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'appointment_id',
        'clinic_id',
        'pet_id',
        'pet_name',
        'vet_id',
        'vet_name',
        'reason',
        'appointment_date',
        'time_slot',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'datetime',
            'time_slot'        => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (empty($appointment->appointment_id)) {
                $appointment->appointment_id = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class, 'vet_id', 'vet_id');
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id', 'appointment_id');
    }
}
