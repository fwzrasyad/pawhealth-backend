<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'role',
        'phone_number',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ── Relationships ──

    public function pets()
    {
        return $this->hasMany(Pet::class, 'owner_id', 'user_id');
    }

    public function veterinarian()
    {
        return $this->hasOne(Veterinarian::class, 'vet_id', 'user_id');
    }
}
