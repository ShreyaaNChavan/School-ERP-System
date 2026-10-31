<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'school_class_id',
        'subject_id',
        'title',
        'description',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
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

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(CurriculumChapter::class)
            ->orderBy('sequence');
    }

    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->chapters()
            ->withCount('topics')
            ->get()
            ->sum('topics_count');

        if ($total === 0) {
            return 0;
        }

        $completed = CurriculumTopic::whereHas(
            'chapter',
            fn ($query) => $query->where('curriculum_id', $this->id)
        )->where('status', 'completed')->count();

        return (int) round(($completed / $total) * 100);
    }
}
