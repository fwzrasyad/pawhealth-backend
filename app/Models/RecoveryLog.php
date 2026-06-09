<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecoveryLog extends Model
{
    use HasFactory;

    protected $table = 'recovery_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'recovery_plan_id',
        'date',
        'symptom_status',
        'owner_notes',
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'symptom_status' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RecoveryLog $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function recoveryPlan()
    {
        return $this->belongsTo(RecoveryPlan::class, 'recovery_plan_id', 'id');
    }
}
