<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DailyRoutineLog extends Model
{
    use HasFactory;

    protected $table = 'daily_routine_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pet_id',
        'date',
        'weight',
        'diet_notes',
        'activity_level',
    ];

    protected function casts(): array
    {
        return [
            'date'   => 'date',
            'weight' => 'double',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DailyRoutineLog $log) {
            if (empty($log->id)) {
                $log->id = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }
}
