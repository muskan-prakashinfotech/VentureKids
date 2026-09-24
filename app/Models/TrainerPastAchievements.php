<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerPastAchievements extends Model
{
    use HasFactory;
    protected $table = "trainer_past_achievements";
    protected $fillable = ['id','trainer_id','job_title','employer','city_municipality','country','start_date','end_date','status'];
}
