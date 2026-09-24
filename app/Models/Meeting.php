<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = ['zoom_id', 'school_id', 'trainer_id', "meeting_time", "join_url", "password", "allowcated_id"];

    protected $casts = ["meeting_time" => "datetime:Y-m-d H:i:s"];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

}
