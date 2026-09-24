<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\School;
use App\Models\Students;
use App\Models\LoginTracking;
use App\Enums\UserType;
use App\Exports\StudentLoginHistoryExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
class LoginTrackingController extends Controller
{
    public function index()
    {
        $grades = Grade::all();
        // Only fetch schools where the related user is not suspended
        $schoolList = applyCountryScope(
            School::select('id', 'school_name')
            ->whereHas('user', function ($query) {
                $query->where('suspend', '!=', 1); // Only active users
            })
            ->orderBy('school_name'),
            'country_id'
        )->get();
        $filteredLevels = [];
        $filtered_collection = $grades->filter(function ($item) use (&$filteredLevels) {
            if ($item->is_primary == 1) {
                $filteredLevels['primary'][$item->id] = $item->toArray();
            } else {
                $filteredLevels['add-ons'][$item->id] = $item->toArray();;
            }
        })->values();

        return view('backend.school.student.student_login_history', compact('grades', 'filteredLevels', 'schoolList'));
    }
    public function login_history_datatable(Request $request)
    {
        if (!$request->filled('school_id')) {
            return datatables()->of(collect())->make(true);
        }

        $schoolId = (int) $request->school_id;
        if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
            return datatables()->of(collect())->make(true);
        }

        // Get latest login ID per student
        $latestLoginIds = LoginTracking::selectRaw('MAX(id) as id')
            ->where('user_type', UserType::STUDENT->value)
            ->whereHas('student', function ($q) use ($request) {
                $q->where('school_id', $request->school_id)
                    ->whereHas('user', function ($uq) {
                        $uq->where('suspend', '!=', 1);  // Exclude suspended users
                    });

                if (!empty($request->grade_id)) {
                    $q->whereRaw('FIND_IN_SET(?, grade_id)', [$request->grade_id]);
                }
            })
            ->groupBy('item_id')
            ->pluck('id');

        // Fetch login records
        $query = LoginTracking::with([
            'student' => function ($q) {
                $q->select('id', 'name', 'grade_id', 'school_id')
                    ->whereHas('user', function ($uq) {
                        $uq->where('suspend', '!=', 1);  // Ensure students from suspended users are excluded
                    });
            }
        ])
            ->whereIn('login_trackings.id', $latestLoginIds);

        // Date Filters
        if ($request->filled('from_date')) {
            $query->whereDate('login_at', '>=', Carbon::parse($request->from_date));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('login_at', '<=', Carbon::parse($request->to_date));
        }

        $query->orderBy('login_at', 'desc');

        //  Return for DataTable
        if ($request->ajax()) {
            return datatables()->of($query)
                ->addColumn('student_name', fn($row) => optional($row->student)->name ?? 'N/A')
                ->editColumn('login_at', function ($row) {
                    return $row->login_at ? $row->login_at->format('Y-m-d') : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0)" class="btn btn-sm btn-info view-logins-btn" 
                                data-student-id="' . $row->student->id . '">
                                <i class="fa fa-eye"></i> View All Login
                            </a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /* To get all the login information of student in modal */
    public function getStudentLoginHistory(Request $request)
    {
        $studentId = $request->get('student_id');

        $student = Students::select('id', 'name', 'school_id')
            ->where('id', $studentId)
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        if (!applyCountryScope(School::query()->whereKey($student->school_id), 'country_id')->exists()) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $logins = LoginTracking::where('user_type', UserType::STUDENT->value)
            ->where('item_id', $studentId)
            ->orderByDesc('login_at')
            ->get(['login_at']);

        // Group by date and get only the first login of each date
        $uniqueDailyLogins = $logins->groupBy(function ($log) {
            return Carbon::parse($log->login_at)->format('Y-m-d');
        })->keys() // get just the unique dates
            ->map(function ($date) {
                return ['login_at' => $date];
            })
            ->values(); // reset index

        return response()->json([
            'student_name' => $student->name,
            'logins' => $uniqueDailyLogins,
        ]);
    }

    public function exportStudentLoginHistory($studentId)
    {
        $student = Students::select('id', 'name', 'school_id')->findOrFail($studentId);
        if (!applyCountryScope(School::query()->whereKey($student->school_id), 'country_id')->exists()) {
            abort(403, 'Access denied.');
        }
        $fileName = 'Login History - ' . $student->name . '.xlsx';

        return Excel::download(new StudentLoginHistoryExport($studentId), $fileName);
    }

    public function exportLoginHistoryOfAllStudent(Request $request)
    {
       if ($request->filled('school_id')) {
           $schoolId = (int) $request->school_id;
           if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
               abort(403, 'Access denied.');
           }
       }

       return Excel::download(new StudentLoginHistoryExport(null, $request), 'Student Login History.xlsx');
    }
}
