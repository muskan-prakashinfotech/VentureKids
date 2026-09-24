<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use App\Exports\StudentObservationDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Observation;
use App\Models\Grade;
use App\Models\Trainer;
use App\Models\TrainerAllocationNew;
use App\Models\ExternalSession;
use App\Models\TrainerSessionReport;
use App\Models\StudentObservations;
use App\Models\Students;
use App\Helpers\SimpleZipBuilder;

class ObservationController extends Controller
{
    // public function exportData()
    // {
    //     $school_list = School::select(['id', 'school_name'])->whereHas('user', function($query) {
    //         $query->where('suspend',2);
    //     })->get();

    //     return view('backend.observation.export', compact('school_list'));
    // }

    // public function downloadData(Request $request)
    // {
    //     $request->validate([
    //         'school' => 'required',
    //     ]);

    //     $fileName = 'observationDataExport_'.date('Ymd').'.xlsx';
        
    //     $param['source'] = 'Admin'; 
    //     $param['school_id'] = $request->school; 

    //     return Excel::download(new StudentObservationDataExport($param), $fileName, \Maatwebsite\Excel\Excel::XLSX);

    // }

    public function index()
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $observation_list = Observation::all();

        $usedObservationIds = StudentObservations::whereNotNull('observation_id')
            ->pluck('observation_id')
            ->flatMap(fn ($val) => explode(',', $val))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique();

