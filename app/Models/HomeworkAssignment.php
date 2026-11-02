<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeworkAssignment extends Model
{
    protected $fillable = [
        'school_id', 'section_id', 'subject_id', 'staff_profile_id',
        'academic_year_id', 'title', 'instructions', 'assigned_date',
        'due_date', 'attachment_path', 'status',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
    ];

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(StaffProfile::class, 'staff_profile_id'); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function submissions(): HasMany { return $this->hasMany(HomeworkSubmission::class); }
}
