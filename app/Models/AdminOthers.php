<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminOthers extends Model
{
    use HasFactory;
    protected $table = "admin_others";
    protected $fillable = ['id','setting_name','setting_value'];

}
