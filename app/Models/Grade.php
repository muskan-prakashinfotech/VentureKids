<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;
    protected $table = 'grades';
    protected $fillable = ['id', 'grade', 'description', 'cert_description', 'cert_quote', 'image', 'is_primary', 'level_icon', 'display_order_id', 'assessment_order', 'is_publish'];

    public function streams()
    {
        return $this->hasMany(Stream::class, 'agegroup_id');
    }

    public function getNextDisplayOrderId()
    {
        return ($this->max('display_order_id') + 1);
    }
}
