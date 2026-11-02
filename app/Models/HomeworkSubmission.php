<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    protected $fillable = [
        'homework_assignment_id', 'student_id', 'submission_path',
        'submission_text', 'submitted_at', 'marks_obtained',
        'teacher_feedback', 'reviewed_by', 'reviewed_at', 'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'marks_obtained' => 'decimal:2',
    ];

    public function homework(): BelongsTo
    {
        return $this->belongsTo(HomeworkAssignment::class, 'homework_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
