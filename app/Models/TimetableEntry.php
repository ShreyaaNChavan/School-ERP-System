<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEntry extends Model
{
    protected $fillable = [
        'timetable_id',
        'timetable_period_id',
        'class_subject_id',
        'staff_profile_id',
        'day_of_week',
        'room_number',
        'notes',
    ];

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(
            TimetablePeriod::class,
            'timetable_period_id'
        );
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(
            StaffProfile::class,
            'staff_profile_id'
        );
    }
}
