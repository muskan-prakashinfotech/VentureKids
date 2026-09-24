<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewTracking extends Model
{
    use HasFactory;

     protected $fillable = [
        'student_id',
        'item_id',
        'play_count',
        'item_type',
    ];

    public function student()
    {
        return $this->belongsTo(Students::class);
    }

    public function affirmation()
    {
        return $this->belongsTo(PlayAffirmation::class, 'item_id');
    }
}
