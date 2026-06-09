<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veterinarian extends Model
{
    use HasFactory;

    protected $table = 'veterinarians';
    protected $primaryKey = 'vet_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'vet_id',
        'name',
        'profile_image_url',
        'working_hours',
        'specialties',
        'bio',
        'status',
        'weekly_schedule',
        'consultation_fee',
    ];

    protected function casts(): array
    {
        return [
            'specialties'      => 'array',
            'weekly_schedule'  => 'array',
            'consultation_fee' => 'decimal:2',
        ];
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class, 'vet_id', 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'vet_id', 'vet_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'vet_id', 'vet_id');
    }

    public function availableSlots()
    {
        return $this->hasMany(VetAvailableSlot::class, 'vet_id', 'vet_id');
    }
}
