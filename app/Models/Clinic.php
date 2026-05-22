<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'clinics';
    protected $primaryKey = 'clinic_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'clinic_id',
        'name',
        'address',
        'city',
        'state',
        'latitude',
        'longitude',
        'phone',
        'description',
        'status',
        'license_file_path',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Clinic $clinic) {
            if (empty($clinic->clinic_id)) {
                $clinic->clinic_id = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function users()
    {
        return $this->hasMany(User::class, 'clinic_id', 'clinic_id');
    }

    public function veterinarians()
    {
        return $this->users()->where('role', 'vet');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id', 'clinic_id');
    }
}
