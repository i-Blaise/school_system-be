<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'school_id',
        'teacher_id',
        'employee_id',
        'department',
        'designation',
        'joining_date',
        'qualification',
        'subject_specialty',
        'date_of_birth',
        'phone',
        'phone_country_code',
        'address',
        'medical_condition_alert',
        'medical_condition_details',
        'status',
        'created_by',
        'twitter',
        'linkedin',
        'facebook',
        'employment_status',
    ];

    protected function casts(): array
    {
        return [
            'medical_condition_alert' => 'boolean',
            'joining_date' => 'date',
            'date_of_birth' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(TeacherEmergencyContact::class);
    }

    /**
     * Check if this profile is still a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
