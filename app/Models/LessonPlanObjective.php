<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPlanObjective extends Model
{
    protected $fillable = [
        'lesson_plan_id',
        'title',
        'description',
        'sequence',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function lessonPlan(): BelongsTo
    {
        return $this->belongsTo(LessonPlan::class);
    }
}
