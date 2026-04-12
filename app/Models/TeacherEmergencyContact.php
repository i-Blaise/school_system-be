<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TeacherEmergencyContact extends Model
{
    use HasUuids;

    protected $fillable = [
        'teacher_profile_id',
        'name',
        'relation',
        'phone_country_code',
        'phone',
    ];

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}
