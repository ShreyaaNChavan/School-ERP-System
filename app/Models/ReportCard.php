<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    protected $fillable = [
        'student_id',
        'examination_id',
        'total_marks',
        'percentage',
        'overall_grade',
        'rank',
        'teacher_remarks',
        'principal_remarks',
        'published_at',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'rank' => 'integer',
        'published_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function examination()
    {
        return $this->belongsTo(Examination::class, 'examination_id');
    }
}