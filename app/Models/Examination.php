<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examination extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'exam_type',
        'start_date',
        'end_date',
        'status',
        'instructions',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function examSubjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class);
    }
}