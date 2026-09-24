<?php

namespace App\Http\Controllers\school;

use App\Http\Controllers\Controller;

use App\Models\Meeting;
use App\Models\School;
use App\Models\TrainerAllocation;
use App\Models\TrainerSessionReport;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\ExternalSession;
use Carbon\Carbon;

class ClassScheduleController extends Controller
{
    public function Class_schedule()
    {
        return view('school.class_schedule.schedule');
    }

    public function school_classSchedule(Request $request)
    {
        $schoolId = Session::get('school_id');
        
           // Get school's country timezone
        $school = School::with('countrynew')->select('country_id')->find($schoolId);
        $timeZone = $school?->countrynew?->timezone ?? 'UTC';

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

        // Get sessions where attendees include this school or are for 'all'
        $sessions = ExternalSession::where('attendee_type', 'schools')
            ->where(function ($query) use ($schoolId) {
                $query->where('attendees', 'all')
                    ->orWhereRaw("FIND_IN_SET(?, attendees)", [$schoolId]);
            })
            ->where('is_cancelled', false)
            ->get();

        // Format data for FullCalendar
        $events = ExternalSession::occurrencesForSessions($sessions, $from, $to)
            ->map(function ($entry) use ($timeZone) {
                $session = $entry['session'];
                $localTime = $entry['date_time']->copy()->setTimezone($timeZone);
                return [
                    'session_title' => $session->title,
                    'start' => $localTime->toIso8601String(),
                    'time'  => $localTime->format('g:i A'),
                    'speaker' => $session->speaker,
                    'zoom_link' => $session->zoom_link,
                    'agenda' => $session->agenda,
                    'session_type' => $session->session_type,
                ];
            });
        
        return $events->toJson();
    }

      public function sessionReport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        if ($request->ajax()) {
            return $this->sessionReportDataTable($fromDate, $toDate);
        }

