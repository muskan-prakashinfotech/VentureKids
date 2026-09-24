<?php

namespace App\Http\Controllers\trainer;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\TrainerAllocation;
use App\Models\Trainer;
use App\Models\ExternalSession;
use App\Models\TrainerAllocationNew;
use Carbon\Carbon;
use App\Models\TrainerSessionReport;
use App\Models\TrainerSessionReportPhoto;
use Illuminate\Support\Facades\Storage;
use App\Models\Students;
use App\Models\Observation;
use App\Models\StudentObservations;
use App\Helpers\StudentObservationHelper;

class ClassScheduleController extends Controller
{
    public function Class_schedule()
    {
        return view('trainer.class_schedule.schedule');
    }

    public function trainer_classSchedule(Request $request)
    {
        $trainer_id = Session::get('trainer_id');

        $trainer = Trainer::with('country')->select('country_id')->find($trainer_id);
        $timeZone = $trainer?->country?->timezone ?? 'UTC';
          
        $trainerAllocations = TrainerAllocationNew::with('getSchool.domains')->where('trainer_id', $trainer_id)->get();

        $schoolMap = $trainerAllocations->mapWithKeys(function ($alloc) {
            return [$alloc->school_id => $alloc->getSchool?->school_name ?? 'Unknown'];
        });

        $domainMap = $trainerAllocations->mapWithKeys(function ($alloc) {
            return [$alloc->school_id => $alloc->getSchool?->domains?->domain ?? null];
        });

       
        $trainerSchoolIds = $trainerAllocations->pluck('school_id')->toArray();

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

        $sessions = ExternalSession::where(function ($query) use ($trainerSchoolIds, $trainer_id) {
                $query->where(function ($query) use ($trainerSchoolIds) {
                    $query->where('attendee_type', 'schools')
                        ->where(function ($query) use ($trainerSchoolIds) {
                            $query->where('attendees', 'all');
                            foreach ($trainerSchoolIds as $schoolId) {
                                $query->orWhereRaw("FIND_IN_SET(?, attendees)", [$schoolId]);
                            }
                        });
                })
                ->orWhere(function ($query) use ($trainer_id) {
                    $query->where('attendee_type', 'trainer')
                        ->where(function ($query) use ($trainer_id) {
                            $query->where('trainer_ids', 'all')
                                ->orWhereRaw("FIND_IN_SET(?, trainer_ids)", [$trainer_id]);
                        });
                });
            })
            ->where('is_cancelled', false)
            ->get();

        // Format data for FullCalendar
        $events = ExternalSession::occurrencesForSessions($sessions, $from, $to)
            ->flatMap(function ($entry) use ($timeZone, $schoolMap, $domainMap, $trainerSchoolIds) {
                $session = $entry['session'];
                $localTime = $entry['date_time']->copy()->setTimezone($timeZone);
                $endTime   = $localTime->copy()->addMinutes($session->duration ?? 60);

                $attendeeIds = $session->attendees === 'all' ? $trainerSchoolIds : array_map('intval', explode(',', $session->attendees));
                $matchedIds  = array_values(array_intersect($attendeeIds, $trainerSchoolIds));

                return array_map(function ($id) use ($schoolMap, $domainMap, $session, $localTime, $endTime) {
                    $full  = $schoolMap->get($id, 'School');
                    $short = $domainMap->get($id) ?? preg_split('/[\s\-–—]+/', trim($full))[0];

                    return [
                        'session_id'      => $session->id,
                        'session_title'   => $session->title,
                        'start'           => $localTime->toIso8601String(),
                        'end'             => $endTime->toIso8601String(),
                        'start_time'      => $localTime->format('g:i a'),
                        'end_time'        => $endTime->format('g:i a'),
                        'occurrence_date' => $localTime->format('Y-m-d'),
                        'school'          => ['id' => $id, 'name' => $full, 'short' => $short],
                        'zoom_link'       => $session->zoom_link,
                        'session_type'    => $session->session_type,
                    ];
                }, $matchedIds);
            })
            ->values();

        return $events->toJson();
    }

