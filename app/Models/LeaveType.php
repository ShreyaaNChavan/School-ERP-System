<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'annual_allowance',
        'carry_forward_allowed',
        'is_paid',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'annual_allowance' => 'decimal:2',
            'carry_forward_allowed' => 'boolean',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
}