<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollComponent extends Model
{
    protected $fillable = [
        'payroll_record_id',
        'name',
        'component_type',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payrollRecord()
    {
        return $this->belongsTo(PayrollRecord::class);
    }
}