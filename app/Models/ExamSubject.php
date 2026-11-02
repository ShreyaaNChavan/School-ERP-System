<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    protected $fillable = [
        'examination_id',
        'school_class_id',
        'section_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'maximum_marks',
        'passing_marks',
        'room_number',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'maximum_marks' => 'decimal:2',
        'passing_marks' => 'decimal:2',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}