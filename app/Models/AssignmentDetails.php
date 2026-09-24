<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentDetails extends Model
{
    use HasFactory;
    protected $table = "assignment_details";
    protected $fillable = ['id','student_id','assignment_id','read_status','comment_status'];

    public function assignment()
    {
        return $this->belongsTo(StudentCommunications::class, 'assignment_id');
    }
}