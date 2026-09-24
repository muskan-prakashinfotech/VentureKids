<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AITool extends Model
{
    use HasFactory;

    protected $table = 'ai_tools';

    protected $fillable = ['title', 'url'];

    public function subcategories()
    {
        return $this->hasMany(AIToolSubcategory::class, 'ai_tool_id');
    }

}
