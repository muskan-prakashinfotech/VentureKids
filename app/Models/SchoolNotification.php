<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolNotification extends Model
{
    protected $fillable = ['school_id', 'title', 'description', 'admin'];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
