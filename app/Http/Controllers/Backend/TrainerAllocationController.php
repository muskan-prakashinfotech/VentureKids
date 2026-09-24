<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AllocationEvent;
use App\Models\ClassSchedule;
use App\Models\Grade;
use App\Models\School;
use App\Models\SchoolBatch;
use App\Models\SchoolNotification;
use App\Models\StudentNotification;
use App\Models\Students;
use App\Models\Trainer;
use App\Models\TrainerAllocation;
use App\Models\TrainerAllocationNew;
use App\Models\TrainerNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class TrainerAllocationController extends Controller
{
    public function trainerallocation()
    {
        $all_school = School::whereHas('user', function($query) {
            $query->where('suspend',2);
        });

        if (isPartnerUser()) {
            applyCountryScope($all_school, 'country_id');
        }

        $all_school = $all_school->get();

        return view('backend.trainer_allocation.trainer_allocation', compact('all_school'));
    }

    public function trainer_allocation_list_datatable(Request $request)
    {
        $allocationQuery = TrainerAllocationNew::with([
            'getSchool' => function($query) {
                $query->select('id', 'school_name', 'country_id');
            },
            'getTrainer' => function($query) {
                $query->select('id', 'trainer_name', 'country_id');
            },
            'getBatch' => function($query) {
                $query->select('id', 'batch_name');
            },
        ]);

        if (isPartnerUser()) {
            $allocationQuery->whereHas('getSchool', function ($query) {
                applyCountryScope($query, 'country_id');
            })->whereHas('getTrainer', function ($query) {
                applyCountryScope($query, 'country_id');
            });
        }

        $allocation_data = $allocationQuery->get()->toArray();
        if (request()->ajax()) {
            return datatables()->of($allocation_data)->make(true);
        }

        return null;
    }

    public function allocateTrainer(Request $request) {
        $trainer_list = Trainer::select(['id', 'trainer_name', 'country_id'])->whereHas('user', function($query) {
            $query->where('suspend',2);
        });

        if (isPartnerUser()) {
            $selectedCountryId = partnerCountryId();
            if (empty($selectedCountryId)) {
                return redirect()->route('backend.trainerallocation.trainerallocation')->with('error', 'No country has been assigned to your account.');
            }

            applyCountryScope($trainer_list, 'country_id');
        }

        $trainer_list = $trainer_list->get();
        
        return view('backend.trainer_allocation.allocate_trainer', compact('trainer_list'));
    }

    public function getSchoolsByTrainer(Request $request)
    {
        $trainerId = (int) $request->trainer_id;
        if (!$trainerId) {
            return response()->json(['status' => 'fail', 'data' => []]);
        }

        $trainer = Trainer::findOrFail($trainerId);
        $this->ensurePartnerTrainerAccess($trainer->id);

        $schoolQuery = School::select(['id', 'school_name'])
            ->whereHas('user', function ($query) {
                $query->where('suspend', 2);
            });

        if (isPartnerUser()) {
            applyCountryScope($schoolQuery, 'country_id');
        } else {
            $schoolQuery->where('country_id', $trainer->country_id);
        }

        return response()->json([
            'status' => 'success',
            'data' => $schoolQuery->orderBy('school_name')->get(),
        ]);
    }

    public function storeAllocateTrainer(Request $request) {
        $request->validate([
            'trainer' => 'required',
            'school' => 'required',
            'school_batch' => 'required|array|min:1',
        ]);

        $school = School::findOrFail($request->school);
        $trainer = Trainer::findOrFail($request->trainer);
        $this->ensurePartnerAllocationAccess($school->id, $trainer->id);

        $alreadyAllocated = [];
        $allowedBatchIds = SchoolBatch::where('school_id', $school->id)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requestedBatchIds = array_values(array_unique(array_map('intval', (array) $request->school_batch)));

        foreach ($requestedBatchIds as $batchId) {
            if (!in_array($batchId, $allowedBatchIds, true)) {
                return redirect()->back()->with('error', 'Selected batch does not belong to the chosen school.')->withInput();
            }
        }

        foreach ($requestedBatchIds as $batchId) {
            $check_already_allocated = TrainerAllocationNew::select('id')->where([
                'school_id' => $school->id,
                'trainer_id' => $trainer->id,
                'school_batch_id' =>$batchId])->count();

            if($check_already_allocated) {
                $batchName = SchoolBatch::find($batchId)?->batch_name ?? "Batch ID $batchId";
                $alreadyAllocated[] = $batchName;
                continue; 
            }
            
            $trainer_allocation = new TrainerAllocationNew();
            $trainer_allocation->trainer_id = $trainer->id;
            $trainer_allocation->school_id = $school->id;
            $trainer_allocation->school_batch_id = $batchId;
            $trainer_allocation->save();
        }

        if (!empty($alreadyAllocated)) {
            $names = implode(', ', $alreadyAllocated);
            return redirect()->route('backend.trainerallocation.trainerallocation')->with('error', "Trainer already allocated to: {$names}. Other batches were saved successfully.");
        }

        return redirect()->route('backend.trainerallocation.trainerallocation')->with('success', 'Trainer Allocated Successfully!');
    }

    public function editAllocateTrainer(Request $request) {
        $trainer_allocation_data = TrainerAllocationNew::find($request->id);
        $this->ensurePartnerAllocationAccess($trainer_allocation_data?->school_id, $trainer_allocation_data?->trainer_id);

        $trainer_list = Trainer::select(['id', 'trainer_name'])->whereHas('user', function($query) {
            $query->where('suspend',2);
        });

        $school_list = School::select(['id', 'school_name'])->whereHas('user', function($query) {
            $query->where('suspend',2);
        });

        if (isPartnerUser()) {
            applyCountryScope($trainer_list, 'country_id');
            applyCountryScope($school_list, 'country_id');
        }

        $trainer_list = $trainer_list->get();
        $school_list = $school_list->get();
        $school_batch_list = SchoolBatch::where('school_id', $trainer_allocation_data->school_id)->get();
        return view('backend.trainer_allocation.edit_allocate_trainer', compact('trainer_list', 'school_list', 'school_batch_list', 'trainer_allocation_data'));
    }

    public function updateAllocateTrainer(Request $request) {
        $request->validate([
            'trainer' => 'required',
            'school' => 'required',
            'school_batch' => 'required',
        ]);
        $school = School::findOrFail($request->school);
        $trainer = Trainer::findOrFail($request->trainer);
        $this->ensurePartnerAllocationAccess($school->id, $trainer->id);

        $trainer_allocation_id = $request->trainer_allocation_id;
        $trainer_allocation = TrainerAllocationNew::find($trainer_allocation_id);
        if($trainer_allocation) {
            $this->ensurePartnerAllocationAccess($trainer_allocation->school_id, $trainer_allocation->trainer_id);

            $allowedBatchIds = SchoolBatch::where('school_id', $school->id)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (!in_array((int) $request->school_batch, $allowedBatchIds, true)) {
                return redirect()->route('backend.trainer_allocation.edit',$trainer_allocation_id)->with('error', 'Selected batch does not belong to the chosen school.');
            }

            $check_already_allocated = TrainerAllocationNew::select('id')->where([
                'school_id' => $school->id,
                'trainer_id' => $trainer->id,
                'school_batch_id' => $request->school_batch])->where('id', '!=', $trainer_allocation_id)->count();
            if($check_already_allocated) {
                return redirect()->route('backend.trainer_allocation.edit',$trainer_allocation_id)->with('error', 'Trainer already allocated to this batch!');
            }
            $trainer_allocation->trainer_id = $trainer->id;
            $trainer_allocation->school_id = $school->id;
            $trainer_allocation->school_batch_id = $request->school_batch;
            $trainer_allocation->save();
            return redirect()->route('backend.trainerallocation.trainerallocation')->with('success', 'Trainer Allocated Successfully!');
        }
        return redirect()->route('backend.trainerallocation.trainerallocation')->with('error', 'Error occurred. Please try again!');
    }

    public function deleteTrainerAllocation($trainer_allocation_id) { 
        $allocation = TrainerAllocationNew::findOrFail($trainer_allocation_id);
        $this->ensurePartnerAllocationAccess($allocation->school_id, $allocation->trainer_id);

        $allocation->delete();
        return redirect()->route('backend.trainerallocation.trainerallocation')->with('success', 'Trainer Allocation Deleted Successfully.');
    }

    public function getAllSchoolBatch(Request $request) {
        $school_id = (int) $request->school_id;
        if($school_id) {
            $school = School::findOrFail($school_id);
            $this->ensurePartnerAllocationAccess($school->id, null);
            return json_encode(['status' => 'success', 'data' => SchoolBatch::where('school_id', $school_id)->get()]);
        }
        return json_encode(['status' => 'fail']);
    }
    public function alltainer(Request $req)
    {
        $city_id = $req->city_id;
        $mood_id = $req->mood_id;

        $all_trainer = Trainer::query();
        if (isPartnerUser()) {
            applyCountryScope($all_trainer, 'country_id');
        }

        if ($mood_id == '1') {
            $all_trainer = $all_trainer->where('city', $city_id);
        }
        if ($mood_id == '2') {
            $all_trainer = $all_trainer->orWhere('mode', $mood_id);
        }
        $all_trainer = $all_trainer->get()->toArray();

        return json_encode($all_trainer);
    }

    public function classSchedule(Request $request){
        if(isset($request->school_id)){
            $this->ensurePartnerSchoolAccess($request->school_id);
            $school = School::with('ClassSchedule')->find($request->school_id)->toArray();
            $grade = Grade::all();
        }else{
            $school = School::with('ClassSchedule')->find(0);
            $grade = Grade::all();
        }
        return (string) view('backend.trainer_allocation.classschedulemodal', ['school' => $school,'grade' => $grade]);
    }

    public function trainer_schedule_show(Request $req)
    {
        $school_id = $req->school_id;
        if ($school_id == '0') {
            $new_array_events = [];
            echo json_encode($new_array_events);
        } else {
            $this->ensurePartnerSchoolAccess($school_id);
            $class_schedule = TrainerAllocation::with('trainer')->where('school_id', $school_id)->get();

            //$class_schedule=School::with('ClassSchedule')->find($school_id)->toArray();

            // $date=date('Y-m-d',strtotime($school_row['created_at']));
            // $school_weekly=$school_row['class_schedule'];

            $events = [];
            foreach ($class_schedule as $key => $schedules) {
                if (isset($schedules->trainer)) {
                    $events[] = [
                        'id' => $schedules->id,
                        'daysOfWeek' => [$schedules->day],
                        'startTime' => $schedules->class_start,
                        'endTime' => $schedules->class_end,
                        'color' => 'purple',
                        'title' => 'Trainer Name: ' . $schedules->trainer->trainer_name,
                        'description' => 'Trainer Name: ' . $schedules->trainer->trainer_name,
                    ];
                }
            }

            $school_event = AllocationEvent::where('school_id', $school_id)->get()->toArray();

            if ($school_event) {
                foreach ($school_event as $key => $school_events) {
                    $events_new[] = ['ids' => $school_events['id'], 'title' => $school_events['event_name'], 'start' => $school_events['event_date'], 'color' => $school_events['event_color']];
                }
                $new_array_events = array_merge($events, $events_new);
                echo json_encode($new_array_events);
            } else {
                echo json_encode($events);
            }
        }
    }

    public function assigntrainer(Request $req)
    {
        $this->ensurePartnerAllocationAccess($req->school_id, $req->trainer_id);

        //    echo $req->event_date; die();
        //$date=date('Y-m',strtotime($req->event_date));
        for ($i = 1; $i < 10; $i++) {
            TrainerAllocation::where('grade', 'grade' . $i)->update(['grade' => $i]);
        }
        $trainer_assign = TrainerAllocation::where('school_id', $req->school_id)->where('day', $req->day)->where('class_schedule', $req->class_schedule)->get()->toArray();
        if ($trainer_assign) {
            $response = [
                'trainer_exist' => 'This Schedule Trainer Already Exits',
            ];

            return json_encode($response);
        }
        $date = Carbon::parse($req->event_date);

        $weekNumber = $date->weekNumberInMonth;
        $start = $date->startOfWeek()->toDateString();
        $end = $date->endOfWeek()->toDateString();

        $class_schedlue = ClassSchedule::find($req->class_schedule);
        $hourdiff = round((strtotime($class_schedlue->start_time) - strtotime($class_schedlue->end_time)) / 3600, 1);

        $grade = $class_schedlue->grade;

        //echo abs($hourdiff);die();
        $weekly_hour = TrainerAllocation::where('trainer_id', $req->trainer_id)->where('class_date', '>=', $start)->where('class_date', '<=', $end)->sum('class_duration');
        $weekly_hour_new = $weekly_hour + abs($hourdiff);

        $today_tainer_hour = TrainerAllocation::where('trainer_id', $req->trainer_id)->where('class_date', $req->event_date)->sum('class_duration');
        $today_tainer_hour_new = $today_tainer_hour + abs($hourdiff);

        if (($weekly_hour_new > 16) || ($today_tainer_hour_new > 4)) {
            $response = [
                'today_tainer_hour' => 4,
                'trainer_id' => $req->trainer_id,
            ];

            return json_encode($response);
        }

        $TrainerAllocation = new TrainerAllocation();
        $TrainerAllocation->class_schedule = $req->class_schedule;
        $TrainerAllocation->school_id = $req->school_id;
        $TrainerAllocation->trainer_id = $req->trainer_id;
        $TrainerAllocation->class_date = $req->event_date;
        $TrainerAllocation->day = $req->day;
        $TrainerAllocation->grade = $grade;
        $TrainerAllocation->class_start = $class_schedlue->start_time;
        $TrainerAllocation->class_end = $class_schedlue->end_time;
        $TrainerAllocation->class_duration = abs($hourdiff);
        ///echo $req->day;
        //echo $req->class_schedule;

        $success = $TrainerAllocation->save();

        /* Email send to school and trainer */

        //School Email--------------
        //  $one_school=School::where('id',$req->school_id)->get('official_email_id')->toArray();
        //  $school_email=$one_school['0']['official_email_id'];

        //  $email_body="New Trainer Assign";

        //  file_put_contents('../resources/views/mail.blade.php',$email_body);
        //  $data = array('email'=>$school_email,'subject'=>"Trainer Assign");

        //  $send_mail=Mail::send('mail', $data, function($message) use ($data){
        //      $message->to($data['email'], 'kidsinterpreneurship')->subject
        //         ($data['subject']);
        //   });
        $students = Students::where('grade_id', $grade)->where('school_id', $req->school_id)->get();
        $trainer = Trainer::find($req->trainer_id);

        $start_time = date('g:i a', strtotime($class_schedlue->start_time));
        $end_time = date('g:i a', strtotime($class_schedlue->end_time));

        $notifications = [];
        foreach ($students as $student) {
            $obj = [
                'student_id'    => $student->id,
                'title'         => 'New Trainer Assign',
                'description'   => $trainer->trainer_name . ' will take a class every ' .date('l',strtotime($req->event_date)).' '.$start_time . ' - ' . $end_time,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
            $notifications[] = $obj;
        }

        StudentNotification::insert($notifications);

        $school = School::find($req->school_id);
        $level = Grade::find($grade);
        SchoolNotification::create([
            'school_id'    => $school->id,
            'title'         => 'New Trainer Assign',
            'description'   => $trainer->trainer_name . ' will take a class every ' .date('l',strtotime($req->event_date)).' '. $level->grade . ' ' . $start_time . ' - ' . $end_time,
        ]);

        TrainerNotification::create([
            'trainer_id'    => $trainer->id,
            'title'         => 'New Class Assign',
            'description'   => $school->school_name . ' take class of ' . $level->grade . ' at ' . $start_time . ' - ' . $end_time,
        ]);

        $after_weekly_hour = TrainerAllocation::where('trainer_id', $req->trainer_id)->where('class_date', '>=', $start)->where('class_date', '<=', $end)->sum('class_duration');

        $after_today_tainer_hour = TrainerAllocation::where('trainer_id', $req->trainer_id)->where('class_date', $req->event_date)->sum('class_duration');

        if (($after_weekly_hour > 16) || ($after_today_tainer_hour > 4)) {
            $response = [
                'success' => 4,
                'trainer_id' => $req->trainer_id,
            ];

            return json_encode($response);
        }

        if ($success) {
            $response = [
                'success' => true,
                'message' => 'successfully Inserted!',
            ];

            return json_encode($response);
        }
    }

    public function event_insert(Request $req)
    {
        $this->ensurePartnerSchoolAccess($req->school_id);
        $event = new AllocationEvent();
        $event->school_id = $req->school_id;
        $event->event_name = $req->event_name;
        $event->event_date = $req->event_date;
        $event->event_color = $req->event_color;
        $event->save();
    }

    public function trainer_class_schedule(Request $req)
    {
        $school_id = $req->school_id;
        $day = $req->day;

        $this->ensurePartnerSchoolAccess($school_id);

        $full_schedule = ClassSchedule::where('school_id', $school_id)->get()->toArray();
        //  echo '<pre>';
        //  print_r($full_schedule);die();
        $class_check = ClassSchedule::where('school_id', $school_id)->where('day', $day)->with(['level'])->get()->toArray();

        $class_schedlue = [];
        if ($class_check) {
            $events = [];
            foreach ($class_check as $schedules) {
                $events[] = ['title' => 'class time:' . $schedules['level']['grade'] . ' (' . $schedules['start_time'] . '-' . $schedules['end_time'] . ')', 'value' => $schedules['id']];
            }

            return json_encode($events);
        } else {
            foreach ($full_schedule as $full_schedules) {
                if ($full_schedules['day'] == '6') {
                    $day = 'Saturday';
                }
                if ($full_schedules['day'] == '0') {
                    $day = 'Sunday';
                }
                if ($full_schedules['day'] == '1') {
                    $day = 'Monday';
                }
                if ($full_schedules['day'] == '2') {
                    $day = 'Tuesday';
                }
                if ($full_schedules['day'] == '3') {
                    $day = 'Wednesday';
                }
                if ($full_schedules['day'] == '4') {
                    $day = 'Thursday';
                }
                if ($full_schedules['day'] == '5') {
                    $day = 'Friday';
                }
                $class_schedlue[] = ['title' => 'class time: Grade-' . $full_schedules['grade'] . ' Day-' . $day . '(' . $full_schedules['start_time'] . '-' . $full_schedules['end_time'] . ')'];
            }
            // for($i=0; $i<count($school_weekly); $i++){

            //     $events[]=['title'=>$day.' ('.$school_weekly[$i]['start_time'].'-'.$school_weekly[$i]['end_time'].')','start'=>$date];

            // }
            $response = [
                'no_schedule' => 'This day school has no class',
                'class_schedlue' => $class_schedlue,
            ];

            return json_encode($response);
        }
    }

    public function assigntrainer_delete(Request $req)
    {
        $trainer_id = $req->trainer_assign_id;
        $custom_event_id = $req->custom_event_id;
        if ($trainer_id) {
            $allocation = TrainerAllocation::findOrFail($trainer_id);
            $this->ensurePartnerSchoolAccess($allocation->school_id);
            $allocation->delete();
        }
        if ($custom_event_id) {
            $customEvent = AllocationEvent::findOrFail($custom_event_id);
            $this->ensurePartnerSchoolAccess($customEvent->school_id);
            $customEvent->delete();
        }

        echo json_encode('successfully deleted', JSON_THROW_ON_ERROR);
    }

    public function daySchoolClassSchedule(Request $request): string
    {
        $this->ensurePartnerSchoolAccess($request->school_id);
        $trainerallocations = TrainerAllocation::with(['trainer', 'level'])->where('school_id', $request->school_id)->where('day', $request->day)->get();

        return (string) view('backend.trainer_allocation.daydetails', ['trainers' => $trainerallocations]);
    }

    private function ensurePartnerSchoolAccess($schoolId): void
    {
        if (!isPartnerUser() || empty($schoolId)) {
            return;
        }

        if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
            abort(403, 'You are not authorized to access this school.');
        }
    }

    private function ensurePartnerTrainerAccess($trainerId): void
    {
        if (!isPartnerUser() || empty($trainerId)) {
            return;
        }

        if (!applyCountryScope(Trainer::query()->whereKey($trainerId), 'country_id')->exists()) {
            abort(403, 'You are not authorized to access this trainer.');
        }
    }

    private function ensurePartnerAllocationAccess($schoolId, $trainerId = null): void
    {
        if (!isPartnerUser()) {
            return;
        }

        $this->ensurePartnerSchoolAccess($schoolId);
        $this->ensurePartnerTrainerAccess($trainerId);
    }
}
