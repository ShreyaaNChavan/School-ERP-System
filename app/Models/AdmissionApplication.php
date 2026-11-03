<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionApplication extends Model
{
    protected $fillable = [
        'admission_lead_id',
        'school_id',
        'campus_id',
        'academic_year_id',
        'application_number',
        'applicant_name',
        'date_of_birth',
        'applying_grade',
        'parent_name',
        'parent_phone',
        'parent_email',
        'documents',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'reviewed_at' => 'datetime',
        'documents' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(AdmissionLead::class, 'admission_lead_id');
    }

    public function assessments()
    {
        return $this->hasMany(AdmissionAssessment::class, 'application_id');
    }

    public function decisions()
    {
        return $this->hasMany(AdmissionDecision::class, 'application_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
