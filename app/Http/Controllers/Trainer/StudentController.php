<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Models\Grade;
use App\Models\ExternalSession;
use App\Models\Project;
use App\Models\School;
use App\Models\StudentAttendance;
use App\Models\StudentCommunications;
use App\Models\StudentFeedback;
use App\Models\Studentscontent;
use App\Models\Students;
use App\Models\Submission;
use App\Models\Trainer;
use App\Models\TrainerAllocation;
use App\Models\TrainerAllocationNew;
use App\Models\StudentSkills;
use App\Models\StudentMindset;
use App\Helpers\QuizHelper;
use App\Models\StudentMindsetData;
use App\Models\Observation;
use App\Models\StudentObservations;
use App\Models\StudentCertificates;
use App\Models\StudentGrade;
use App\Helpers\StudentRewardPointsHelper;
use App\Helpers\StudentObservationHelper;
use App\Models\SchoolAcademicYear;
use App\Services\StudentProgressService;
use App\Helpers\SimpleZipBuilder;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PDF;
use App\Jobs\SendStudentCertificateEmail;
use App\Jobs\SendTrainerBulkCertificateEmail;

class StudentController extends Controller
{
    public function generateObservationReport(Request $request, $studentId)
    {
        try {
            $student  = Students::findOrFail($studentId);
            $fromDate = $request->input('from_date');
            $toDate   = $request->input('to_date');

            $pdfBinary = $this->buildObservationReportPdfBinary($student, $fromDate, $toDate);

            $slug     = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($student->name)));
            $filename = $slug . '-observation-report-' . now()->format('Y-m-d') . '.pdf';

