<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRagistration extends Model
{
    use HasFactory;
    protected $table = "event_registration";
    protected $fillable = ['id','event_id','student_id','name','email','person','date','position','booking_agree'];

}
