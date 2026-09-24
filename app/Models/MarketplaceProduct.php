<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceProduct extends Model
{
    protected $fillable = [
        'student_id', 'name', 'currency_id', 'story', 'description', 'special_feature', 'price', 'status',
    ];

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id');
    }

    public function images()
    {
        return $this->hasMany(MarketplaceProductImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(MarketplaceProductImage::class)->oldestOfMany();
    }
}
