<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EventChallenge;

class Event extends Model
{
    use HasFactory;
    protected $table = 'events';
    protected $fillable = ['country_id', 'event_name', 'event_image', 'event_date', 'event_last_date', 'event_fee', 'event_poster', 'event_description', 'currency'];

    public function studentChallenges(): HasMany
    {
        return $this->hasMany(EventChallenge::class);
    }

    public function eventHighlights()
    {
        return $this->hasMany(EventResource::class, 'event_id', 'id');
    }

    public function eventAttachments()
    {
        return $this->hasMany(EventPosterAttachment::class, 'event_id', 'id');
    }
}
