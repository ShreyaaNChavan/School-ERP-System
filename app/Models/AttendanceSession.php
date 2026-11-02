<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $fillable = [
        'school_id', 'campus_id', 'academic_year_id', 'section_id',
        'recorded_by', 'attendance_date', 'session_name', 'subject_id', 'status',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function campus(): BelongsTo { return $this->belongsTo(Campus::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }
    public function records(): HasMany { return $this->hasMany(StudentAttendance::class); }
}
