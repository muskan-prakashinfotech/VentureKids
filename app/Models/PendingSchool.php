<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingSchool extends Model
{
    protected $fillable = [
        'submitted_by',
        'form_data',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'form_data' => 'array',
        'reviewed_at' => 'datetime',
    ];
}
