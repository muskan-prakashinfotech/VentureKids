<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Studentscontent extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'title', 'description', 'agegroup_id', 'stream_id', 'video', 'video_url', 'learning_object', 'outcome_session', 'question_access_knowledge', 'introduce_topic_student', 'related_activity_one', 'related_activity_two', 'vocabulary', 'tips_of_parents', 'display_order_id', 'worksheet', 'worksheet_name', 'is_publish'];

    public function getNextDisplayOrderId($streamId)
    {
        return ($this->where(['stream_id' => $streamId])->max('display_order_id') + 1);
    }
}
