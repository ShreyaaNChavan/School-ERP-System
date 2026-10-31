<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumChapter extends Model
{
    protected $fillable = [
        'curriculum_id',
        'chapter_number',
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
            'chapter_number' => 'integer',
            'estimated_periods' => 'integer',
            'sequence' => 'integer',
            'completed_at' => 'date',
        ];
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(CurriculumTopic::class, 'chapter_id')
            ->orderBy('sequence');
    }
}
