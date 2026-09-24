<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TrainerContentImages;

class Trainercontent extends Model
{
    use HasFactory;
    protected $table = 'trainercontents';
    protected $fillable = ['id', 'title', 'description', 'agegroup_id', 'stream_id', 'video', 'worksheet', 'video_url', 'display_order_id', 'is_publish'];

    public function getstream()
    {
        return $this->belongsTo(Trainerstream::class, 'stream_id', 'id');
    }

    public function getagegroup()
    {
        return $this->belongsTo(Trainerlavel::class, 'agegroup_id');
    }

    public function getNextDisplayOrderId($streamId)
    {
        return ($this->where(['stream_id' => $streamId])->max('display_order_id') + 1);
    }

    public function hasContentImages()
    {
        return (bool) $this->trainerContentImages()->first();
    }

    public function trainerContentImages()
    {
        return $this->hasMany(TrainerContentImages::class, 'trainercontents_id');
    }
}
