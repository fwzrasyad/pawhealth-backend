<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AIScan extends Model
{
    use HasFactory;

    protected $table = 'ai_scans';
    protected $primaryKey = 'scan_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'scan_id',
        'pet_id',
        'scan_date',
        'image_url',
        'ai_result_label',
        'confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'scan_date'        => 'datetime',
            'confidence_score' => 'double',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AIScan $scan) {
            if (empty($scan->scan_id)) {
                $scan->scan_id = (string) Str::uuid();
            }
        });
    }

    // ── Relationships ──

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id', 'pet_id');
    }
}
