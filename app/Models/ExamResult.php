<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_subject_id',
        'student_id',
        'marks_obtained',
        'grade',
        'remarks',
        'entered_by',
        'published_at',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function examSubject()
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
