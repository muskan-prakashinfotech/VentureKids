<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    //  Optional: Automatically cast common setting types
    public function getValueAttribute($value)
    {
        $json = json_decode($value, true);
        return $json ?? $value;
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }

    // Helper method
    public static function getValue($key, $default = null)
    {
        return self::where('key', $key)->first()->value ?? $default;
    }
}
