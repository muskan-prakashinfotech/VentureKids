<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contentview extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'content_id', 'trainer_id'];
}
