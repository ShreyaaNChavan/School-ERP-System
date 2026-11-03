<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounsellingRecord extends Model
{
    protected $table = 'counselling_records';

    protected $fillable = [
        'school_id',
        'student_id',
        'admission_application_id',
        'counsellor_id',
        'session_at',
        'session_type',
        'summary',
        'action_plan',
        'next_session_at',
        'status',
    ];

    protected $casts = [
        'session_at' => 'datetime',
        'next_session_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function admissionApplication()
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function counsellor()
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }

    public function getSessionNumberAttribute()
    {
        return 'COUN-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}