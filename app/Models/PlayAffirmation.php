<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayAffirmation extends Model
{
    use HasFactory;

    protected $fillable = ['file_path'];

    public function views()
    {
        return $this->hasMany(ViewTracking::class, 'affirmation_id');
    }
}
