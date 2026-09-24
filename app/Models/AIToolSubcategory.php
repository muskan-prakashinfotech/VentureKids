<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIToolSubcategory extends Model
{
    use HasFactory;

    protected $table='ai_tool_subcategories';

      protected $fillable = [
        'ai_tool_id',
        'name',
        'image',
        'description',
        'display_order',
        'status',
    ];

     public function tool()
    {
        return $this->belongsTo(AITool::class, 'ai_tool_id');
    }

     public function getNextDisplayOrderId()
    {
        return ($this->max('display_order') + 1);
    }
}
