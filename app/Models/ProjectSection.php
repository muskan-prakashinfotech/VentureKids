<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSection extends Model
{
    protected $fillable = ['section_title', 'display_order', 'status'];

    public function questions()
    {
        return $this->hasMany(ProjectQuestion::class);
    }

    protected static function booted()
    {
        static::deleting(function (ProjectSection $section) {
            $questionIds = $section->questions()->pluck('id');

            if ($questionIds->isNotEmpty()) {
                StudentProjectAnswer::whereIn('project_question_id', $questionIds)->delete();
                StudentProjectAttachment::whereIn('project_question_id', $questionIds)->delete();
                ProjectQuestion::whereIn('id', $questionIds)->delete();
            }
        });
    }
}