        return view('school.class_schedule.session_report', compact('fromDate', 'toDate'));
    }

    private function sessionReportDataTable($fromDate, $toDate)
    {
        $schoolId = Session::get('school_id');

        $school = School::with('countrynew')->select('country_id')->find($schoolId);
        $timeZone = $school?->countrynew?->timezone ?? 'UTC';

        $fromBoundary = $fromDate ? Carbon::parse($fromDate, $timeZone)->startOfDay() : null;
        $toBoundary   = $toDate ? Carbon::parse($toDate, $timeZone)->endOfDay() : null;

        $reports = TrainerSessionReport::with(['photos'])
            ->where('school_id', $schoolId)
            ->where('status', 1)
            ->get();

        $sessions = ExternalSession::whereIn('id', $reports->pluck('session_id')->unique()->values())
            ->get()->keyBy('id');

        $reportData = $reports->filter(function ($report) use ($sessions, $fromBoundary, $toBoundary, $timeZone) {
            $session = $sessions->get($report->session_id);
            if (!$session) {
                return false;
            }

            $localDate = $report->session_date
                ? Carbon::parse($report->session_date, $timeZone)->startOfDay()
                : Carbon::parse($session->date_time, 'UTC')->setTimezone($timeZone);

            if ($fromBoundary && $localDate->lt($fromBoundary)) {
                return false;
            }
            if ($toBoundary && $localDate->gt($toBoundary)) {
                return false;
            }

            return true;
        })->map(function ($report) use ($sessions, $timeZone) {
            $session = $sessions->get($report->session_id);
            $localDate = $report->session_date
                ? Carbon::parse($report->session_date, $timeZone)
                : Carbon::parse($session->date_time, 'UTC')->setTimezone($timeZone);

            return [
                'date_formatted'   => $localDate ? $localDate->format('d/m/Y') : 'N/A',
                'date_timestamp'   => $localDate ? $localDate->timestamp : 0,
                'session_title'    => $session?->title ?? 'N/A',
                'session_summary'  => $report->session_summary,
                'learning_outcome' => $report->learning_outcome,
                'skill_focus'      => $report->skill_focus,
                'photos'           => $report->photos->map(fn ($p) => ['url' => asset('storage/' . $p->file_path)])->values(),
            ];
        })->sortBy('date_timestamp')->values();

        return datatables()->of($reportData)->make(true);
    }

 
    public function downloadSessionReportPdf(Request $request)
    {
        $schoolId = Session::get('school_id');

        $school    = School::with('countrynew')->find($schoolId);
        $timeZone  = $school?->countrynew?->timezone ?? 'UTC';

        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');

        $fromBoundary = $fromDate ? Carbon::parse($fromDate, $timeZone)->startOfDay() : null;
        $toBoundary   = $toDate ? Carbon::parse($toDate, $timeZone)->endOfDay() : null;

        $reports = TrainerSessionReport::with(['photos'])
            ->where('school_id', $schoolId)
            ->where('status', 1)
            ->get();

        $sessions = ExternalSession::whereIn('id', $reports->pluck('session_id')->unique()->values())
            ->get()->keyBy('id');

        $reportData = $reports->filter(function ($report) use ($sessions, $fromBoundary, $toBoundary, $timeZone) {
            $session = $sessions->get($report->session_id);
            if (!$session) {
                return false;
            }

            $localDate = $report->session_date
                ? Carbon::parse($report->session_date, $timeZone)->startOfDay()
                : Carbon::parse($session->date_time, 'UTC')->setTimezone($timeZone);

            if ($fromBoundary && $localDate->lt($fromBoundary)) {
                return false;
            }
            if ($toBoundary && $localDate->gt($toBoundary)) {
                return false;
            }

            return true;
        })->map(function ($report) use ($sessions, $timeZone) {
            $session   = $sessions->get($report->session_id);
            $localDate = $report->session_date
                ? Carbon::parse($report->session_date, $timeZone)
                : Carbon::parse($session->date_time, 'UTC')->setTimezone($timeZone);

            $firstPhoto = null;
            $firstPhotoRecord = $report->photos->first();
            if ($firstPhotoRecord) {
                $path = public_path('storage/' . $firstPhotoRecord->file_path);
                if (file_exists($path)) {
                    $mime       = mime_content_type($path);
                    $firstPhoto = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                }
            }

            return [
                'date_formatted'  => $localDate ? $localDate->format('d/m/Y') : 'N/A',
                'date_timestamp'  => $localDate ? $localDate->timestamp : 0,
                'session_title'   => $session?->title ?? 'N/A',
                'session_summary' => $report->session_summary,
                'learning_outcome' => $report->learning_outcome,
                'skill_focus'     => $report->skill_focus,
                'photo'           => $firstPhoto,
            ];
        })->sortBy('date_timestamp')->values();

        $logoPath = public_path('asset/images/logo.png');
        $logoData = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('school.class_schedule.session_report_pdf', compact(
            'reportData', 'school', 'fromDate', 'toDate', 'logoData'
        ))->setPaper('A4', 'portrait');

        $schoolSlug = $school->school_name
            ? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($school->school_name)))
            : 'school';
        $filename = $schoolSlug . '-session-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    public function dayClassSchedule(Request $req): string
    {
        $schoolId = School::where('user_id', Session::get('user_id'))->first();
        $trainer_allocations = TrainerAllocation::with(['trainer', 'level', 'getSchool'])->where('school_id', $schoolId->id)->where('day', $req->day)->where('class_date',date('Y-m-d',strtotime($req->date)))->get();
        // dd($trainer_allocations);
        foreach ($trainer_allocations as $trainer_allocation) {
            // $this->trainerAllocationToCreateMeeting($trainer_allocation, $req->date); //School can't create zoom link..
        }
        $allmeetings = Meeting::whereIn('allowcated_id', $trainer_allocations->pluck('id')->toArray())->get();
        return (string) view('school.class_schedule.classdetails', compact('trainer_allocations', 'allmeetings'));
    }
}
