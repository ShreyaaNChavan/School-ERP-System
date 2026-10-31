<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonPlan extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'term_id',
        'class_subject_id',
        'section_id',
        'staff_profile_id',
        'curriculum_topic_id',
        'created_by',
        'title',
        'description',
        'scheduled_date',
        'duration_minutes',
        'teaching_method',
        'teacher_notes',
        'status',
        'published_at',
        'completed_at',
        'rescheduled_at',
        'reschedule_reason',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'published_at' => 'datetime',
            'completed_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function curriculumTopic(): BelongsTo
    {
        return $this->belongsTo(CurriculumTopic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(LessonPlanObjective::class)
            ->orderBy('sequence');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LessonPlanActivity::class)
            ->orderBy('sequence');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonPlanResource::class);
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->objectives()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->objectives()
            ->where('is_completed', true)
            ->count();

        return (int) round(($completed / $total) * 100);
    }
}
