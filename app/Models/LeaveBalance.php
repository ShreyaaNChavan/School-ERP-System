<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'staff_profile_id',
        'leave_type_id',
        'academic_year_id',
        'allocated_days',
        'used_days',
        'adjusted_days',
    ];

    protected function casts(): array
    {
        return [
            'allocated_days' => 'decimal:2',
            'used_days' => 'decimal:2',
            'adjusted_days' => 'decimal:2',
        ];
    }

    public function staff()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}