<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerSessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id', 'session_id', 'school_id', 'session_date','image_without_faces', 'image_with_faces','session_summary', 'highlights_feedback', 'learning_outcome', 'skill_focus', 'status'
    ];

    public function photos()
    {
        return $this->hasMany(TrainerSessionReportPhoto::class, 'report_id');
    }

    public function photosByType($type)
    {
        return $this->photos()->where('photo_type', $type)->get();
    }
}
