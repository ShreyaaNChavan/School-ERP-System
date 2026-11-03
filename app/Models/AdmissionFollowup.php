<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionFollowup extends Model
{
    protected $fillable = [
        'admission_lead_id',
        'assigned_to',
        'followup_type',
        'notes',
        'scheduled_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(
            AdmissionLead::class,
            'admission_lead_id'
        );
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
