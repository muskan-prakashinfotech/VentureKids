<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestions extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'content_id', 'content_type', 'question', 'option1', 'option2', 'option3', 'option4', 'correct_option', 'isDisabled', 'created_by', 'updated_by', 'created_at', 'updated_at'];
}
