<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceProductImage extends Model
{
    protected $fillable = ['marketplace_product_id', 'image_path'];
    protected $appends = ['url'];

    public function product()
    {
        return $this->belongsTo(MarketplaceProduct::class, 'marketplace_product_id');
    }

    public function getUrlAttribute()
    {
        return asset($this->image_path);
    }
}
