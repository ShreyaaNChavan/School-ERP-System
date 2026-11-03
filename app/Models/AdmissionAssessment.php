<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionAssessment extends Model
{
    protected $fillable = [
        'application_id',
        'assessment_type',
        'assessment_date',
        'score',
        'maximum_score',
        'result',
        'remarks',
        'assessed_by',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'score' => 'decimal:2',
        'maximum_score' => 'decimal:2',
    ];

    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
