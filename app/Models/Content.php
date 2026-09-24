<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    protected $table = 'contents';
    protected $fillable = ['id', 'title', 'description', 'agegroup_id', 'stream_id', 'video', 'worksheet', 'video_url'];

    public function getstream()
    {
        return $this->belongsTo(Stream::class, 'stream_id', 'id');
    }

    public function getagegroup()
    {
        return $this->belongsTo(Grade::class, 'agegroup_id');
    }
}
