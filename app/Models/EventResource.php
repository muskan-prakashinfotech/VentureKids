<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventResource extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'event_id', 'title', 'description', 'attachment', 'created_at', 'updated_at'];
}
