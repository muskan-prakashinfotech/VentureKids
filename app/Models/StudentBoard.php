<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBoard extends Model
{
    use HasFactory;

    protected $table = 'student_board';

    protected $fillable = [
        'name',
        'is_other',
    ];
}