            return response($pdfBinary, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            \Log::error('ObsReport: failed', ['error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile()]);
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generates every requested student's observation report in a single request and
     * returns either the lone PDF or a ZIP of them all — previously the client fetched
     * one PDF per selected student individually, which meant selecting hundreds/
     * thousands of students fired that many AJAX calls.
     */
    public function generateObservationReports(Request $request)
    {
        $studentIds = array_filter(array_map('intval', (array) $request->input('student_ids', [])));
        $fromDate   = $request->input('from_date');
        $toDate     = $request->input('to_date');

        if (empty($studentIds)) {
            return response()->json(['error' => 'No students selected.'], 422);
        }

        $students = Students::whereIn('id', $studentIds)->get();

        $pdfs = [];
        foreach ($students as $student) {
            try {
                $binary = $this->buildObservationReportPdfBinary($student, $fromDate, $toDate);
            } catch (\Exception $e) {
                \Log::error('ObsReport: bulk failed for student', [
                    'student_id' => $student->id, 'error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(),
                ]);
                continue;
            }

            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($student->name))) ?: 'student';
            $pdfs[] = ['filename' => $slug . '-observation-report.pdf', 'binary' => $binary];
        }

        if (empty($pdfs)) {
            return response()->json(['error' => 'Failed to generate any reports.'], 500);
        }

        if (count($pdfs) === 1) {
            return response($pdfs[0]['binary'], 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $pdfs[0]['filename'] . '"',
            ]);
        }

        $zipBinary   = SimpleZipBuilder::build(SimpleZipBuilder::dedupeNames($pdfs));
        $zipFilename = 'observation-reports-' . now()->format('Y-m-d') . '.zip';

        return response($zipBinary, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFilename . '"',
        ]);
    }

    /**
     * Builds one student's observation report PDF binary. Shared by the single-student
     * download route and the bulk multi-student route above.
     */
    private function buildObservationReportPdfBinary($student, $fromDate, $toDate)
    {
        $studentId = $student->id;

        \Log::info('ObsReport: start', ['student_id' => $studentId, 'from' => $fromDate, 'to' => $toDate]);

        $query = StudentObservations::where('student_id', $studentId)
                ->whereNotNull('external_session_id')
                ->whereNotNull('short_note')
                ->where('short_note', '!=', '');

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
            \Log::info('ObsReport: records fetched', ['count' => $records->count()]);

            // Resolve all observation IDs (handle possible comma-separated values)
            $allObsIds = $records->flatMap(function ($rec) {
                return array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
            })->unique()->toArray();

            $observations = Observation::whereIn('id', $allObsIds)->get()->keyBy('id');

            // Build full obsData with category and obs_id
            $obsData = $records->map(function ($rec) use ($observations) {
                $ids = array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
                $obs = collect($ids)->map(fn ($id) => $observations->get($id))->filter()->first();

                $iconPath = $obs ? public_path('observations/' . $obs->icon) : null;
                $imgPath  = $rec->image ? public_path('storage/' . $rec->image) : null;

                return [
                    'obs_id'     => $obs ? $obs->id : null,
                    'name'       => $obs ? $obs->name : 'Observation',
                    'category'   => $obs ? $obs->category : null,
                    'icon_data'  => ($iconPath && file_exists($iconPath))
                                        ? 'data:' . mime_content_type($iconPath) . ';base64,' . base64_encode(file_get_contents($iconPath))
                                        : null,
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

            // Photos: max 4 evidence images, randomly shuffled
            $photos = $obsData->filter(fn ($r) => !empty($r['image_data']))
                ->pluck('image_data')->shuffle()->take(4)->values();

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

            // AI summary
            $firstName = explode(' ', trim($student->name))[0];
            $aiSummary = null;
            if ($obsData->isNotEmpty()) {
                \Log::info('ObsReport: calling AI', ['obs_count' => $obsData->count()]);
                $service   = app(\App\Services\OpenAIService::class);
                $aiSummary = $service->generateObservationSummary($firstName, $obsData->toArray());
                \Log::info('ObsReport: AI done', ['has_summary' => !is_null($aiSummary)]);
            }

            // Logo
            $logoPath = public_path('asset/images/logo.png');
            $logoData = file_exists($logoPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                : null;

            // Student photo
            $studentPhotoData = null;
            if (!empty($student->image) && $student->image !== 'no_image') {
                $photoPath = public_path('tenants/' . $student->image);
                if (file_exists($photoPath)) {
                    $mime = mime_content_type($photoPath);
                    $studentPhotoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($photoPath));
                }
            }

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

            // Trainer name
            $trainer     = Trainer::find(Session::get('trainer_id'));
            $trainerName = $trainer?->trainer_name ?? '';

            // Duration display
            $durationFrom = $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/y') : '';
            $durationTo   = $toDate   ? \Carbon\Carbon::parse($toDate)->format('d/m/y')   : '';

            \Log::info('ObsReport: rendering PDF');

            $pdf = PDF::loadView('trainer.student.observation_report_pdf', compact(
                'student', 'firstName', 'obsData', 'aiSummary', 'logoData', 'fromDate', 'toDate',
                'photos', 'momentGroups', 'skillsDemonstrated', 'mindsetsDemonstrated',
                'studentPhotoData', 'gradeName', 'trainerName', 'durationFrom', 'durationTo'
            ))->setPaper('A4', 'portrait');

        \Log::info('ObsReport: PDF rendered', ['student_id' => $studentId]);

        return $pdf->output();
    }

    public function student_list()
    {
        $trainer_id = Session::get('trainer_id');

        $school_list = TrainerAllocationNew::select(['id', 'school_id', 'trainer_id'])->with(['getSchool' => function($query) {
            $query->select('id','school_name');
        }])->where('trainer_id', $trainer_id)->groupBy('school_id')->get()->toArray();
        
        $grades = Grade::all();
        $filteredLevels = [];
        $filtered_collection = $grades->filter(function ($item) use (&$filteredLevels) {
            if($item->is_primary == 1) {
                $filteredLevels['primary'][$item->id] = $item->toArray();        
            } else {
                $filteredLevels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();

        $gradeList = StudentGrade::select('id', 'name')->get()->toArray();

        $batch_list = TrainerAllocationNew::select(['id', 'school_id', 'school_batch_id'])->with(['getBatch' => function($query) {
            $query->select('id', 'school_id', 'batch_name');
        }, 'getSchool' => function($query) {
            $query->select('id', 'school_name');
        }])->where('trainer_id', $trainer_id)->get()->toArray();

        // Resolve the primary/default grade dynamically instead of a
        // hardcoded unique_code — see the matching fix in
        // Student\StudentAccountController::index().
        $gradeData = Grade::select('id')->where('is_primary', 1)->orderBy('display_order_id')->first()
            ?: Grade::select('id')->orderBy('display_order_id')->first();
        $currentGradeId = $gradeData ? $gradeData->id : null;

        // Build per-school data (academic years scoped to each school)
        $schoolsData = TrainerAllocationNew::select('school_id')
            ->with(['getSchool' => fn($q) => $q->select('id', 'school_name')])
            ->where('trainer_id', $trainer_id)
            ->groupBy('school_id')
            ->get()
            ->map(function ($allocation) {
                $schoolId   = $allocation->school_id;
                $schoolName = $allocation->getSchool->school_name ?? '';
                $yearRecords = SchoolAcademicYear::where('school_id', $schoolId)
                    ->orderByDesc('start_date')->get();
                $academicYears = $yearRecords->map(fn($yr) => [
                    'id'    => $yr->id,
                    'label' => 'AY ' . Carbon::parse($yr->start_date)->format('Y') . ' - ' . Carbon::parse($yr->end_date)->format('y'),
                ])->values()->toArray();
                return [
                    'id'                   => $schoolId,
                    'name'                 => $schoolName,
                    'academicYears'        => $academicYears,
                    'selectedAcademicYear' => !empty($academicYears) ? (string) $academicYears[0]['id'] : 'past',
                ];
            })->values()->toArray();

        return view('trainer.student.student_list', compact(
            'school_list', 'filteredLevels', 'gradeList', 'batch_list',
            'currentGradeId', 'schoolsData'
        ));
    }

    public function leaderboard()
    {
        $trainer_id = Session::get('trainer_id');

        $school_list = TrainerAllocationNew::select(['id', 'school_id', 'trainer_id'])->with(['getSchool' => function($query) {
            $query->select('id','school_name');
        }])->where('trainer_id', $trainer_id)->groupBy('school_id')->get()->toArray();

        $grades = Grade::all();
        $filteredLevels = [];
        $grades->filter(function ($item) use (&$filteredLevels) {
            if($item->is_primary == 1) {
                $filteredLevels['primary'][$item->id] = $item->toArray();
            } else {
                $filteredLevels['add-ons'][$item->id] = $item->toArray();
            }
        })->values();

        $gradeList = StudentGrade::select('id', 'name')->get()->toArray();

        $batch_list = TrainerAllocationNew::select(['id', 'school_id', 'school_batch_id'])->with(['getBatch' => function($query) {
            $query->select('id', 'school_id', 'batch_name');
        }, 'getSchool' => function($query) {
            $query->select('id', 'school_name');
        }])->where('trainer_id', $trainer_id)->get()->toArray();

        // Resolve the primary/default grade dynamically instead of a
        // hardcoded unique_code — see the matching fix in
        // Student\StudentAccountController::index().
        $gradeData = Grade::select('id')->where('is_primary', 1)->orderBy('display_order_id')->first()
            ?: Grade::select('id')->orderBy('display_order_id')->first();
        $currentGradeId = $gradeData ? $gradeData->id : null;

        // Academic years scoped per school, keyed by school_id for client-side lookup
        $academicYearsBySchool = TrainerAllocationNew::select('school_id')
            ->where('trainer_id', $trainer_id)
            ->groupBy('school_id')
            ->get()
            ->mapWithKeys(function ($allocation) {
                $schoolId = $allocation->school_id;
                $academicYears = SchoolAcademicYear::where('school_id', $schoolId)
                    ->orderByDesc('start_date')->get()
                    ->map(fn($yr) => [
                        'id'    => $yr->id,
                        'label' => 'AY ' . Carbon::parse($yr->start_date)->format('Y') . ' - ' . Carbon::parse($yr->end_date)->format('y'),
                    ])->values();
                return [$schoolId => $academicYears];
            });

        return view('trainer.student.leaderboard', compact(
            'school_list', 'filteredLevels', 'gradeList', 'batch_list',
            'currentGradeId', 'academicYearsBySchool'
        ));
    }

    public function student_list_datatable(Request $request)
    {
        $trainer_id = Session::get('trainer_id');

        $batch_list = TrainerAllocationNew::select(['id', 'school_batch_id'])->where('trainer_id', $trainer_id)->get();
        
        $batch_ids = $batch_list->pluck('school_batch_id');
                
        if (($request->school_id != '') || ($request->grade_id != '') || ($request->assigned_grade != '') || ($request->school_batch_id != '')) {
            $data = Students::select(['id','user_id', 'school_id', 'grade_id', 'student_grade_id', 'school_batch_id'])->with([
                'school' => function ($query) {
                    $query->select('id', 'school_name');
                },
                'getAssignedGrade' => function ($query) {
                    $query->select('id', 'name');
                },
                'getAssignedBatch' => function ($query) {
                    $query->select('id', 'school_id', 'batch_name');
                },
                'stdUser',
            ]);
            if($request->school_id != ''){
                $data = $data->where('school_id', $request->school_id);
            }
            if(isset($request->grade_id)){
                $data = $data->whereRaw('FIND_IN_SET('.$request->grade_id.',grade_id)');
            }
            if($request->assigned_grade != ''){
                $data = $data->where('student_grade_id', $request->assigned_grade);
            }
            if($request->school_batch_id != ''){
                $data = $data->where('school_batch_id', $request->school_batch_id);
            }
            $data = $data->whereIn('school_batch_id', $batch_ids);
            $data = $data->get()->toArray();
        } else {
            $data = Students::select(['id','user_id', 'school_id', 'grade_id', 'student_grade_id', 'school_batch_id'])->with([
                'school' => function ($query) {
                    $query->select('id', 'school_name');
                },
                'getAssignedGrade' => function ($query) {
                    $query->select('id', 'name');
                },
                'getAssignedBatch' => function ($query) {
                    $query->select('id', 'school_id', 'batch_name');
                },
                'stdUser',
            ])
                ->whereIn('school_batch_id', $batch_ids)
                ->get()
                ->toArray();
        }
        if (request()->ajax()) {
            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('grade_id',function($row){
                    $lavel = Grade::whereIn('id',explode(',',$row['grade_id']))->get();
                    $lavelname = [];
                    foreach($lavel as $r){
                        $lavelname[] = $r->grade;
                    }
                    return implode(',',$lavelname);
                })
                // ->addColumn('age', function ($row) {
                //     if (isset($row['std_user'])) {
                //         return Carbon::parse($row['std_user']['date_of_birth'])->age;
                //     } else {
                //         return '-';
                //     }
                // })
                ->addColumn('action', function ($row) {
                    $gradeId = explode(',', $row['grade_id'])[0] ?? '';
                    return '<button class="btn iconBtn btn-primary btn-sm view-student-progress"'
                        . ' data-id="' . $row['id'] . '"'
                        . ' data-grade="' . $gradeId . '"'
                        . ' data-school="' . $row['school_id'] . '">'
                        . '<i class="material-icons">visibility</i></button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return null;
    }

    // student_view is no longer used from the action button.
    // Progress report is now shown inline below the student list table.
    // public function student_view($id)
    // {
    //     $trainer_id = Session::get('trainer_id');
    //     $grades = Grade::all();
    //     $filteredLevels = [];
    //     $filtered_collection = $grades->filter(function ($item) use (&$filteredLevels) {
    //         if($item->is_primary == 1) {
    //             $filteredLevels['primary'][$item->id] = $item->toArray();
    //         } else {
    //             $filteredLevels['add-ons'][$item->id] = $item->toArray();
    //         }
    //     })->values();
    //     $skills = StudentSkills::all()->toArray();
    //     $mindset = StudentMindset::all()->toArray();
    //     $batch_list = TrainerAllocationNew::select(['id', 'school_batch_id'])->where('trainer_id', $trainer_id)->get();
    //     $batch_ids = $batch_list->pluck('school_batch_id');
    //     if(!$batch_ids->count()) {
    //         return redirect()->route('trainer.student_list');
    //     }
    //     $students = Students::with(['school' => function($query) {
    //         $query->select(['id','tenant_id']);
    //     }])->whereIn('school_batch_id', $batch_ids)->get()->toArray();
    //     $one_student = Students::find($id)->toArray();
    //     return view('trainer.student.student_view', compact('students', 'one_student', 'filteredLevels', 'skills', 'mindset'));
    // }

    public function getProgressByGrade(Request $request)
    {
        $grade_id = $request->grade_id;
        $trainer_id = Session::get('trainer_id');

        $batch_ids = TrainerAllocationNew::select('school_batch_id')
            ->where('trainer_id', $trainer_id)
            ->pluck('school_batch_id');

        $data['students'] = [];
        $students = Students::select('id', 'user_id', 'image')->with([
            'user' => function ($query) { $query->select('id', 'name'); },
        ])->whereIn('school_batch_id', $batch_ids)
            ->whereRaw('find_in_set(?, grade_id)', [$grade_id])
            ->get();

        if ($students->count()) {
            $data['students'] = $students->toArray();
        }
        $data['grade_name'] = Grade::select('grade')->find($grade_id);

        echo json_encode($data);
    }

    public function generateLeaderBoard(Request $request)
    {
        $trainer_id = Session::get('trainer_id');
        $academicYearFilter = $request->get('academic_year');
        $gradeId       = (int) $request->get('grade_id');
        $assignedGrade = $request->get('assigned_grade');
        $batchId       = $request->get('school_batch_id');
        $month         = $request->get('month');
        $schoolId      = (int) $request->get('school_id');

        $with = ['user' => function ($q) { $q->select('id', 'name'); }];

        if (!empty($schoolId)) {
            // Per-school leaderboard: show all students from the school (school-wide comparison)
            $query = Students::select('id', 'user_id', 'image')->with($with)
                ->where('school_id', $schoolId);
        } else {
            // No school filter: restrict to this trainer's allocated batch students
            $batch_ids = TrainerAllocationNew::select('school_batch_id')
                ->where('trainer_id', $trainer_id)
                ->pluck('school_batch_id');
            $query = Students::select('id', 'user_id', 'image')->with($with)
                ->whereIn('school_batch_id', $batch_ids);
        }

        if (!empty($gradeId)) {
            $query->whereRaw('find_in_set(?, grade_id)', [$gradeId]);
        }

        if (!empty($assignedGrade)) {
            $query->where('student_grade_id', $assignedGrade);
        }

        if (!empty($batchId)) {
            $query->where('school_batch_id', $batchId);
        }

        $students = $query->get();

        if ($students->count()) {
            foreach ($students as $student) {
                $reward_points = StudentRewardPointsHelper::getRewardPoints($student->id, $academicYearFilter, $month);
                $student->tot_reward_points = array_sum($reward_points);
            }
            $students = $students->sortByDesc('tot_reward_points');
        }

        echo json_encode(array_values($students->toArray()));
    }

    public function getRewardPointDetails(Request $request)
    {
        $stud_id = (int) $request->stud_id;
        $academicYearFilter = $request->get('academic_year');
        $month = $request->get('month');
        if ($stud_id) {
            return json_encode(StudentRewardPointsHelper::getRewardPoints($stud_id, $academicYearFilter, $month));
        }
        return false;
    }

    public function getStudentObservations(Request $request)
    {
        return StudentObservationHelper::getByStudentAndGrade(
            (int) $request->studentId,
            (int) $request->gradeId
        );
    }

    public function studentProgressInfo(Request $request)
    {
        return response()->json(
            StudentProgressService::getData(
                (int) $request->studentId,
                (int) $request->gradeId,
                $request->get('academic_year')
            )
        );
    }

    public function studentByLevel(Request $request)
    {   
        $gradeId = $request->gradeId;
        
        $students = $one_student = '';
        
        $trainer_id = Session::get('trainer_id');
        
        $batch_list = TrainerAllocationNew::select(['id', 'school_batch_id'])->where('trainer_id', $trainer_id)->get();
        
        $batch_ids = $batch_list->pluck('school_batch_id');
        
        if(!empty($gradeId)) {
            $students = Students::whereIn('school_batch_id', $batch_ids)->whereRaw('FIND_IN_SET('.$gradeId.',grade_id)')->get()->toArray();
        } else {
            $students = Students::whereIn('school_batch_id', $batch_ids)->get()->toArray();
        }

        if(!empty($students)) {
            $one_student = Students::find($students[0]['id'])->toArray();
        }    
        
        return response()->json(['success' => [
            'students' => $students,
            'one_student' => $one_student,
        ]]); 
    }
    
    public function student_feedback(Request $req)
    {
        $student_id = $req->studentId;        
        $levelId = $req->levelId;

        $observationsData = StudentObservations::with('studMindsetData')->where('student_id', $student_id)->get()->toArray();
        if(!empty($observationsData)) {
            foreach($observationsData as $key => $observation) {
                $observationsData[$key]['stream_list'] = Stream::where('agegroup_id', $observation['grade_id'])->get()->toArray();
                $observationsData[$key]['session_list'] = Studentscontent::where(['ageGroup_id' => $observation['grade_id'], 'stream_id' => $observation['stream_id'], 'is_publish' => 1])->get()->toArray();
            }
        }
        $response['observationData'] = $observationsData;
        
        $certificationData = DB::table('student_certificates as cert')
                    ->Join('grades as grade', 'cert.grade_id', '=', 'grade.id')
                    ->where('cert.student_id',$student_id)
                    ->select("grade.grade as level_name", DB::raw("DATE_FORMAT(cert.created_at, '%d-%m-%Y') as released_date"))->get()->toArray();
        $response['certificationData'] = $certificationData;

        return response()->json($response);
    }

    public function student_feedback_submit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'level' => 'required',
            'feedback' => 'required',
            'grade' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        } else {
            $student = Students::find($req->student_id);
            $feedback_check = StudentFeedback::where('student_id', $student->id)->where('level', $req->level)->get();
            
            $data['student_id'] = $req->student_id;
            $data['year'] = now()->year;
            $data['level'] = $req->level;
            $data['feedback'] = $req->feedback;
            $data['grade'] = $req->grade;
            if ($req->assesment) {
                $data['assessment'] = 1;
            } else {
                $data['assessment'] = 0;
            }
            $data['eq'] = $req->eq;
            $data['iq'] = $req->iq;
            $data['cq'] = $req->cq;
            $data['aq'] = $req->aq;
            $data['sq'] = $req->sq;
            $data['enq'] = $req->enq;
            $data['term'] = $req->term;
                
            if ($feedback_check->count()) {
                StudentFeedback::find($feedback_check[0]->id)->update($data);
                $responseMsg = 'Student Assessment Updated Successfully!';
            } else {
                StudentFeedback::insert($data);
                $responseMsg = 'Student Assessment Inserted Successfully!';
            }
            return response()->json(['success' => $responseMsg]);
        }
    }

    public function project_approve(Request $req)
    {
        $student = Students::with([
            'getproject' => function ($query) {
                $query->where('project_status', 0);
            },
        ])->find($req->student_id)->toArray();

        echo json_encode($student);
    }

    public function approve_project_submit(Request $req)
    {
        $student_id = $req->project_approve;

        if ($student_id) {
            foreach ($student_id as $students_id) {
                $data['project_status'] = 1;
                Project::where('id', $students_id)->update($data);

                return response()->json(['success' => 'Successfully update']);
            }
        }
    }

    public function reject_project_submit(Request $req)
    {
        $student_id = $req->project_approve;
        if ($student_id) {
            foreach ($student_id as $students_id) {
                $data['project_status'] = 2;
                Project::where('id', $students_id)->update($data);

                return response()->json(['success' => 'Successfully update']);
            }
        }
    }

    public function student_attendence(Request $request)
    {
        $trainer = Trainer::where('user_id', Session::get('user_id'))->first()->toArray();
        $school_id = !empty($request->school_id) ? $request->school_id : '';
        $grade_id = !empty($request->grade_id) ? $request->grade_id : '';

        $schools = TrainerAllocation::with('getSchool')->where('trainer_id', $trainer['id'])->groupBy('school_id')->get()->toArray();
        
        $grades = Grade::get()->toArray();
        
        return view('trainer.student.student_attendence', compact('schools', 'grades', 'school_id', 'grade_id'));
    }

    public function getStudent(Request $request)
    {
        $school_id = $request->school_id;
        $grade_id = $request->grade_id;
        $data['students'] = Students::where('school_id', $school_id)->where('grade_id', $grade_id)->get()->toArray();
        $attendance = [];
        foreach ($data['students'] as $student) {
            $attendance[] = StudentAttendance::where('student_id', $student['id'])->get()->toArray();
        }
        $data['attendance'] = $attendance;
        echo json_encode($data);
    }

    public function saveAttendance(Request $request)
    {
        $school_id = !empty($request->school_id) ? $request->school_id : '';
        $grade_id = !empty($request->grade_id) ? $request->grade_id : '';

        if(!empty($request->dates)) {
            foreach ($request->dates as $date => $rec) {
                foreach ($rec as $student_id => $val) {
                    $atDate = Carbon::parse($date);
                    if ($atDate->lte(now())) {
                        StudentAttendance::updateOrCreate([
                            'trainer_id' => Session::get('user_id'),
                            'student_id' => $student_id,
                            'date' => $date,
                        ], ['status'=> $val]);
                    } else {
                        StudentAttendance::where([
                            'trainer_id' => Session::get('user_id'),
                            'student_id' => $student_id,
                            'date' => $date,
                        ])->delete();
                    }
                }
            }
        }

        return redirect()->route('trainer.student_attendence', compact('school_id', 'grade_id'))->with('success', 'Attendances saved Successfully');
    }


    public function getStream(Request $request) {
        return Stream::where('agegroup_id', $request->agegroupId)->get();
    }

    public function getSession(Request $request) {
        return Studentscontent::where(['ageGroup_id' => $request->agegroupId, 'stream_id' => $request->sessionId, 'is_publish' => 1])->get();
    }

    public function observationStore(Request $request) {
        $request->validate([
            'student_id' => 'required',
            'level' => 'required',
            'stream' => 'required',
        ]);
        
        $trainer_id = Session::get('trainer_id');
        $student_id = $request->student_id;
        $grade_id = $request->level;
        if(!empty($grade_id)) {
            $mindsetList = StudentMindset::all();
            $observationIdList = $request->observationIdList;
            foreach($grade_id as $key => $value) {
                $observation_action = 'add';
                if(!empty($observationIdList) && array_key_exists($key, array_keys($observationIdList))) {
                    $observation_action = 'edit';
                    $observationsData = StudentObservations::find($observationIdList[$key]);
                } else {
                    $observationsData = new StudentObservations;
                }
                $observationsData->student_id = $student_id;
                $observationsData->trainer_id = $trainer_id;
                $observationsData->grade_id = $value;
                $observationsData->stream_id = $request->stream[$key];
                $observationsData->session_id = $request->session[$key];

                $session_image = $request->session_image;
                if(isset($request->session_image) && array_key_exists($key, $session_image) && $request->session_image[$key]) {
                    if($observation_action == 'edit' && !empty($observationsData->session_image)) {
                        $destinationPath = public_path('/image/student/observation/');
                        if (File::exists($destinationPath . $observationsData->session_image)) {
                            File::delete($destinationPath . $observationsData->session_image);
                        }
                    }
                    $image = $request->session_image[$key];
                    $extension = strtolower($image->getClientOriginalExtension());
                    if(!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                        return back()->withErrors(["session_image" => "Only JPG, JPEG or PNG files are allowed."])->withInput();
                    }
                    $image_name = Str::random(10); //unique name generate every time
                    $ext = strtolower($image->getClientOriginalExtension());
                    $image_full_name = 'image_' . $image_name . '.' . $ext;
                    $upload_path = 'image/student/observation/';
                    $image->move($upload_path, $image_full_name);
                    $observationsData->session_image = $image_full_name;
                }

                $observationsData->remarkable_note = $request->remarkable_note[$key];
                
                if(!empty(array_filter($request->skills[$key]))) {
                    $observationsData->skill_id = implode("," , array_filter($request->skills[$key]));
                } else if($observation_action == 'edit') {
                    $observationsData->skill_id = null;
                }

                // $observationsData->achievement_points = $request->achievement_points[$key];
                
                $observationsData->save();
                
                $observation_id = $observationsData->id;
                
                if($mindsetList->count()) {
                    $mVal = 'mindset'.$key;
                    StudentMindsetData::where('student_observation_id',$observation_id)->delete();
                    foreach($mindsetList as $mkey => $mindset) {
                        $student_mindset_data = new StudentMindsetData;
                        $student_mindset_data->student_observation_id = $observation_id;
                        $student_mindset_data->mindset_id = $mindset->id;
                        $student_mindset_data->value = $request->$mVal[$mkey];
                        $student_mindset_data->save();
                    }
                }

                /* START - Store Faculty Recognition Reward Points */
                // $chk_reward = StudentRewardPointsHelper::checkRewardTypeExist($student_id, 'external_reward', $observation_id);
                // if(!$chk_reward->count()) {
                //     if($observationsData->achievement_points != '') {
                //         StudentRewardPointsHelper::storeRewardPoints([
                //             'student_id' => $student_id,
                //             'reward_type' => 'external_reward',
                //             'item_id' => $observation_id,
                //             'reward_points' => $observationsData->achievement_points,
                //         ]);
                //     }
                // } else {
                //     if($observationsData->achievement_points != '') {
                //         StudentRewardPointsHelper::updateRewardPoints([
                //             'id' => $chk_reward[0]->id,
                //             'reward_points' => $observationsData->achievement_points,
                //         ]);
                //     } else {
                //         StudentRewardPointsHelper::deleteRewardPoints([
                //             'id' => $chk_reward[0]->id,
                //         ]);
                //     }
                // }
                /* END - Store Faculty Recognition Reward Points */
            }
        }
        return redirect()->route('trainer.student_view',$student_id)->with('message', 'Observation Data Added Successfully!');
    }

    public function deleteSessionImage(Request $request) { 
        $observationId = $request->observationId;
        $observation_data = StudentObservations::find($observationId);
        if ($observation_data && !empty($observation_data->session_image)) {
            $destinationPath = public_path('/image/student/observation/');
            if (File::exists($destinationPath . $observation_data->session_image)) {
                File::delete($destinationPath . $observation_data->session_image);
            }
            $observation_data->session_image = null;
            $observation_data->save();
        }
        return true;
    }

    public function observationDelete(Request $request) {  
        $observationId = $request->observationId;
        $observation_data = StudentObservations::find($observationId);
        if ($observation_data) {
            if(!empty($observation_data->session_image)) {
                $destinationPath = public_path('/image/student/observation/');
                if (File::exists($destinationPath . $observation_data->session_image)) {
                    File::delete($destinationPath . $observation_data->session_image);
                }
            }
            StudentMindsetData::where('student_observation_id',  $observationId)->delete();
            $observation_data->delete();
        }
        return true;
    }
    public function releaseCertificate(Request $request) {
        $student_id = $request->c_stud_id;
        $grade_id = $request->cLevel;

        \Log::info('Certificate Release: Starting individual certificate release', [
            'student_id' => $student_id,
            'grade_id' => $grade_id,
            'trainer_id' => Session::get('trainer_id')
        ]);

        if(!empty($student_id) && !empty($grade_id)) {
            $chk_released = StudentCertificates::where([
                'student_id' => $student_id,
                'grade_id' => $grade_id
            ])->get();
            
            if(!$chk_released->count()) {
                \Log::info('Certificate Release: Creating new certificate', [
                    'student_id' => $student_id,
                    'grade_id' => $grade_id
                ]);

                $certificate = new StudentCertificates;
                $certificate->student_id = $student_id;
                $certificate->grade_id = $grade_id;
                $certificate->trainer_id = Session::get('trainer_id');
                $certificate->released_by = 'Trainer';
                $certificate->unique_id = strtoupper(Str::random(10));
                $certificate->issue_date = now()->toDateString();
                $certificate->download_token = Str::random(64);
                $certificate->token_expires_at = now()->addWeek();
                $certificate->save();

                // Generate and store PDF
                $this->generateAndStoreCertificatePDF($certificate);

                \Log::info('Certificate Release: Certificate saved, dispatching email jobs', [
                    'certificate_id' => $certificate->id,
                    'unique_id' => $certificate->unique_id
                ]);

                // Dispatch email jobs to queue
                SendStudentCertificateEmail::dispatch($certificate);
                SendTrainerBulkCertificateEmail::dispatch([$certificate], '', $certificate->grade);

                return redirect()->route('trainer.student_view',$student_id)->with('message', 'Certificate Released Successfully! Email will be sent in background.');
            } else {
                \Log::info('Certificate Release: Certificate already exists', [
                    'student_id' => $student_id,
                    'grade_id' => $grade_id,
                    'existing_count' => $chk_released->count()
                ]);
                $msg = 'Certificate Already Released!';
            }
        } else {
            \Log::warning('Certificate Release: Invalid parameters', [
                'student_id' => $student_id,
                'grade_id' => $grade_id
            ]);
            $msg = 'Error Occurred!';
        }
        return redirect()->route('trainer.student_view',$student_id)->with('error', $msg);
    }

    // public function saveAttendance(Request $request)
    // {
    //     $student_id = $request->student_id;
    //     $class_no = $request->class_no;
    //     $attend_status = $request->attend_status;
    //     $trainer = Trainer::where('user_id', Session::get('user_id'))->first()->toArray();
    //     $trainer_id = $trainer['id'];

    //     $check_attendance = StudentAttendance::where('student_id', $student_id)->where('trainer_id', $trainer_id)->where('class_no', $class_no)->get()->toarray();

    //     if (empty($check_attendance)) {
    //         $attendance = new StudentAttendance;
    //         $attendance->student_id = $student_id;
    //         $attendance->trainer_id = $trainer_id;
    //         $attendance->class_no = $class_no;
    //         $attendance->attend_status = $attend_status;
    //         $attendance->save();

    //         $attend_id = DB::getPdo()->lastInsertId();

    //         $data['attendance'] = StudentAttendance::where('id', $attend_id)->first()->toArray();
    //         $data['status'] = 1;
    //         echo json_encode($data);
    //     } else {
    //         $check_attendance = StudentAttendance::where('student_id', $student_id)->where('trainer_id', $trainer_id)->where('class_no', $class_no)->get();
    //         $check_attendance[0]->attend_status = $attend_status;
    //         $check_attendance[0]->save();
    //         $check_attendance = StudentAttendance::where('student_id', $student_id)->where('trainer_id', $trainer_id)->where('class_no', $class_no)->get()->toArray();
    //     }
    // }

    public function certificateManagement(Request $request)
    {
        $trainer_id = Session::get('trainer_id');

        // Get schools allocated to trainer
        $schools = TrainerAllocationNew::where('trainer_id', $trainer_id)
            ->with('getSchool')
            ->groupBy('school_id')
            ->get()
            ->pluck('getSchool')
            ->filter();

        // Resolve the primary/default grade dynamically (is_primary + lowest
        // display order) instead of a hardcoded unique_code/name, which
        // broke whenever the active database's `grades` catalog didn't
        // contain that exact code or name (see the matching fix in
        // Student\StudentAccountController::index() and
        // StudentService::createStudent()).
        $thinkpreneurGrade = Grade::where('is_publish', 1)->where('is_primary', 1)->orderBy('display_order_id')->first()
            ?: Grade::where('is_publish', 1)->orderBy('display_order_id')->first();

        return view('trainer.certificates.management', compact('schools', 'thinkpreneurGrade'));
    }

    public function getCertificateStudents(Request $request)
    {
        $trainer_id = Session::get('trainer_id');

        // Resolve the primary/default grade dynamically — see the matching
        // comment in certificateManagement() above.
        $thinkpreneurGrade = Grade::where('is_publish', 1)->where('is_primary', 1)->orderBy('display_order_id')->first()
            ?: Grade::where('is_publish', 1)->orderBy('display_order_id')->first();

        if (!$thinkpreneurGrade) {
            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        // Get students allocated to this trainer
        $batch_list = TrainerAllocationNew::select(['id', 'school_batch_id'])
            ->where('trainer_id', $trainer_id)
            ->get();

        if ($batch_list->isEmpty()) {
            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $batch_ids = $batch_list->pluck('school_batch_id');

        // Base query for students assigned to Thinkpreneur
        $query = Students::with(['school', 'certificates' => function($q) use ($trainer_id, $thinkpreneurGrade) {
            $q->where('trainer_id', $trainer_id)
              ->where('grade_id', $thinkpreneurGrade->id);
        }])
        ->whereRaw("FIND_IN_SET(?, grade_id)", [$thinkpreneurGrade->id])
        ->whereIn('school_batch_id', $batch_ids);

        // Total records before filtering
        $recordsTotal = $query->count();

        // Apply filters
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Global search from DataTables (this handles the name search)
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                  ->orWhereHas('school', function($sq) use ($searchValue) {
                      $sq->where('school_name', 'like', '%' . $searchValue . '%');
                  });
            });
        }

        // Records after filtering
        $recordsFiltered = $query->count();

        // Apply default ordering (no sorting from DataTables since it's disabled)
        $query->orderBy('name', 'asc');

        // Apply pagination
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $query->skip($start)->take($length);

        $students = $query->get();

        $data = [];
        foreach ($students as $student) {
            $cert = $student->certificates->first();

            $data[] = [
                'checkbox' => !$cert ? '<input type="checkbox" name="student_ids[]" value="' . $student->id . '" class="student-checkbox">' : '',
                'name' => $student->name,
                'school' => $student->school->school_name ?? 'N/A',
                'certificate_status' => $cert ?
                    '<span class="badge badge-success">Released</span><br><small>Unique ID: ' . $cert->unique_id . '</small><br><small>Issue Date: ' . $cert->issue_date . '</small>' :
                    '<span class="badge badge-warning">Not Released</span>',
                'actions' => $cert ?
                    '<button type="button" class="btn btn-sm btn-info download-cert" data-token="' . $cert->download_token . '"><i class="fas fa-download"></i> Download</button>' :
                    '<button type="button" class="btn btn-sm btn-primary release-single" data-student-id="' . $student->id . '" data-grade-id="' . $thinkpreneurGrade->id . '"><i class="fas fa-certificate"></i> Release</button>'
            ];
        }

        return response()->json([
            'draw' => intval($request->get('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function bulkReleaseCertificates(Request $request)
    {
        $trainer_id = Session::get('trainer_id');
        $student_ids = $request->input('student_ids', []);
        $grade_id = $request->input('grade_id');
        $quote = $request->input('quote');

        \Log::info('Bulk Certificate Release: Starting bulk release process', [
            'trainer_id' => $trainer_id,
            'student_ids' => $student_ids,
            'grade_id' => $grade_id,
            'quote' => $quote,
            'student_count' => count($student_ids)
        ]);

        if (empty($student_ids) || empty($grade_id)) {
            \Log::warning('Bulk Certificate Release: Validation failed - missing parameters', [
                'student_ids' => $student_ids,
                'grade_id' => $grade_id
            ]);
            return response()->json(['success' => false, 'message' => 'Please select students and grade.']);
        }

        $released_certificates = [];
        $released_count = 0;

        foreach ($student_ids as $student_id) {
            \Log::info('Bulk Certificate Release: Processing student', [
                'student_id' => $student_id,
                'grade_id' => $grade_id
            ]);

            // Check if certificate already released
            $existing = StudentCertificates::where('student_id', $student_id)
                ->where('grade_id', $grade_id)
                ->first();

            if (!$existing) {
                \Log::info('Bulk Certificate Release: Creating certificate for student', [
                    'student_id' => $student_id
                ]);

                $certificate = new StudentCertificates();
                $certificate->student_id = $student_id;
                $certificate->grade_id = $grade_id;
                $certificate->trainer_id = $trainer_id;
                $certificate->released_by = 'Trainer';
                $certificate->unique_id = strtoupper(Str::random(10));
                $certificate->issue_date = now()->toDateString();
                $certificate->download_token = Str::random(64);
                $certificate->token_expires_at = now()->addWeek();
                $certificate->save();

                // Generate and store PDF
                $this->generateAndStoreCertificatePDF($certificate, $quote);

                $released_certificates[] = $certificate;

                \Log::info('Bulk Certificate Release: Certificate created', [
                    'certificate_id' => $certificate->id,
                    'unique_id' => $certificate->unique_id,
                    'student_id' => $student_id
                ]);

                $released_count++;
            } else {
                \Log::info('Bulk Certificate Release: Certificate already exists for student', [
                    'student_id' => $student_id,
                    'existing_certificate_id' => $existing->id
                ]);
            }
        }

        // Send emails if any certificates were released
        if (!empty($released_certificates)) {
            \Log::info('Bulk Certificate Release: Dispatching email jobs', [
                'released_count' => count($released_certificates),
                'trainer_id' => $trainer_id
            ]);

            // Dispatch individual emails to each student
            foreach ($released_certificates as $certificate) {
                SendStudentCertificateEmail::dispatch($certificate);
            }

            // Create bulk download token (concatenated certificate IDs)
            $certificate_ids = collect($released_certificates)->pluck('id')->toArray();
            $bulk_token = 'BULK-' . strtoupper(Str::random(32)) . '-' . implode('-', $certificate_ids);

            // Generate bulk download URL
            $bulkDownloadUrl = route('trainer.certificates.bulkDownload', [
                'token' => $bulk_token
            ]);

            // Dispatch bulk email to trainer
            SendTrainerBulkCertificateEmail::dispatch($released_certificates, $bulkDownloadUrl, $released_certificates[0]->grade);
        }

        \Log::info('Bulk Certificate Release: Completed', [
            'total_processed' => count($student_ids),
            'actually_released' => $released_count
        ]);

        return response()->json([
            'success' => true,
            'message' => "Certificates released for {$released_count} students."
        ]);
    }

    private function generateAndStoreCertificatePDF(StudentCertificates $certificate, $quote = null)
    {
        try {
            $student = $certificate->student;
            $grade = $certificate->grade;
            $school = $student ? $student->school : null;
            $schoolLogoPath = asset('asset/images/logo.png');

            if ($school && isset($school->school_logo)) {
                $logoPath = \Storage::disk('tenant_uploads')->path($school->school_logo);
                if (File::exists($logoPath)) {
                    $schoolLogoPath = url('tenants/'.$school->school_logo);
                }
            }

            // Generate PDF
            $pdf = PDF::loadView('certificates.studentcertificate', [
                'student' => $student,
                'grade' => $grade,
                'certificate' => $certificate,
                'custom_quote' => $quote,
                'schoolLogoPath' => $schoolLogoPath
            ]);

            $pdf->setPaper('A4', 'landscape');

            // Create filename
            $filename = $certificate->unique_id . '.pdf';
            $path = 'certificates/' . $filename;

            // Store PDF in public/certificates/
            $fullPath = public_path($path);
            $directory = dirname($fullPath);

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Save PDF to file
            $pdf->save($fullPath);

            // Update certificate record with PDF path
            $certificate->pdf_path = $path;
            $certificate->save();

            \Log::info('Certificate PDF generated and stored', [
                'certificate_id' => $certificate->id,
                'pdf_path' => $path,
                'full_path' => $fullPath
            ]);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Failed to generate certificate PDF', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function downloadCertificate($token)
    {
        $certificate = StudentCertificates::where('download_token', $token)
            ->where('token_expires_at', '>', now())
            ->with(['student', 'grade'])
            ->first();

        // If not found and user is logged in as trainer, allow permanent access
        if (!$certificate && auth()->check()) {
            $trainer = auth()->user()->trainer;
            if ($trainer) {
                $certificate = StudentCertificates::where('download_token', $token)
                    ->where('trainer_id', $trainer->id)
                    ->with(['student', 'grade'])
                    ->first();
            }
        }

        if (!$certificate) {
            abort(404, 'Certificate not found or link expired.');
        }

        $student = $certificate->student;
        $grade = $certificate->grade;
        if (!$student || !$grade) {
            \Log::error('Certificate download failed: missing student or grade', [
                'certificate_id' => $certificate->id,
                'student_id' => $certificate->student_id,
                'grade_id' => $certificate->grade_id
            ]);
            abort(404, 'Certificate data is incomplete.');
        }

        $school = $student ? $student->school : null;
        $schoolLogoPath = asset('asset/images/logo.png');

        if ($school && isset($school->school_logo)) {
            try {
                $logoPath = \Storage::disk('tenant_uploads')->path($school->school_logo);
                if (File::exists($logoPath)) {
                    $schoolLogoPath = url('tenants/'.$school->school_logo);
                }
            } catch (\Exception $e) {
                \Log::warning('Certificate download: unable to resolve school logo path', [
                    'certificate_id' => $certificate->id,
                    'school_logo' => $school->school_logo,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Check if PDF is already generated
        if ($certificate->pdf_path && File::exists(public_path($certificate->pdf_path))) {
            // Serve pre-generated PDF
            $filename = $student->name . '_' . $grade->grade . '_certificate.pdf';
            return response()->file(public_path($certificate->pdf_path), [
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } else {
            // If missing, generate and persist PDF (same logic as release)
            \Log::warning('Pre-generated PDF not found, generating and saving', [
                'certificate_id' => $certificate->id
            ]);
           
            $storedPath = $this->generateAndStoreCertificatePDF($certificate);
            if ($storedPath && File::exists(public_path($storedPath))) {
                $filename = $student->name . '_' . $grade->grade . '_certificate.pdf';
                return response()->file(public_path($storedPath), [
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]);
            }

            // Fallback: Generate PDF on-the-fly if saving failed
            $pdf = PDF::loadView('certificates.studentcertificate', [
                'student' => $student,
                'grade' => $grade,
                'certificate' => $certificate,
                'custom_quote' => null,
                'schoolLogoPath' => $schoolLogoPath
            ]);

            $pdf->setPaper('A4', 'landscape');

            $filename = $student->name . '_' . $grade->grade . '_certificate.pdf';

            return $pdf->download($filename);
        }
    }

    public function bulkDownloadCertificates($token)
    {
        // Validate bulk token format
        if (!str_starts_with($token, 'BULK-')) {
            abort(404, 'Invalid bulk download token.');
        }

        // Extract certificate IDs from token
        $parts = explode('-', $token);
        if (count($parts) < 3) {
            abort(404, 'Invalid bulk download token format.');
        }

        // Remove 'BULK-' prefix and random string to get certificate IDs
        array_shift($parts); // Remove 'BULK'
        array_shift($parts); // Remove random string
        $certificate_ids = $parts;

        \Log::info('Bulk Certificate Download: Creating ZIP file', [
            'bulk_token' => substr($token, 0, 20) . '...',
            'certificate_ids' => $certificate_ids,
            'certificate_count' => count($certificate_ids)
        ]);

        // Since this route is protected by trainer middleware, user is authenticated
        // Just verify that this trainer owns all the certificates
        $trainer = auth()->user()->trainer;
        if (!$trainer) {
            abort(403, 'Trainer access required.');
        }

        $certificates = StudentCertificates::whereIn('id', $certificate_ids)
            ->where('trainer_id', $trainer->id)
            ->with(['student', 'grade'])
            ->get();

        if ($certificates->isEmpty() || $certificates->count() != count($certificate_ids)) {
            abort(404, 'Some certificates not found or access denied.');
        }

        // Create ZIP file
        $zipFileName = 'certificates_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path('temp/' . $zipFileName);

        // Ensure temp directory exists
        $tempDir = public_path('temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Create ZIP file
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($certificates as $certificate) {
                if ($certificate->pdf_path && File::exists(public_path($certificate->pdf_path))) {
                    $fileName = $certificate->student->name . '_' . $certificate->grade->grade . '_certificate.pdf';
                    $zip->addFile(public_path($certificate->pdf_path), $fileName);
                }
            }
            $zip->close();
        } else {
            abort(500, 'Failed to create ZIP file.');
        }

        \Log::info('Bulk Certificate Download: ZIP file created', [
            'zip_path' => $zipPath,
            'certificate_count' => $certificates->count()
        ]);

        // Return ZIP file for download
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function testCertificateDesign(Request $request)
    {
        // Create dummy student data
        $dummyStudent = (object) [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];

        
        // Resolve the primary/default grade dynamically — see the matching
        // comment in certificateManagement() above.
        $thinkpreneurGrade = Grade::where('is_publish', 1)->where('is_primary', 1)->orderBy('display_order_id')->first()
            ?: Grade::where('is_publish', 1)->orderBy('display_order_id')->first();

        $dummyGrade = (object) [
            'id' => $thinkpreneurGrade->id,
            'grade' => $thinkpreneurGrade->grade,
            'description' => $thinkpreneurGrade->description,
            'cert_description' => $thinkpreneurGrade->cert_description,
            'cert_quote' => $thinkpreneurGrade->cert_quote
        ];

        // Create dummy certificate data
        $dummyCertificate = (object) [
            'id' => 1,
            'student_id' => 1,
            'grade_id' => 1,
            'unique_id' => strtoupper(Str::random(10)),
            'issue_date' => now()->toDateString(),
            'download_token' => 'test-token-123',
            'token_expires_at' => now()->addWeek(),
            'student' => $dummyStudent,
            'grade' => $dummyGrade
        ];

        // Get custom quote from request or use default
        $customQuote = $request->get('quote', null);
        $schoolLogoPath = asset('asset/images/logo.png');
        $schoolId = $request->get('school_id', null);

        if ($schoolId) {
            $school = School::find($schoolId);
            if ($school && isset($school->school_logo)) {
                $logoPath = \Storage::disk('tenant_uploads')->path($school->school_logo);
                if (File::exists($logoPath)) {
                    $schoolLogoPath = url('tenants/'.$school->school_logo);
                }
            }
        }

        // Return HTML view for design testing
        if ($request->get('format') === 'pdf') {
            $pdf = PDF::loadView('certificates.studentcertificate', [
                'student' => $dummyStudent,
                'grade' => $dummyGrade,
                'certificate' => $dummyCertificate,
                'custom_quote' => $customQuote,
                'schoolLogoPath' => $schoolLogoPath
            ]);
            $pdf->setPaper('A4', 'landscape');
            return $pdf->stream('certificate-preview.pdf');
        }

        return view('certificates.studentcertificate', [
            'student' => $dummyStudent,
            'grade' => $dummyGrade,
            'certificate' => $dummyCertificate,
            'custom_quote' => $customQuote,
            'schoolLogoPath' => $schoolLogoPath
        ]);
    }
}
