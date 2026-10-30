<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campus extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'phone',
        'timezone',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}