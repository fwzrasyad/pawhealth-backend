<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecoveryPlan extends Model
{
    use HasFactory;

    protected $table = 'recovery_plans';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pet_id',
        'appointment_id',
        'vet_id',
        'instructions',
        'duration_days',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RecoveryPlan $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class, 'vet_id', 'vet_id');
    }

    public function recoveryLogs()
    {
        return $this->hasMany(RecoveryLog::class, 'recovery_plan_id', 'id');
    }
}
