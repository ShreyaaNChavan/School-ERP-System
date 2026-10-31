<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumTopic extends Model
{
    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'estimated_periods',
        'status',
        'sequence',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_periods' => 'integer',
            'sequence' => 'integer',
            'completed_at' => 'date',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(
            CurriculumChapter::class,
            'chapter_id'
        );
    }
}
