<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'trainer_id',
        'student_id',
        'knowledge_score',
        'knowledge_feedback',
        'skills_score',
        'skills_feedback',
        'curious_score',
        'resilient_score',
        'open_score',
        'positive_score',
        'creative_score',
        'empathetic_score',
        'observant_score',
        'abundance_score',
        'growth_score',
        'entrepreneurial_score',
        'mindset_feedback',
        'spider_graph_data',
        'is_publish',
        'teacher_notes',
        'mindset_score'
    ];

    protected $casts = [
        'spider_graph_data' => 'array',
        'knowledge_score' => 'integer',
        'skills_score' => 'integer',
        'mindset_score' => 'integer',
    ];

     public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function student()
    {
        return $this->belongsTo(Students::class);
    }

     public function getMindsetScores()
    {
        return [
            'Curious' => $this->curious_score,
            'Resilient' => $this->resilient_score,
            'Open' => $this->open_score,
            'Positive' => $this->positive_score,
            'Creative' => $this->creative_score,
            'Empathetic' => $this->empathetic_score,
            'Observant' => $this->observant_score,
            'Abundance' => $this->abundance_score,
            'Growth' => $this->growth_score,
            'Entrepreneurial' => $this->entrepreneurial_score,
        ];
    }

    // Calculate average mindset score
    public function getAverageMindsetScore()
    {
        $scores = array_filter($this->getMindsetScores());
        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;
    }


}
