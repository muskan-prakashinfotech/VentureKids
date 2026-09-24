<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerAllocationNew extends Model
{
    use HasFactory;
    protected $table = 'trainer_allocation_new';

    public function getSchool()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }

    public function getTrainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id', 'id');
    }

    public function getBatch()
    {
        return $this->belongsTo(SchoolBatch::class, 'school_batch_id', 'id');
    }
}