    public function trainer_day_class_schedule(Request $req)
    {

        // $events=[];
        // if($trainer_schedule)
        // {
        //     foreach($trainer_schedule as  $trainer_schedules)
        //     {
        //       //$events[]='<b>School Name:</b> '.$trainer_schedules['get_school']['school_name'].', <b>Class Schedule:</b> '.$trainer_schedules['class_schedule'].', <b>Join Url:</b> '.$trainer_schedules['get_meting_url'][0]['join_url'].', <b>Password</b> :'.$trainer_schedules['get_meting_url'][0]['password'].'<br><br>';
        //       $events[]='<b>School Name:</b> '.$trainer_schedules['get_school']['school_name'].', <b>Class Schedule:</b> '.$trainer_schedules['class_schedule'].', <b>Join Url:</b> , <b>Password</b>: <br><br>';
        //     }
        //     echo json_encode($events);
        // }

        // else
        // {
        //     echo json_encode($events);
        // }

        $trainer = Trainer::where('user_id', Session::get('user_id'))->first();
        $trainer_schedule = TrainerAllocation::with('getSchool')->where('trainer_id', $trainer->id)->where('day', $req->day)->get();
        // dd($trainer_schedule);
        foreach ($trainer_schedule as $sch) {
            $this->trainerAllocationToCreateMeeting($sch, $req->date);
        }
        $allmeetings = Meeting::whereIn("allowcated_id", $trainer_schedule->pluck('id')->toArray())->get();
        return (string)view('trainer.schedule.classdetails', compact('trainer_schedule', 'allmeetings'));
    }

    public function getSessionReport($sessionId, $schoolId, $sessionDate)
    {
        $trainer_id = Session::get('trainer_id');
        $report = TrainerSessionReport::with('photos')->where([
                'trainer_id' => $trainer_id,
                'session_id' => $sessionId,
                'school_id'  => $schoolId,
            ])
            ->where(function ($q) use ($sessionDate) {
                $q->where('session_date', $sessionDate)->orWhereNull('session_date');
            })
            ->orderByRaw('session_date IS NULL')
            ->first();

        if (!$report) return response()->json(null);

        return response()->json([
            'id'                  => $report->id,
            'session_summary'     => $report->session_summary,
            'highlights_feedback' => $report->highlights_feedback,
            'learning_outcome'    => $report->learning_outcome,
            'skill_focus'         => $report->skill_focus,
            'status'              => $report->status,
            'photos_without_faces' => $report->photos->where('photo_type', 'without_faces')->map(fn($p) => [
                'id'            => $p->id,
                'original_name' => $p->original_name,
            ])->values(),
            'photos_with_faces' => $report->photos->where('photo_type', 'with_faces')->map(fn($p) => [
                'id'            => $p->id,
                'original_name' => $p->original_name,
            ])->values(),
      ]);
    }

