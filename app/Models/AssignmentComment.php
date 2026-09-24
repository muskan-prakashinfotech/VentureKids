<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentComment extends Model
{
    use HasFactory;
    protected $table = "assignment_comment";
    protected $fillable = ['id','assignment_id','reciever_id','sender_id','message'];

    public function getTrainer(){
        return $this->belongsTo(Trainer::class,'sender_id','id');
    }

    public function getStudent(){
        return $this->belongsTo(Students::class,'recieveer_id','id');
    }

    public function student(){
        return $this->belongsTo(Students::class,'sender_id','id');
    }

    public function getAssignment(){
        return $this->belongsTo(StudentCommunications::class,'assignment_id','id');
    }
}