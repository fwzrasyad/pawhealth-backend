<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pet extends Model
{
    use HasFactory;

    protected $table = 'pets';
    protected $primaryKey = 'pet_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'pet_id',
        'owner_id',
        'name',
        'species',
        'breed',
        'age',
        'gender',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'age'    => 'integer',
            'weight' => 'double',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Pet $pet) {
            if (empty($pet->pet_id)) {
                $pet->pet_id = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'pet_id', 'pet_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'pet_id', 'pet_id');
    }

    public function dailyRoutineLogs()
    {
        return $this->hasMany(DailyRoutineLog::class, 'pet_id', 'pet_id');
    }

    public function aiScans()
    {
        return $this->hasMany(AIScan::class, 'pet_id', 'pet_id');
    }
}
