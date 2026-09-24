<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasFactory;
    protected $table = 'class_schedule';
    protected $fillable = ['school_id', 'day', 'start_time', 'end_time', 'grade', 'section', 'number_of_student'];

    public function level()
    {
        return $this->belongsTo(Grade::class, 'grade');
    }
}
