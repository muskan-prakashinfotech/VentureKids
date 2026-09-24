<?php

namespace App\Http\Controllers\Student;

use App\Helpers\QuizHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\StudentCommunications;
use App\Models\Students;
use App\Models\AssignmentDetails;
use App\Models\EventRagistration;
use App\Models\Event;
use Validator;
use App\Http\Requests\Student\EventChallengeRequest;
use App\Models\EventChallenge;
use App\Models\EventChallengeFiles;
use Illuminate\Support\Str;
use App\Jobs\StudentChallenge;
use Carbon\Carbon;
use App\Models\EventResource;
use App\Models\EventPosterAttachment;
use App\Models\Country;
use App\Models\WeeklyChallenges;
use App\Helpers\StudentRewardPointsHelper;

class EventController extends Controller
{
   // Event List Page
    public function eventList() {
        return view('student.event.event_list');   
    }

    // AJAX Call Daily Quizzes
    public function getDailyChallenges() {
        $student_id = Session::get('student_id');
        $dailyChallengeData = QuizHelper::getDailyChallengeUnlockStatus($student_id);

        return view('student.event.partials.daily_challenges', $dailyChallengeData);
    }
   

    // AJAX Call Industry Challenges
    public function getIndustryChallenges() {
        $student_id = Session::get('student_id');
        $stud_data = Students::select('country_id', 'created_at')->find($student_id);

        $eventList = Event::select(
            'id','event_name','event_date','event_last_date','event_image','visibility_type','is_publish','country_id','event_description')
            ->where('is_publish',1)
            ->where(function ($query) use ($stud_data) {
                $query->where('visibility_type', 1) //global
                      ->orWhere(function ($q) use ($stud_data) {
                          $q->where('visibility_type', 2)   //country-specific
                            ->where('country_id', $stud_data->country_id);
                      });
            })->get();

        
        $currentEventList = [];
        $pastEventList = [];
        $current_date = Carbon::now()->startOfDay();

        foreach ($eventList as $event) {
            if (!empty($event->event_description)) {
                $event_description = strip_tags(html_entity_decode($event->event_description));
                if (strlen($event_description) > 75) {
                    $event_description = substr($event_description, 0, 75) . '...';
                }
                $event->event_description = $event_description;
            }

            if (!empty($event->event_image)) {
                $extension = pathinfo($event->event_image, PATHINFO_EXTENSION);
                $filename = pathinfo($event->event_image, PATHINFO_FILENAME);
                $filename = $filename . '-1920x1080.' . $extension;
                if (file_exists(public_path('image/event/' . $filename))) {
                    $event->event_image = 'image/event/' . $filename;
                }
            }

            // Lock Check
            $event_last_date = Carbon::parse($event->event_last_date)->startOfDay();
            $event->is_locked = $current_date->gt($event_last_date);

            // Categorize
            if ($event->is_locked) {
                $pastEventList[] = $event;
            } else {
                $currentEventList[] = $event;
            }
        }

        return view('student.event.partials.industry_challenges', compact('currentEventList', 'pastEventList'));
    }


    public function eventView($id){
        $event = Event::where('id',$id)->first();
        $student_id = Session::get('student_id');

        $studCountry = Students::select('country_id')->find($student_id);

        if(($event['visibility_type'] == 2 && $event['country_id'] != $studCountry->country_id) || $event['is_publish']==0) {
            return redirect()->route('student.event_list')->with('message', 'You are not allowed to view this event.');
        }

        // Check if event last date has passed
        $current_date = Carbon::now()->startOfDay();
        $event_last_date = Carbon::parse($event->event_last_date)->startOfDay();

        if ($current_date->gt($event_last_date)) {
            return redirect()->route('student.event_list')->with('message', 'This event has ended and is no longer accessible.');
        }

        $studentAllowToRespondChallenge = true;
        $challengeRespondData = EventChallenge::with('challengesFiles')->where(['event_id' => $id, 'student_id' => $student_id])->get();
        if ($challengeRespondData->count()) {
            $studentAllowToRespondChallenge = false;
        }

        $resourceData = EventResource::where('event_id', $id)->get();
        if($resourceData->count()) {
            $resourceData = $resourceData->toArray();
        } else {
            $resourceData = [];
        }

        $posterData = EventPosterAttachment::where('event_id', $id)->get();
        if($posterData->count()) {
            $posterData = $posterData->toArray();
        } else {
            $posterData = [];
        }
        
        return view('student.event.event_view')->with(['event' => $event, 'studentAllowToRespondChallenge' => $studentAllowToRespondChallenge, 'resourceData' => $resourceData, 'posterData' => $posterData, 'challengeRespondData' => $challengeRespondData]);
    }

