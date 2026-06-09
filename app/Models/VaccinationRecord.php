<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VaccinationRecord extends Model
{
    use HasFactory;

    protected $table = 'vaccination_records';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pet_id',
        'record_id',
        'administered_by_vet_id',
        'vaccine_name',
        'is_core',
        'date_administered',
        'next_due_date',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'date_administered' => 'date',
            'next_due_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VaccinationRecord $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class, 'record_id', 'record_id');
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class, 'administered_by_vet_id', 'vet_id');
    }
}
