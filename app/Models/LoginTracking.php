<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\UserType;
use App\Models\Students;

class LoginTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'item_id',
        'login_at',
    ];

    protected $casts = [
        'user_type' => UserType::class,
        'login_at' => 'datetime',
    ];
    public function student()
    {
        return $this->belongsTo(Students::class, 'item_id');
    }
}
