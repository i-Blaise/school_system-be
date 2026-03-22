<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SchoolProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'school_id',
        'logo_url',
        'description',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
