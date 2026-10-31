<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPlanActivity extends Model
{
    protected $fillable = [
        'lesson_plan_id',
        'title',
        'description',
        'activity_type',
        'duration_minutes',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'sequence' => 'integer',
        ];
    }

    public function lessonPlan(): BelongsTo
    {
        return $this->belongsTo(LessonPlan::class);
    }
}
