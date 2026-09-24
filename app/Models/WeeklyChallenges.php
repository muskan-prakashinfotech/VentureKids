<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyChallenges extends Model
{
    use HasFactory;

    public function questions()
    {
        return $this->hasMany(QuizQuestions::class, 'content_id', 'id');
    }

    public function questionsByType($type)
    {
        return $this->questions()->where('content_type', $type);
    }
}