    public function storeSessionReport(Request $request)
    {
        $trainer_id = Session::get('trainer_id');
        $existingReport = TrainerSessionReport::where([
            'trainer_id'   => $trainer_id,
            'session_id'   => $request->session_id,
            'school_id'    => $request->school_id,
            'session_date' => $request->session_date,
        ])->first();

        $hasExistingWithout = $existingReport ? $existingReport->photos()->where('photo_type', 'without_faces')->count() > 0 : false;

        $hasExistingWith = $existingReport ? $existingReport->photos()->where('photo_type', 'with_faces')->count() > 0 : false;

        $request->validate([
            'session_id'             => 'required|integer',
            'school_id'              => 'required|integer',
            'session_date'           => 'required|date',
            'session_summary'        => 'required|string|max:500',
            'highlights_feedback'    => 'nullable|string|max:500',
            'learning_outcome'       => 'nullable|string|max:500',
            'skill_focus'            => 'nullable|string|max:500',
            'status'                 => 'required|in:0,1',
            'photos_without_faces'   => ($hasExistingWithout ? 'nullable' : 'required') . '|array|max:5',
            'photos_without_faces.*' => 'image|mimes:png,jpg,jpeg|max:10240',
            'photos_with_faces'      => ($hasExistingWith    ? 'nullable' : 'required') . '|array|max:5',
            'photos_with_faces.*'    => 'image|mimes:png,jpg,jpeg|max:10240',
            'deleted_photo_ids'      => 'nullable|string',
        ]);

        $report = TrainerSessionReport::updateOrCreate(
            [
                'trainer_id'   => $trainer_id,
                'session_id'   => $request->session_id,
                'school_id'    => $request->school_id,
                'session_date' => $request->session_date,
            ],
            [
                'session_summary'     => $request->session_summary,
                'highlights_feedback' => $request->highlights_feedback,
                'learning_outcome'    => $request->learning_outcome,
                'skill_focus'         => $request->skill_focus,
                'status'              => $request->status,
            ]
        );

        // Delete removed photos
        if ($request->deleted_photo_ids) {
            $ids    = explode(',', $request->deleted_photo_ids);
            $photos = TrainerSessionReportPhoto::whereIn('id', $ids)->where('report_id', $report->id)->get();
            foreach ($photos as $photo) {
                Storage::disk('public')->delete($photo->file_path);
              $photo->delete();
            }
        }

        if ($request->hasFile('photos_without_faces')) {
            $existing = $report->photos()->where('photo_type', 'without_faces')->count();
            $slots    = 5 - $existing;
            foreach (array_slice($request->file('photos_without_faces'), 0, $slots) as $file) {
                $path = $file->store('session_reports', 'public');
                TrainerSessionReportPhoto::create([
                    'report_id'     => $report->id,
                    'photo_type'    => 'without_faces',
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }
    
        if ($request->hasFile('photos_with_faces')) {
            $existing = $report->photos()->where('photo_type', 'with_faces')->count();
            $slots    = 5 - $existing;
            foreach (array_slice($request->file('photos_with_faces'), 0, $slots) as $file) {
                $path = $file->store('session_reports', 'public');
                TrainerSessionReportPhoto::create([
                    'report_id'     => $report->id,
                    'photo_type'    => 'with_faces',
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }
    
        return response()->json(['success' => true, 'status' => $report->status]);
    }

    public function deleteReportPhoto($photoId)
    {
        $trainer_id = Session::get('trainer_id');
        $photo = TrainerSessionReportPhoto::whereHas('report', function($q) use ($trainer_id) {
            $q->where('trainer_id', $trainer_id)->where('status', 0);
        })->findOrFail($photoId);

        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    public function getSessionStudents($sessionId, $schoolId, $sessionDate)
    {
        $session = ExternalSession::findOrFail($sessionId);
        $school  = \App\Models\School::find($schoolId);

        $studentsQuery = Students::where('school_id', $schoolId);
        $levels  = trim($session->levels  ?? '');
        $batches = trim($session->batches ?? '');

        if ($levels != 'all' && !empty($levels)) {
            $levelIds = array_map('intval', explode(',', $levels));
            $studentsQuery->where(function ($q) use ($levelIds) {
                foreach ($levelIds as $id) {
                    $q->orWhereRaw("FIND_IN_SET(?, grade_id)", [$id]);
                }
            });
        }

        if ($batches != 'all' && !empty($batches)) {
            $batchIds = array_map('intval', explode(',', $batches));
            $studentsQuery->where(function ($q) use ($batchIds) {
                foreach ($batchIds as $id) {
                    $q->orWhereRaw("FIND_IN_SET(?, school_batch_id)", [$id]);
                }
            });
        }

        $students = $studentsQuery->select('id', 'name', 'image', 'school_batch_id')->orderBy('name')->get();

        $batchIds   = $students->pluck('school_batch_id')->filter()->unique()->toArray();
        $batchNames = \DB::table('school_batch')->whereIn('id', $batchIds)->pluck('batch_name', 'id');

        $trainerId = Session::get('trainer_id');
        $observedStudentIds = StudentObservations::where([
                'external_session_id' => $sessionId,
                'school_id'           => $schoolId,
                'trainer_id'          => $trainerId,
            ])
            ->where(function ($q) use ($sessionDate) {
                $q->where('session_date', $sessionDate)->orWhereNull('session_date');
            })
            ->whereIn('student_id', $students->pluck('id'))
            ->pluck('student_id')
            ->unique()
            ->flip();

        return response()->json($students->map(function ($student) use ($school, $batchNames, $observedStudentIds) {
            $image = trim($student->image ?? '');
            $imagePath = !empty($image) ? asset('tenants/' . $image) : null;

            return [
                'id'              => $student->id,
                'first_name'      => explode(' ', trim($student->name))[0],
                'image_url'       => $imagePath,
                'batch_name'      => $batchNames[$student->school_batch_id] ?? null,
                'has_observation' => $observedStudentIds->has($student->id),
            ];
        }));
    }

    public function getObservations()
    {
        $observations = Observation::all()->map(function ($obs) {
            return [
                'id'       => $obs->id,
                'name'     => $obs->name,
                'category' => $obs->category,
                'icon_url' => asset('observations/' . $obs->icon),
            ];
        });
        return response()->json($observations);
    }

    public function storeStudentObservation(Request $request)
    {
        $trainer_id = Session::get('trainer_id');

        $request->validate([
            'student_id'          => 'required|integer',
            'school_id'           => 'required|integer',
            'external_session_id' => 'required|integer',
            'session_date'        => 'required|date',
            'obs_count'           => 'required|integer|min:1|max:6',
        ]);

        $obsCount = (int) $request->input('obs_count');

        $rules = [];
        for ($i = 0; $i < $obsCount; $i++) {
            $rules["obs_{$i}_id"]    = 'required|integer';
            $rules["obs_{$i}_note"]  = 'required|string|max:300';
            $rules["obs_{$i}_photo"] = 'nullable|image|mimes:png,jpg,jpeg|max:10240';
        }
        $request->validate($rules);

        $student = Students::withoutGlobalScopes()
            ->select('id', 'grade_id')
            ->findOrFail($request->student_id);

        $studentGradeIds = array_values(array_filter(
            array_map('intval', explode(',', trim($student->grade_id ?? '')))
        ));

        $extSession = ExternalSession::find($request->external_session_id);
        $gradeId    = '';

        if ($extSession) {
            $sessionLevels = trim($extSession->levels ?? '');
            if ($sessionLevels === 'all' || empty($sessionLevels)) {
                $gradeId = implode(',', $studentGradeIds);
            } else {
                $sessionLevelIds = array_values(array_filter(
                    array_map('intval', explode(',', $sessionLevels))
                ));
                $matching = array_values(array_intersect($studentGradeIds, $sessionLevelIds));
                $gradeId  = implode(',', $matching ?: $studentGradeIds);
            }
        } else {
            $gradeId = implode(',', $studentGradeIds);
        }

        for ($i = 0; $i < $obsCount; $i++) {
            $imagePath = null;
            if ($request->hasFile("obs_{$i}_photo")) {
                $imagePath = $request->file("obs_{$i}_photo")->store('student_observations', 'public');
            }

            StudentObservations::create([
                'student_id'          => $request->student_id,
                'trainer_id'          => $trainer_id,
                'school_id'           => $request->school_id,
                'grade_id'            => $gradeId,
                'observation_id'      => $request->input("obs_{$i}_id"),
                'external_session_id' => $request->external_session_id,
                'session_date'        => $request->session_date,
                'short_note'          => $request->input("obs_{$i}_note"),
                'image'               => $imagePath,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function getStudentObservations($sessionId, $schoolId, $studentId, $sessionDate)
    {
        return StudentObservationHelper::getBySession(
            (int) $sessionId,
            (int) $schoolId,
            (int) $studentId,
            (int) Session::get('trainer_id'),
            $sessionDate
        );
    }

    public function rephraseAnecdote(Request $request)
    {
        $request->validate([
            'text'             => 'required|string|max:300',
            'observation_name' => 'required|string|max:100',
        ]);

        $service   = app(\App\Services\OpenAIService::class);
        $rephrased = $service->rephraseAnecdote($request->text, $request->observation_name);

        if (!$rephrased) {
            return response()->json(['error' => 'Could not rephrase. Please try again.'], 500);
        }

        return response()->json(['rephrased' => $rephrased]);
    }

}
