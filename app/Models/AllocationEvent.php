<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllocationEvent extends Model
{
    use HasFactory;
    protected $table = "allocation_event";
    protected $fillable = ['school_id','event_name'];
}
