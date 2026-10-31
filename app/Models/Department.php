<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'head_staff_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function headStaff(): BelongsTo
    {
        return $this->belongsTo(
            StaffProfile::class,
            'head_staff_id'
        );
    }
}
