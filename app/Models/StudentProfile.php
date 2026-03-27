<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'school_id', 'class_name', 'admission_number',
        'guardian_name', 'guardian_phone', 'date_of_birth',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