    public function eventBookingRegistration(Request $request){

        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required',
            'person' => 'required',
            'date' => 'required',
            'position' => 'required',
            'booking_agree' => 'required',
        ]);

        if ($validator->fails())
        {
     
          return response()->json(['error'=>$validator->errors()]);
        }
        $event_id = $request->event_id;
        $student_id = Session::get('user_id');
        $name = $request->full_name;
        $email = $request->email;
        $person = $request->person;
        $date = $request->date;
        $position = $request->position;
        $booking_agree = $request->booking_agree;

        $check_reg = EventRagistration::where('event_id',$event_id)->where('student_id',$student_id)->first();
        if(empty($check_reg)){

        $event = new EventRagistration();
        $event->event_id = $event_id;
        $event->student_id = $student_id;
        $event->name = $name;
        $event->email = $email;
        $event->person = $person;
        $event->date = $date;
        $event->position = $position;
        $event->booking_agree = $booking_agree;
        $event->save();
        echo 1;
        }else{
        echo 2;
        }


       
        
    }

    public function eventChallengeResponse(EventChallengeRequest $request)
    {
        $student_id = Session::get('student_id');
        if ($student_id) {
            $eventChallange = new EventChallenge();
            $eventChallange->event_id = $request->event_id;
            $eventChallange->student_id = $student_id;
            $eventChallange->description = $request->description;

            if ($eventChallange->save()) {

                /* START - Store Industry Challenger Reward Points */
                StudentRewardPointsHelper::storeRewardPoints([
                    'student_id' => $student_id,
                    'reward_type' => 'challenge_respond',
                    'item_id' => $request->event_id,
                    'reward_points' => 1,
                ]);
                /* END - Store Industry Challenger Reward Points */

                $multiChallengeAttachments = $request->attachment;
                if ($multiChallengeAttachments) {
                    $eventChallengeUploadPath = 'image/student/event_challenge/';
                    for ($i = 0; $i < count($multiChallengeAttachments); $i++) {
                        $attachmentName = Str::random(10);//unique name generate every time
                        $ext = strtolower($multiChallengeAttachments[$i]->getClientOriginalExtension());
                        
                        if (tenant() && tenant()->tenant_id) {
                            $multiChallengeAttachments[$i]->storeAs(tenant()->tenant_id.'/student/event_challenge' , $attachmentName.'.'.$ext, 'tenant_uploads');
                            $eventChallengeUploadPath = tenant()->tenant_id.'/student/event_challenge/';
                        } else {
                            $success = $multiChallengeAttachments[$i]->move($eventChallengeUploadPath, $attachmentName.'.'.$ext);
                        }

                        $eventChallengeFile = new EventChallengeFiles();
                        $eventChallengeFile->event_challenge_id = $eventChallange->id;
                        $eventChallengeFile->attachment = $eventChallengeUploadPath.''.$attachmentName.'.'.$ext;
                        $eventChallengeFile->save();
                    }
                }
                // $studentDetail = ['studentId' => $student_id, 'studentEmail' => Session::get('email')];
                // dispatch(new StudentChallenge($studentDetail));
                return redirect()->back()->with(['message' => 'You have successfully challenge to the event!', 'confetti_visible' => 1]);
            } else {
                return redirect()->back()->with('message', 'Unable to save your challange.');
            }
        } else {
            return redirect()->back()->with('message', 'Session timeout.');
        }
    }
}