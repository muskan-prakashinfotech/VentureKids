<?php

namespace App\Http\Controllers\trainer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventResource;
use App\Models\EventPosterAttachment;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class EventController extends Controller
{
     public function event_list()
    {
        $eventList = Event::where('country_id', Session::get('country_id'))->get();
        $pastEventList = $currentEventList = [];
        if($eventList->count()) {
            $eventList = $eventList->toArray();
            $current_date = Carbon::now();
            foreach($eventList as $event) {
                if(!empty($event['event_description'])) {
                    $event_description = strip_tags(html_entity_decode($event['event_description']));
                    if(strlen($event_description) > 75) {
                        $event_description = substr($event_description, 0, 75).'...';
                    }
                    $event['event_description'] = $event_description;
                }
                $event_image = $event['event_image'];
                if(!empty($event_image)) {
                    $extension = pathinfo($event_image, PATHINFO_EXTENSION);
                    $filename = pathinfo($event_image, PATHINFO_FILENAME);
                    $filename = $filename.'-1920x1080.'.$extension;
                    if (file_exists(public_path('image/event/'.$filename))) {
                        $event['event_image'] = 'image/event/'.$filename;
                    }
                }
                if($event['event_last_date'] < date('Y-m-d')) {
                    $pastEventList[] = $event;
                } else {
                    $currentEventList[] = $event;
                }
            }
        }
        
        return view('trainer.event.event_list')->with(['currentEvents' => $currentEventList, 'pastEvents' => $pastEventList]);
    }

    public function eventView($id)
    {
        $event = Event::with('eventHighlights', 'eventAttachments')->find($id);
        if($event) {
            $event = $event->toArray();
        } else {
            return redirect()->route('trainer.event/list.eventList');
        }
        
        return view('trainer.event.view_event',compact('event'));
    }
}
