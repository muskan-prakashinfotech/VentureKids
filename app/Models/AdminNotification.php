<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = ['title', 'description', 'grade_id', 'school_id'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
