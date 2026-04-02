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
        'diagnosis',
        'treatment',
        'vaccination_date',
        'next_due_date',
        'attachment_url',
    ];

    protected function casts(): array
    {
        return [
            'vaccination_date' => 'date',
            'next_due_date'    => 'date',
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
}
