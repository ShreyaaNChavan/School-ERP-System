<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetablePeriod extends Model
{
    protected $fillable = [
        'timetable_id',
        'period_number',
        'name',
        'period_type',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
