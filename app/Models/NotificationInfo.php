<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationInfo extends Model
{
    use HasFactory;
    protected $table = 'notification_info';
    
    public function grade(){
        return $this->belongsTo(Grade::class, 'grade_id', 'id');
    }

    public function school(){
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
}
