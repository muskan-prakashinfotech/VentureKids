<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempts extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'student_id', 'quiz_id', 'created_by', 'updated_by', 'created_at', 'updated_at'];
}
