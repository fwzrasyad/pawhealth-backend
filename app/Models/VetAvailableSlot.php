<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VetAvailableSlot extends Model
{
    use HasFactory;

    protected $table = 'vet_available_slots';

    protected $fillable = [
        'vet_id',
        'slot_datetime',
    ];

    protected function casts(): array
    {
        return [
            'slot_datetime' => 'datetime',
        ];
    }

    // ── Relationships ──

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class, 'vet_id', 'vet_id');
    }
}
