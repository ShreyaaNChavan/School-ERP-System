<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'relationship',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'occupation',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            StudentProfile::class,
            'student_guardians',
            'guardian_id',
            'student_id'
        )->withPivot([
            'is_primary',
            'is_emergency_contact',
            'can_pick_up_student',
            'receives_communications',
        ])->withTimestamps();
    }
}
