<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HealthJournal extends Model
{
    use HasFactory;

    protected $table = 'health_journals';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'pet_id',
        'date',
        'symptom_tags',
        'notes',
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'symptom_tags' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (HealthJournal $journal) {
            if (empty($journal->id)) {
                $journal->id = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }
}
