<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerEducationBackground extends Model
{
    use HasFactory;
    protected $table = "trainer_education_background";
    protected $fillable = ['id','trainer_id','school_name','school_location','degree','pass_year','gread'];
}
