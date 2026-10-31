<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'staff_profile_id',
        'allowances',
        'deductions',
        'gross_salary',
        'net_salary',
        'payment_status',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allowances' => 'decimal:2',
            'deductions' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function staff()
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function components()
    {
        return $this->hasMany(PayrollComponent::class);
    }
}