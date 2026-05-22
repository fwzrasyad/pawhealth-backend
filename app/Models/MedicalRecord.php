<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_records';
    protected $primaryKey = 'record_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'record_id',
        'pet_id',
        'vet_id',
        'appointment_id',
        'diagnosis',
        'doctor_notes',
        'medications_prescribed',
        'follow_up_instructions',
    ];

    protected function casts(): array
    {
        return [
            'medications_prescribed' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MedicalRecord $record) {
            if (empty($record->record_id)) {
                $record->record_id = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class, 'vet_id', 'vet_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
}
