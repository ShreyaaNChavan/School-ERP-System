<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timetable extends Model
{
    protected $fillable = [
        'school_id',
        'campus_id',
        'academic_year_id',
        'school_class_id',
        'section_id',
        'created_by',
        'name',
        'version',
        'effective_from',
        'effective_until',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(TimetablePeriod::class)
            ->orderBy('period_number');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }
}
