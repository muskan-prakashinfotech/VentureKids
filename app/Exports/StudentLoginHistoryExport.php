<?php

namespace App\Exports;

use App\Models\School;
use App\Models\LoginTracking;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class StudentLoginHistoryExport implements FromArray, WithHeadings
{
    protected ?int $studentId;
    protected ?Request $request;

    public function __construct(?int $studentId = null, ?Request $request = null)
    {
        $this->studentId = $studentId;
        $this->request = $request;
    }

    public function headings(): array
    {
        return $this->studentId
            ? ['#', 'Login Date']
            : ['Student Name', 'Login Date'];
    }

    public function array(): array
    {
        if ($this->studentId) {
            // Export for single student
            $logins = LoginTracking::where('user_type', UserType::STUDENT->value)
                ->where('item_id', $this->studentId)
                ->orderByDesc('login_at')
                ->get(['login_at']);

            $uniqueDates = $logins->groupBy(function ($log) {
                return $log->login_at->format('Y-m-d');
            });

            $data = [];
            $i = 1;
            foreach ($uniqueDates as $date => $entries) {
                $data[] = [$i++, $date];
            }
        } else {
            // Export for all students with filters
            if (!empty($this->request?->school_id)) {
                $schoolId = (int) $this->request->school_id;
                if (!applyCountryScope(School::query()->whereKey($schoolId), 'country_id')->exists()) {
                    return [['Access denied', '']];
                }
            }

            $latestLoginIds = LoginTracking::selectRaw('MAX(id) as id')
                ->where('user_type', UserType::STUDENT->value)
                ->whereHas('student', function ($q) {
                    $q->where('school_id', $this->request->school_id)
                        ->whereHas('user', function ($uq) {
                            $uq->where('suspend', '!=', 1);
                        });

                    if (!empty($this->request->grade_id)) {
                        $q->whereRaw('FIND_IN_SET(?, grade_id)', [$this->request->grade_id]);
                    }
                })
                ->groupBy('item_id')
                ->pluck('id');

            $query = LoginTracking::with('student')
                ->whereIn('id', $latestLoginIds);

            $query->whereHas('student.school', function ($q) {
                applyCountryScope($q, 'country_id');
            });

            if ($this->request->filled('from_date')) {
                $query->whereDate('login_at', '>=', Carbon::parse($this->request->from_date));
            }

            if ($this->request->filled('to_date')) {
                $query->whereDate('login_at', '<=', Carbon::parse($this->request->to_date));
            }

            $query->orderBy('login_at', 'desc');

           $data = $query->get()->map(function ($item) {
                return [
                    optional($item->student)->name ?? 'N/A',
                    $item->login_at ? $item->login_at->format('Y-m-d') : 'N/A',
                ];
            })->values()
            ->toArray();
        }
        return $data;

    }
}
