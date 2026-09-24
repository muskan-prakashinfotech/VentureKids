<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeedback extends Model
{
    use HasFactory;
    protected $table = "student_feedback";
    protected $fillable = ['id','student_id','year','level','feedback','grade','assessment', 'term'];
}
