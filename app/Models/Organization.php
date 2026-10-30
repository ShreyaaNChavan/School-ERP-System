<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'registration_number',
        'primary_email',
        'primary_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'timezone',
        'status',
    ];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
