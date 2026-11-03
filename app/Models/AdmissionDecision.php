<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionDecision extends Model
{
    protected $fillable = [
        'application_id',
        'decision',
        'remarks',
        'decided_by',
        'decided_at',
        'offer_expires_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'offer_expires_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    public function decisionMaker()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