        return view('backend.observation.index', compact('observation_list', 'usedObservationIds'));
    }

    public function create()
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        return view('backend.observation.create');
    }

    public function store(Request $request)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name'     => 'required',
            'icon'     => 'required|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'category' => 'required|in:skill,mindset',
        ]);

        $observation = new Observation();
        $observation->name     = $request->name;
        $observation->category = $request->category;

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('observations'), $filename);
            $observation->icon = $filename;
        }
        
        $observation->save();

        return redirect()->route('backend.observation_list')->with('success', 'Observation added successfully.');
    }

    public function edit($observationId)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $observationData = Observation::find($observationId);

        return view('backend.observation.edit', compact('observationData'));
    }

    public function update(Request $request)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name'     => 'required',
            'icon'     => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'category' => 'required|in:skill,mindset',
        ]);

        $observation_id = $request->observation_id;
        $observation = Observation::find($observation_id);

        if($observation) {
            $observation->name     = $request->name;
            $observation->category = $request->category;

            if ($request->hasFile('icon')) {
                $oldPath = public_path('observations/' . $observation->icon);
                if (file_exists($oldPath) && $observation->icon) {
                    unlink($oldPath);
                }
                $file = $request->file('icon');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('observations'), $filename);
                $observation->icon = $filename;
            }
            $observation->save();
        }

        return redirect()->route('backend.observation_list')->with('success', 'Observation updated successfully.');
    }

    public function destory($observationId)
    {
        if (isPartnerUser()) {
            abort(403, 'Access denied.');
        }

        $observation = Observation::find($observationId);
        if ($observation) {
            $isUsed = StudentObservations::where('observation_id', $observationId)
                ->orWhereRaw('FIND_IN_SET(?, observation_id)', [$observationId])
                ->exists();

            if ($isUsed) {
                return redirect()->route('backend.observation_list')
                    ->with('error', 'This observation cannot be deleted because it has already been recorded for one or more students.');
            }

            $imagePath = public_path('observations/' . $observation->icon);
            if (file_exists($imagePath) && $observation->icon) {
                unlink($imagePath);
            }

            $observation->delete();
        }

        return redirect()->route('backend.observation_list')->with('success', 'Observation deleted successfully.');
    }

    public function viewAllObservations(Request $request)
    {
        // Schools (same pattern as exportData)
        $school_list = School::select(['id', 'school_name', 'country_id'])->whereHas('user', function ($q) {
            $q->where('suspend', 2);
        });

        if (isPartnerUser()) {
            applyCountryScope($school_list, 'country_id');
        }

        $school_list = $school_list->get();

        // Levels — exact same logic as StudentAccountController::index()
        $levels = Grade::where('is_publish', 1)->orderBy('display_order_id')->get();
        $filtered_levels = [];
        $levels->filter(function ($item) use (&$filtered_levels) {
            if ($item->is_primary == 1) {
                $filtered_levels['primary'][$item->id] = $item->toArray();
            } else {
                $filtered_levels['add-ons'][$item->id] = $item->toArray();
            }
        });

        // Keyed by id, reusing the query above — used to resolve session level IDs to
        // names when building each row.
        $all_grades = $levels->keyBy('id');

        // All trainers + per-school allocation map (used for JS-driven trainer dropdown filtering)
        $all_trainers = Trainer::select(['id', 'trainer_name'])->orderBy('trainer_name')->get();
        $trainer_allocations = TrainerAllocationNew::select(['trainer_id', 'school_id'])->get()
            ->groupBy('school_id')
            ->map(fn ($items) => $items->pluck('trainer_id')->unique()->values());

        // Keyed by id — used to resolve a session's trainer_ids to trainer names
        $all_trainers_map = $all_trainers->keyBy('id');

        // Filter inputs
        $selected_school  = $request->get('school_id');
        $selected_trainer = $request->get('trainer_id');
        $selected_level   = $request->get('level_id');
        $date_from        = $request->get('date_from');
        $date_to          = $request->get('date_to');

        if ($selected_school) {
            $this->ensurePartnerSchoolAccess($selected_school);
        }

        // Trainers for dropdown (server-filtered on initial load; JS handles live updates)
        $selected_school_data   = null;
        $school_trainer_count   = 0;
        $school_student_count   = 0;

        if ($selected_school) {
            $school_trainer_ids = TrainerAllocationNew::where('school_id', $selected_school)
                ->pluck('trainer_id')->unique();
            $trainers = Trainer::whereIn('id', $school_trainer_ids)
                ->select(['id', 'trainer_name'])->orderBy('trainer_name')->get();

            $selected_school_data = School::select(['id', 'school_name'])->find($selected_school);
            $school_trainer_count = $school_trainer_ids->count();
            $school_student_count = Students::withoutGlobalScopes()
                ->where('school_id', $selected_school)->count();
        } else {
            $trainers = collect();
        }

        // The session table itself is populated entirely via this same endpoint's
        // AJAX/DataTables response (serverSide, same {draw, recordsTotal,
        // recordsFiltered, data} shape as trainer/student/list) — this branch only
        // needs to render the filter form / page shell once.
        if ($request->ajax()) {
            return $this->sessionsDataTable(
                $selected_school, $selected_trainer, $selected_level, $date_from, $date_to,
                $all_grades, $all_trainers_map, $selected_school_data, $school_trainer_count, $school_student_count
            );
        }

        return view('backend.observation.all_observations', compact(
            'school_list', 'trainers', 'filtered_levels',
            'all_trainers', 'trainer_allocations',
            'selected_school', 'selected_trainer', 'selected_level', 'date_from', 'date_to',
            'selected_school_data', 'school_trainer_count', 'school_student_count'
        ));
    }

    /**
     * Server-side DataTables response for the session table, in the same
     * {draw, recordsTotal, recordsFiltered, data} shape yajra produces for
     * trainer/student/list — school info + empty-state text ride along via with().
     */
    private function sessionsDataTable(
        $selected_school, $selected_trainer, $selected_level, $date_from, $date_to,
        $all_grades, $all_trainers_map, $selected_school_data, $school_trainer_count, $school_student_count
    ) {
        if ($selected_school) {
            [$reports, $sessions_map, $trainers_map, $student_obs_raw, $students_map] = $this->getReportedSessionData(
                $selected_school, $selected_trainer, $selected_level, $date_from, $date_to
            );
        } else {
            $reports = collect();
            $sessions_map = collect();
            $trainers_map = collect();
            $student_obs_raw = collect();
            $students_map = collect();
        }

        $sessionsData = $this->buildSessionsCollection(
            $reports, $sessions_map, $trainers_map, $student_obs_raw, $students_map,
            $all_grades, $all_trainers_map, $selected_school
        );

        return datatables()->of($sessionsData)
            ->with('school', $selected_school_data ? [
                'school_name'   => $selected_school_data->school_name,
                'trainer_count' => $school_trainer_count,
                'student_count' => $school_student_count,
            ] : null)
            ->with('empty_message', $selected_school
                ? 'No sessions report found for selected school.'
                : 'Please select a school to view session reports.')
            ->make(true);
    }

    /**
     * Builds the row data consumed by the observations/all page's DataTable.
     * Mirrors the row markup in all_observations.blade.php exactly, so the
     * client-side column renderers can build each cell from these fields.
     *
     * One row per reported occurrence (not per session) — a recurring session
     * with reports on several dates produces one row per dated report, each
     * showing that occurrence's own date and roster.
     */
    private function buildSessionsCollection(
        $reports, $sessions_map, $trainers_map, $student_obs_raw, $students_map,
        $all_grades, $all_trainers_map, $selected_school
    ) {
        return $reports->map(function ($report) use (
            $sessions_map, $trainers_map, $student_obs_raw, $students_map, $all_grades, $all_trainers_map, $selected_school
        ) {
            $session = $sessions_map->get($report->session_id);

            $sessionLevelValue = $session->levels;
            $levelBadges = [];
            if ($sessionLevelValue === 'all') {
                $levelBadges[] = ['label' => 'All', 'type' => 'primary'];
            } else {
                $levelIds = array_filter(array_map('trim', explode(',', $sessionLevelValue)));
                foreach ($levelIds as $lid) {
                    if (isset($all_grades[$lid])) {
                        $grade = $all_grades[$lid];
                        $levelBadges[] = [
                            'label' => $grade['grade'],
                            'type'  => $grade['is_primary'] == 1 ? 'success' : 'warning',
                        ];
                    }
                }
            }

            $reportTrainer = $trainers_map->get($report->trainer_id);

            if ($reportTrainer) {
                $trainerName = $reportTrainer->trainer_name;
            } elseif ($session->trainer_ids === 'all') {
                $trainerName = 'All Trainers';
            } elseif (!empty($session->trainer_ids)) {
                $sessionTrainerIds = array_filter(array_map('trim', explode(',', $session->trainer_ids)));
                $trainerName = collect($sessionTrainerIds)
                    ->map(fn ($id) => $all_trainers_map->get($id)?->trainer_name)
                    ->filter()
                    ->implode(', ') ?: 'N/A';
            } else {
                $trainerName = 'N/A';
            }

            $obsKey = $report->session_id . '|' . ($report->session_date ?? '');
            $sessionStudents = $student_obs_raw->get($obsKey, collect());
            $totalStudents   = $sessionStudents->count();
            $displayStudents = $sessionStudents->take(5);

            $students = $displayStudents->map(function ($obs) use ($students_map) {
                $student = $students_map->get($obs->student_id);
                if (!$student) {
                    return null;
                }

                $firstName = explode(' ', trim($student->name ?? 'S'))[0];
                $hasImage  = !empty($student->image) && $student->image !== 'no_image';

                return [
                    'id'         => $student->id,
                    'first_name' => $firstName,
                    'initial'    => strtoupper(substr($firstName, 0, 1)),
                    'image_url'  => $hasImage ? asset('tenants/' . $student->image) : null,
                ];
            })->filter()->values();

            return [
                'date'           => \Carbon\Carbon::parse($report->occurrence_date, 'UTC')->timezone('Asia/Singapore')->format('M d, Y'),
                'level_badges'   => $levelBadges,
                'topic'          => $session->title,
                'trainer_name'   => $trainerName,
                'trainer_update' => $report->highlights_feedback ?? null,
                'students'       => $students,
                'total_students' => $totalStudents,
                'more_count'     => max(0, $totalStudents - 5),
                'view_url'       => route('backend.observations.session.detail', $session->id) . '?school_id=' . $selected_school . '&session_date=' . $report->occurrence_date,
            ];
        })->values();
    }

    private function getReportedSessionData($selected_school, $selected_trainer, $selected_level, $date_from, $date_to)
    {
        $reportsQuery = TrainerSessionReport::where('status', 1)
            ->where('school_id', $selected_school);

        if ($selected_trainer) {
            $reportsQuery->where('trainer_id', $selected_trainer);
        }

        $reports = $reportsQuery->get();
        $session_ids = $reports->pluck('session_id')->unique();

        $sessionsQuery = ExternalSession::whereIn('id', $session_ids);
        if ($selected_level) {
            $sessionsQuery->where(function ($q) use ($selected_level) {
                $q->where('levels', 'all')
                  ->orWhereRaw('FIND_IN_SET(?, levels)', [$selected_level]);
            });
        }
        $sessions_map = $sessionsQuery->get()->keyBy('id');

        $reports = $reports
            ->filter(fn ($report) => $sessions_map->has($report->session_id))
            ->map(function ($report) use ($sessions_map) {
                $session = $sessions_map->get($report->session_id);
                $report->occurrence_date = $report->session_date
                    ?: \Carbon\Carbon::parse($session->date_time, 'UTC')->setTimezone('Asia/Singapore')->format('Y-m-d');
                return $report;
            })
            ->when($date_from, fn ($c) => $c->filter(fn ($r) => $r->occurrence_date >= $date_from))
            ->when($date_to, fn ($c) => $c->filter(fn ($r) => $r->occurrence_date <= $date_to))
            ->sortByDesc('occurrence_date')
            ->values();

        $report_trainer_ids = $reports->pluck('trainer_id')->unique()->filter()->toArray();
        $trainers_map = Trainer::whereIn('id', $report_trainer_ids)->get()->keyBy('id');

        $school_student_ids = Students::withoutGlobalScopes()
            ->where('school_id', $selected_school)
            ->pluck('id');

        $student_obs_raw = StudentObservations::whereIn('external_session_id', $session_ids)
            ->whereIn('student_id', $school_student_ids)
            ->select(['external_session_id', 'student_id', 'session_date'])
            ->distinct()
            ->get()
            ->groupBy(fn ($obs) => $obs->external_session_id . '|' . ($obs->session_date ?? ''));

        $all_student_ids = $student_obs_raw->flatten()->pluck('student_id')->unique()->filter()->toArray();
        $students_map = Students::withoutGlobalScopes()
            ->whereIn('id', $all_student_ids)
            ->select(['id', 'name', 'image'])
            ->get()
            ->keyBy('id');

        return [$reports, $sessions_map, $trainers_map, $student_obs_raw, $students_map];
    }

    public function downloadObservationsPdf(Request $request)
    {
        $request->validate(['school_id' => 'required']);

        $selected_school  = $request->get('school_id');
        $selected_trainer = $request->get('trainer_id');
        $selected_level   = $request->get('level_id');
        $date_from        = $request->get('date_from');
        $date_to          = $request->get('date_to');

        $this->ensurePartnerSchoolAccess($selected_school);

        $school = School::select(['id', 'school_name'])->find($selected_school);

        [$reports, $sessions_map] = $this->getReportedSessionData(
            $selected_school, $selected_trainer, $selected_level, $date_from, $date_to
        );
        $reports->load('photos');

        $reportData = $reports->map(function ($report) use ($sessions_map) {
            $session   = $sessions_map->get($report->session_id);
            $localDate = \Carbon\Carbon::parse($report->occurrence_date);

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
                'date_formatted'   => $localDate->format('d/m/Y'),
                'date_timestamp'   => $localDate->timestamp,
                'session_title'    => $session->title ?? 'N/A',
                'session_summary'  => $report->session_summary ?? null,
                'learning_outcome' => $report->learning_outcome ?? null,
                'skill_focus'      => $report->skill_focus ?? null,
                'photo'            => $firstPhoto,
            ];
        })->sortBy('date_timestamp')->values();

        $logoPath = public_path('asset/images/logo.png');
        $logoData = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('school.class_schedule.session_report_pdf', [
            'reportData' => $reportData,
            'school'     => $school,
            'fromDate'   => $date_from,
            'toDate'     => $date_to,
            'logoData'   => $logoData,
        ])->setPaper('A4', 'portrait');

        $schoolSlug = $school?->school_name
            ? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($school->school_name)))
            : 'school';
        $filename = $schoolSlug . '-session-report-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generates every qualifying student's observation report in a single request and
     * returns either the lone PDF or a ZIP of them all — previously the client fetched
     * the student list then issued one additional request per student, which meant a
     * school with hundreds/thousands of matching students fired that many AJAX calls.
     */
    public function downloadStudentObservationReports(Request $request)
    {
        $request->validate([
            'school_id' => 'required',
            'date_from' => 'required|date',
            'date_to'   => 'required|date',
        ]);

        $schoolId        = $request->get('school_id');
        $selectedTrainer = $request->get('trainer_id');
        $selectedLevel   = $request->get('level_id');
        $dateFrom        = $request->get('date_from');
        $dateTo          = $request->get('date_to');

        $this->ensurePartnerSchoolAccess($schoolId);

        $studentIds = $this->qualifyingStudentIdsForObservationReport(
            $schoolId, $selectedTrainer, $selectedLevel, $dateFrom, $dateTo
        );

        if ($studentIds->isEmpty()) {
            return response()->json(['error' => 'No student observations found for the selected school and date range.'], 404);
        }

        $students = Students::withoutGlobalScopes()->whereIn('id', $studentIds)->get(['id', 'name', 'image']);

        $logoPath = public_path('asset/images/logo.png');
        $logoData = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $openAiService = app(\App\Services\OpenAIService::class);
        $iconCache = [];

        $pdfs = [];
        foreach ($students as $student) {
            $binary = $this->buildStudentObservationPdfBinary(
                $student, $schoolId, $selectedTrainer, $selectedLevel, $dateFrom, $dateTo, $logoData, $openAiService, $iconCache
            );

            if ($binary === null) {
                continue;
            }

            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($student->name))) ?: 'student';
            $pdfs[] = ['filename' => $slug . '-observation-report.pdf', 'binary' => $binary];
        }

        if (empty($pdfs)) {
            return response()->json(['error' => 'No qualifying observations found for the selected school and date range.'], 404);
        }

        if (count($pdfs) === 1) {
            return response($pdfs[0]['binary'], 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $pdfs[0]['filename'] . '"',
            ]);
        }

        $zipBinary = SimpleZipBuilder::build(SimpleZipBuilder::dedupeNames($pdfs));
        $zipFilename = 'student-observations-' . now()->format('Y-m-d') . '.zip';

        return response($zipBinary, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFilename . '"',
        ]);
    }

    /**
     * Session IDs whose own scheduled date (not record creation time) falls in range —
     * this is the same date field the "Session Updates & Observations" table filters
     * by, so the student observation download stays in sync with what the table shows.
     */
    private function sessionIdsInDateRange($dateFrom, $dateTo)
    {
        $query = ExternalSession::query();

        if ($dateFrom) {
            $query->whereDate('date_time', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date_time', '<=', $dateTo);
        }

        return $query->pluck('id');
    }

    private function qualifyingStudentIdsForObservationReport($selectedSchool, $selectedTrainer, $selectedLevel, $dateFrom, $dateTo)
    {
        $obsQuery = StudentObservations::where('school_id', $selectedSchool)
            ->whereNotNull('external_session_id')
            ->whereNotNull('short_note')
            ->where('short_note', '!=', '');

        $obsQuery->where(function ($q) use ($dateFrom, $dateTo) {
            if ($dateFrom || $dateTo) {
                $q->where(function ($sq) use ($dateFrom, $dateTo) {
                    $sq->whereNotNull('session_date');
                    if ($dateFrom) {
                        $sq->whereDate('session_date', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $sq->whereDate('session_date', '<=', $dateTo);
                    }
                })->orWhere(function ($sq) use ($dateFrom, $dateTo) {
                    $sq->whereNull('session_date');
                    if ($dateFrom) {
                        $sq->whereDate('created_at', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $sq->whereDate('created_at', '<=', $dateTo);
                    }
                });
            }
        });

        if ($selectedTrainer) {
            $obsQuery->where('trainer_id', $selectedTrainer);
        }

        if ($selectedLevel) {
            $obsQuery->where(function ($q) use ($selectedLevel) {
                $q->where('grade_id', $selectedLevel)
                  ->orWhereRaw('FIND_IN_SET(?, grade_id)', [$selectedLevel]);
            });
        }

        return $obsQuery->pluck('student_id')->unique()->filter()->values();
    }

    /**
     * Builds one student's observation report PDF, scoped to exactly the filters the
     * user selected on the page (school/trainer/level/date range) — observations from
     * other schools, trainers, or levels outside that scope must never leak into it.
     */
    private function buildStudentObservationPdfBinary($student, $schoolId, $selectedTrainer, $selectedLevel, $fromDate, $toDate, $logoData, $openAiService, array &$iconCache)
    {
        $query = StudentObservations::where('student_id', $student->id)
            ->where('school_id', $schoolId)
            ->whereNotNull('external_session_id')
            ->whereNotNull('short_note')
            ->where('short_note', '!=', '');

        if ($selectedTrainer) {
            $query->where('trainer_id', $selectedTrainer);
        }

        if ($selectedLevel) {
            $query->where(function ($q) use ($selectedLevel) {
                $q->where('grade_id', $selectedLevel)
                  ->orWhereRaw('FIND_IN_SET(?, grade_id)', [$selectedLevel]);
            });
        }

        $query->where(function ($q) use ($fromDate, $toDate) {
            if ($fromDate || $toDate) {
                $q->where(function ($sq) use ($fromDate, $toDate) {
                    $sq->whereNotNull('session_date');
                    if ($fromDate) {
                        $sq->whereDate('session_date', '>=', $fromDate);
                    }
                    if ($toDate) {
                        $sq->whereDate('session_date', '<=', $toDate);
                    }
                })->orWhere(function ($sq) use ($fromDate, $toDate) {
                    $sq->whereNull('session_date');
                    if ($fromDate) {
                        $sq->whereDate('created_at', '>=', $fromDate);
                    }
                    if ($toDate) {
                        $sq->whereDate('created_at', '<=', $toDate);
                    }
                });
            }
        });

        $records = $query->orderBy('created_at', 'asc')->get();
        if ($records->isEmpty()) {
            return null;
        }

        $allObsIds = $records->flatMap(function ($rec) {
            return array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
        })->unique()->toArray();

        $observations = Observation::whereIn('id', $allObsIds)->get()->keyBy('id');

        $obsData = $records->map(function ($rec) use ($observations, &$iconCache) {
            $ids = array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
            $obs = collect($ids)->map(fn ($id) => $observations->get($id))->filter()->first();

            if ($obs && !array_key_exists($obs->id, $iconCache)) {
                $iconPath = public_path('observations/' . $obs->icon);
                $iconCache[$obs->id] = file_exists($iconPath)
                    ? 'data:' . mime_content_type($iconPath) . ';base64,' . base64_encode(file_get_contents($iconPath))
                    : null;
            }

            $imgPath = $rec->image ? public_path('storage/' . $rec->image) : null;

            return [
                'obs_id'     => $obs ? $obs->id : null,
                'name'       => $obs ? $obs->name : 'Observation',
                'category'   => $obs ? $obs->category : null,
                'icon_data'  => $obs ? ($iconCache[$obs->id] ?? null) : null,
                'short_note' => $rec->short_note,
                'image_data' => ($imgPath && file_exists($imgPath))
                                    ? 'data:' . mime_content_type($imgPath) . ';base64,' . base64_encode(file_get_contents($imgPath))
                                    : null,
                'date'       => $rec->session_date
                                    ? \Carbon\Carbon::parse($rec->session_date)->format('j M Y')
                                    : ($rec->created_at ? $rec->created_at->format('j M Y') : ''),
                'session_date' => $rec->session_date,
            ];
        })->values();

        $photos = $obsData->filter(fn ($r) => !empty($r['image_data']))
            ->pluck('image_data')->shuffle()->take(4)->values();

        // Skills Demonstrated: every distinct skill tagged in range, with its occurrence
        // count — same as Trainer\StudentController::generateObservationReport().
        $skillsDemonstrated = $obsData
            ->filter(fn ($r) => $r['category'] === 'skill' && $r['obs_id'])
            ->groupBy('obs_id')
            ->map(function ($group) {
                $entry = $group->first();
                $entry['count'] = $group->count();
                return $entry;
            })
            ->values();

        // Mindsets Built: same treatment for mindsets.
        $mindsetsDemonstrated = $obsData
            ->filter(fn ($r) => $r['category'] === 'mindset' && $r['obs_id'])
            ->groupBy('obs_id')
            ->map(function ($group) {
                $entry = $group->first();
                $entry['count'] = $group->count();
                return $entry;
            })
            ->values();

        // Moments That Made Me Shine: every skill/mindset above with its full list of
        // anecdotal notes for the range.
        $momentGroups = $skillsDemonstrated->concat($mindsetsDemonstrated)
            ->map(function ($entry) use ($obsData) {
                $entry['notes'] = $obsData
                    ->filter(fn ($r) => $r['obs_id'] === $entry['obs_id'] && $r['category'] === $entry['category'])
                    ->pluck('short_note')
                    ->filter()
                    ->values();
                return $entry;
            })
            ->filter(fn ($group) => $group['notes']->isNotEmpty())
            ->values();

        $firstName = explode(' ', trim($student->name))[0];
        $aiSummary = $obsData->isNotEmpty()
            ? $openAiService->generateObservationSummary($firstName, $obsData->toArray())
            : null;

        $studentPhotoData = null;
        if (!empty($student->image) && $student->image !== 'no_image') {
            $photoPath = public_path('tenants/' . $student->image);
            if (file_exists($photoPath)) {
                $mime = mime_content_type($photoPath);
                $studentPhotoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($photoPath));
            }
        }

        // Trainer name — sourced from the student_observations rows themselves (who
        // actually recorded each observation), not the page-level trainer filter, which
        // may be left blank or wouldn't reflect multiple trainers across sessions.
        $trainerIds = $records->pluck('trainer_id')->filter()->unique()->values();
        $trainerName = $trainerIds->isNotEmpty()
            ? Trainer::whereIn('id', $trainerIds)->pluck('trainer_name')->implode(', ')
            : '';

        $sessionIds = $records->pluck('external_session_id')->filter()->unique()->toArray();
        $sessions   = ExternalSession::whereIn('id', $sessionIds)->get();

        $hasAllLevels = false;
        $sessionLevelIds = [];
        foreach ($sessions as $session) {
            if ($session->levels === 'all') {
                $hasAllLevels = true;
            } else {
                $sessionLevelIds = array_merge(
                    $sessionLevelIds,
                    array_filter(array_map('trim', explode(',', $session->levels ?? '')))
                );
            }
        }
        $sessionLevelIds = array_unique($sessionLevelIds);

        if ($hasAllLevels) {
            $gradeName = 'All Levels';
        } elseif (!empty($sessionLevelIds)) {
            $gradeName = Grade::whereIn('id', $sessionLevelIds)->pluck('grade')->implode(', ');
        } else {
            $gradeName = '';
        }

        $durationFrom = $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/y') : '';
        $durationTo   = $toDate ? \Carbon\Carbon::parse($toDate)->format('d/m/y') : '';

        return Pdf::loadView('trainer.student.observation_report_pdf', compact(
            'student', 'firstName', 'obsData', 'aiSummary', 'logoData', 'fromDate', 'toDate',
            'photos', 'momentGroups', 'skillsDemonstrated', 'mindsetsDemonstrated',
            'studentPhotoData', 'gradeName', 'trainerName', 'durationFrom', 'durationTo'
        ))->setPaper('A4', 'portrait')->output();
    }

    public function viewSessionObservations(Request $request, $sessionId)
    {
        $session     = ExternalSession::findOrFail($sessionId);
        $schoolId    = $request->get('school_id');
        if ($schoolId) {
            $this->ensurePartnerSchoolAccess($schoolId);
        }
        $sessionDate = $request->get('session_date');
        $observationsQuery = StudentObservations::where('external_session_id', $sessionId)
            ->when($sessionDate, fn ($q) => $q->where(function ($qq) use ($sessionDate) {
                $qq->where('session_date', $sessionDate)->orWhereNull('session_date');
            }));

        if ($schoolId) {
            $school_student_ids = Students::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->pluck('id');
            $observationsQuery->whereIn('student_id', $school_student_ids);
        }

        $rawObservations = $observationsQuery
            ->orderBy('student_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $allObsIds = $rawObservations->flatMap(function ($rec) {
            return array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
        })->unique()->filter()->values()->toArray();

        $observationMasters = Observation::whereIn('id', $allObsIds)->get()->keyBy('id');

        $studentIds = $rawObservations->pluck('student_id')->unique()->filter()->toArray();
        $students   = Students::withoutGlobalScopes()
            ->whereIn('id', $studentIds)
            ->select(['id', 'name', 'image'])
            ->get()
            ->keyBy('id');

        $grouped = $rawObservations->groupBy('student_id')->map(function ($recs, $studentId) use ($students, $observationMasters) {
            $student    = $students->get($studentId);
            $firstName  = $student ? explode(' ', trim($student->name ?? 'S'))[0] : 'Unknown';
            $avatarColors = ['#7c6fe0','#f5a623','#2d8cff','#e05c5c','#27ae60','#e67e22','#8e44ad','#16a085'];
            $color      = $student ? $avatarColors[$student->id % count($avatarColors)] : '#aaa';
            $hasImage   = $student && !empty($student->image) && $student->image !== 'no_image';

            $observations = $recs->map(function ($rec) use ($observationMasters) {
                $obsIds  = array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
                $obsData = collect($obsIds)->map(fn($id) => $observationMasters->get($id))->filter()->values();
                $first   = $obsData->first();

                return [
                    'obs_names'  => $obsData->pluck('name')->implode(', '),
                    'icon_url'   => ($first && $first->icon) ? asset('observations/' . $first->icon) : null,
                    'short_note' => $rec->short_note,
                    'image_url'  => $rec->image ? asset('storage/' . $rec->image) : null,
                    'created_at' => $rec->created_at ? $rec->created_at->format('j M Y, g:i a') : '',
                ];
            })->values();

            return [
                'student_name'  => $student->name ?? 'Unknown',
                'first_name'    => $firstName,
                'initial'       => strtoupper(substr($firstName, 0, 1)),
                'avatar_color'  => $color,
                'image_url'     => $hasImage ? asset('tenants/' . $student->image) : null,
                'observations'  => $observations,
            ];
        })->values();

        $sessionReport = TrainerSessionReport::where('session_id', $sessionId)
            ->where('status', 1)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($sessionDate, fn ($q) => $q->where(function ($qq) use ($sessionDate) {
                $qq->where('session_date', $sessionDate)->orWhereNull('session_date');
            }))
            ->orderByRaw('session_date IS NULL')
            ->first();

        $schoolName  = null;
        $trainerName = null;

        if ($sessionReport) {
            $school      = School::select(['id', 'school_name'])->find($sessionReport->school_id);
            $schoolName  = $school?->school_name;
            $trainer     = Trainer::select(['id', 'trainer_name'])->find($sessionReport->trainer_id);
            $trainerName = $trainer?->trainer_name;
        }

        return response()->json([
            'session' => [
                'school_name'  => $schoolName,
                'title'        => $session->title,
                'date'         => $sessionDate
                                    ? \Carbon\Carbon::parse($sessionDate)->format('M d, Y')
                                    : \Carbon\Carbon::parse($session->date_time, 'UTC')->timezone('Asia/Singapore')->format('M d, Y · g:i A'),
                'trainer_name' => $trainerName,
            ],
            'students' => $grouped,
        ]);
    }

    private function ensurePartnerSchoolAccess($schoolId): void
    {
        if (!isPartnerUser() || empty($schoolId)) {
            return;
        }

        if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
            abort(403, 'Access denied.');
        }
    }

}
