<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;

class School extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function awards()
    {
        return $this->hasMany(\App\Models\SchoolAward::class);
    }
}
