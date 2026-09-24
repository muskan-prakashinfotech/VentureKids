<?php

namespace App\Helpers;

use App\Models\ExternalSession;
use App\Models\Observation;
use App\Models\StudentObservations;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class StudentObservationHelper
{
    /**
     * Format records that carry a single observation_id per row (school / backend / student views).
     * Resolves session title and observation name + icon, returns a flat array per record.
     */
    public static function formatProgressRecords(Collection $records): Collection
    {
        $obsIds       = $records->pluck('observation_id')->filter()->unique()->toArray();
        $observations = Observation::whereIn('id', $obsIds)->get()->keyBy('id');

        $sessionIds = $records->pluck('external_session_id')->filter()->unique()->toArray();
        $sessions   = ExternalSession::whereIn('id', $sessionIds)->get()->keyBy('id');

        return $records->map(function ($rec) use ($observations, $sessions) {
            $obs     = $observations->get($rec->observation_id);
            $session = $sessions->get($rec->external_session_id);
            $sessionDate = $rec->session_date
                ? Carbon::parse($rec->session_date)->format('d M Y')
                : ($session ? Carbon::parse($session->date_time, 'UTC')->format('d M Y') : '');

            return [
                'id'           => $rec->id,
                'session_name' => $session ? $session->title : '',
                'session_date' => $rec->session_date,
                'session_date_formatted' => $sessionDate,
                'session_label' => trim(($session ? $session->title : '') . ($sessionDate ? ' - ' . $sessionDate : '')),
                'name'         => $obs ? $obs->name : '',
                'icon_url'     => $obs ? asset('observations/' . $obs->icon) : '',
                'short_note'   => $rec->short_note,
                'image_url'    => $rec->image ? asset('storage/' . $rec->image) : null,
                'created_at'   => $rec->created_at ? $rec->created_at->format('j M Y, g:i a') : '',
            ];
        });
    }

    /**
     * Format records that carry comma-separated observation_ids per row (trainer session view).
     * Returns observations as an array within each record.
     */
    public static function formatSessionRecords(Collection $records): Collection
    {
        $allObsIds = $records->flatMap(function ($rec) {
            return array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
        })->unique()->values()->toArray();

        $observations = Observation::whereIn('id', $allObsIds)->get()->keyBy('id');

        return $records->map(function ($rec) use ($observations) {
            $obsIds  = array_filter(array_map('intval', explode(',', $rec->observation_id ?? '')));
            $obsData = collect($obsIds)->map(function ($id) use ($observations) {
                $obs = $observations->get($id);
                return $obs ? [
                    'id'       => $obs->id,
                    'name'     => $obs->name,
                    'icon_url' => asset('observations/' . $obs->icon),
                ] : null;
            })->filter()->values();

            return [
                'id'           => $rec->id,
                'grade_id'     => $rec->grade_id,
                'session_date' => $rec->session_date,
                'session_date_formatted' => $rec->session_date
                    ? Carbon::parse($rec->session_date)->format('j M Y')
                    : '',
                'observations' => $obsData,
                'short_note'   => $rec->short_note,
                'image_url'    => $rec->image ? asset('storage/' . $rec->image) : null,
                'created_at'   => $rec->created_at ? $rec->created_at->format('j M Y, g:i a') : '',
            ];
        });
    }

    /**
     * Used by School (progress report), Backend (admin progress report), and the
     * Student dashboard's Observation Report tab.
     * Filters by student and grade using FIND_IN_SET on the grade_id column.
     */
    public static function getByStudentAndGrade(int $studentId, int $gradeId): JsonResponse
    {
        if (!$studentId || !$gradeId) {
            return response()->json([]);
        }

        $records = StudentObservations::where('student_id', $studentId)
            ->whereRaw('FIND_IN_SET(?, grade_id)', [$gradeId])
            ->orderByDesc('session_date')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(self::formatProgressRecords($records));
    }

    /**
     * Used by the Trainer session view.
     * Filters by a specific session, school, student, and trainer combination.
     * observation_id may contain comma-separated values, so returns observations as an array.
     */
    public static function getBySession(int $sessionId, int $schoolId, int $studentId, int $trainerId, ?string $sessionDate = null): JsonResponse
    {
        $records = StudentObservations::where([
                'external_session_id' => $sessionId,
                'school_id'           => $schoolId,
                'student_id'          => $studentId,
                'trainer_id'          => $trainerId,
            ])
            ->where(function ($q) use ($sessionDate) {
                $q->where('session_date', $sessionDate)->orWhereNull('session_date');
            })
            ->latest()->get();

        return response()->json(self::formatSessionRecords($records));
    }
}
