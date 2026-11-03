<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionLead extends Model
{
    protected $fillable = [
        'school_id',
        'campus_id',
        'student_name',
        'parent_name',
        'phone',
        'email',
        'prospective_grade',
        'source',
        'status',
        'assigned_to',
        'notes',
    ];

    public function followups()
    {
        return $this->hasMany(
            AdmissionFollowup::class,
            'admission_lead_id'
        );
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function applications()
    {
        return $this->hasMany(
            AdmissionApplication::class,
            'admission_lead_id'
        );
    }

    public function getLeadNumberAttribute()
    {
        return 'LEAD-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
