<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HowItWorks extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_path'
    ];
}
