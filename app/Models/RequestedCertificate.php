<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestedCertificate extends Model
{
    protected $fillable = ['trainer_id', 'file'];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }
}
