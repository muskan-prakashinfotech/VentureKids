<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainerstream extends Model
{
    use HasFactory;
    protected $table = 'trainerstreams';
    //protected $primarykey = "id";
    protected $fillable = ['id', 'title', 'agegroup_id', 'creator_id', 'creator', 'display_order_id'];

    public function agegroup()
    {
        return $this->belongsTo(Trainerlavel::class, 'agegroup_id');
    }

    public function getNextDisplayOrderId($agegroupId)
    {
        return ($this->where(['agegroup_id' => $agegroupId])->max('display_order_id') + 1);
    }

    public function trainerStreamImages()
    {
        return $this->hasMany(TrainerStreamImages::class, 'stream_id', 'id');
    }
}
