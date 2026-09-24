<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerSessionReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['report_id', 'photo_type', 'file_path', 'original_name'];
}
