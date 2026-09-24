<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\Students;
use App\Models\TrainerAllocation;
use App\Models\ExternalSession;
use App\Models\LoginTracking;
use App\Models\School;
class ClassScheduleController extends Controller
{
    public function Class_schedule()
    {
        return view('student.class_schedule.schedule');
    }

    public function student_classSchedule(Request $request)
    {
        $schoolId = Session::get('student_school_id');
        $studentId = Session::get('student_id');
        
        $student = Students::with('country')->select('grade_id','country_id')->find($studentId);
        $school = School::with('countrynew')->select('country_id')->find($schoolId);

        
       // Determine the time zone. Prioritize student's country, then school's, then default to UTC.
        $timeZone = 'UTC';
        if ($student?->country?->timezone) {
            $timeZone = $student->country->timezone;
        } elseif ($school?->countrynew?->timezone) {
           $timeZone = $school->countrynew->timezone;
        }

        $studentLevelIds = [];
        if (!empty($student?->grade_id)) {
            $studentLevelIds = explode(',', $student->grade_id);
        }

        $from = $request->query('start');
        $to = $request->query('end');
        try {
            $from = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        } catch (\Exception $e) {
            $from = Carbon::now()->subMonth()->startOfDay();
        }
        try {
            $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->addMonths(6)->endOfDay();
        } catch (\Exception $e) {
            $to = Carbon::now()->addMonths(6)->endOfDay();
        }
        
        $sessions = ExternalSession::where('attendee_type', 'schools')
            ->where(function ($query) use ($schoolId) {
                $query->where('attendees', 'all')
                    ->orWhereRaw("FIND_IN_SET(?, attendees)", [$schoolId]);
            })
            ->where(function ($query) use ($studentLevelIds) {
                $query->where('levels', 'all');
                foreach ($studentLevelIds as $levelId) {
                    $query->orWhereRaw("FIND_IN_SET(?, levels)", [$levelId]);
                }
            })
            ->where('is_cancelled', false)
            ->get();

        $occurrences = ExternalSession::occurrencesForSessions($sessions, $from, $to);

        $logins  = LoginTracking::select('login_at')->where('user_type', 1)->where('item_id', $studentId)->get();
        $loginDates = collect($logins)
                    ->groupBy(function ($log) {
                        return \Carbon\Carbon::parse($log->login_at)->format('Y-m-d');
                    })
                    ->map(function ($loginsOfDay) {
                        return \Carbon\Carbon::parse($loginsOfDay->first()->login_at);
                    });
        
        $combinedEvents = collect();

        // Format data for FullCalendar & group each recurring occurrence by its own date
        foreach ($occurrences as $entry) {
            $session = $entry['session'];
            $localTime = $entry['date_time']->copy()->setTimezone($timeZone);
            $date = $localTime->format('Y-m-d');

            $combinedEvents[$date] = [
                'session_title' => $session->title,
                'start' => $localTime->toIso8601String(),
                'time' => $localTime->format('g:i A'),
                'speaker' => $session->speaker,
                'zoom_link' => $session->zoom_link,
                'agenda' => $session->agenda,
                'session_type' => $session->session_type,
                'type' => 'session',
                'login' => false, // default
            ];
        }

        // Add login events or merge with existing ones
        foreach ($loginDates as $date => $loginTime) {
            if ($combinedEvents->has($date)) {
                // Update existing session event to include login
                $existing = $combinedEvents->get($date);
                $existing['login'] = true;
                $existing['type'] = 'both';
                $combinedEvents->put($date, $existing);
            } else {
                // Create login-only event
                $combinedEvents->put($date, [
                    'start' => $loginTime->toIso8601String(), // full datetime
                    'type' => 'login',
                    'login' => true,
                ]);
            }
        }
        
        return $combinedEvents->values()->toJson();  
    }

    public function student_day_class_schedule(Request $req)
    {
        
        $student = Students::where('user_id', Session::get('user_id'))->first();
        $class_schedule = TrainerAllocation::where('school_id', $student->school_id)->where('grade', $student->grade_id)->where('day', $req->day)->with('trainer')->get();
        // print_r($class_schedule); exit();
        foreach ($class_schedule as $sch) {
            // $this->trainerAllocationToCreateMeeting($sch, $req->date); // Student can't create zoom link..
        }
        $allmeetings = Meeting::whereIn("allowcated_id", $class_schedule->pluck('id')->toArray())->get();
        return (string)view('student.class_schedule.classdetails', compact('class_schedule', 'allmeetings'));
    }
}
