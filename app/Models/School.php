<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'slug',
        'school_type',
        'affiliation_number',
        'email',
        'phone',
        'timezone',
        'default_currency',
        'status',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campuses()
    {
        return $this->hasMany(Campus::class);
    }
}