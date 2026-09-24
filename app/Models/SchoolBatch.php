<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\SchoolBatchScope;

class SchoolBatch extends Model
{
    use HasFactory;
    protected $table = 'school_batch';

    protected static function booted()
    {
        /**
         * STUDENT SCOPE TO THE SCHOOL
         */
        static::addGlobalScope(new SchoolBatchScope);
    }
}
