<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'campus_id',
        'user_id',
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'designation',
        'department',
        'employment_type',
        'joining_date',
        'phone',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    public function attendanceRecords()
    {
        return $this->hasMany(EmployeeAttendance::class, 'staff_profile_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'staff_profile_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'staff_profile_id');
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class, 'staff_profile_id');
    }

    public function userProfile()
    {
        return $this->hasOneThrough(
            UserProfile::class,
            User::class,
            'id',
            'user_id',
            'user_id',
            'id'
        );
    }
}
