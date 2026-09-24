<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainerlavel extends Model
{
    use HasFactory;
    protected $table = 'trainerlavels';
    protected $fillable = ['id', 'grade', 'image', 'display_order_id'];

    public function streams()
    {
        return $this->hasMany(Trainerstream::class, 'agegroup_id');
    }
    
    public function getNextDisplayOrderId()
    {
        return ($this->max('display_order_id') + 1);
    }
}
