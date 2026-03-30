<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'school_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_location',
        'clock_out_location',
        'clock_in_method',
        'clock_out_method',
        'clocked_in_by',
        'clocked_out_by',
        'admin_note',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'clock_in_location' => 'array',
        'clock_out_location' => 'array',
        'date' => 'date',
    ];

    /**
     * Get the user (Teacher/Student) associated with the attendance record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school where the attendance occurred.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the Admin User who manually clocked this user in (if applicable).
     */
    public function clockedInByAdmin()
    {
        return $this->belongsTo(User::class, 'clocked_in_by');
    }

    /**
     * Get the Admin User who manually clocked this user out (if applicable).
     */
    public function clockedOutByAdmin()
    {
        return $this->belongsTo(User::class, 'clocked_out_by');
    }
}
