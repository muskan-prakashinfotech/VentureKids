<?php

namespace App\Services;

use App\Helpers\CommonHelper;
use App\Models\ExternalSession;
use App\Models\School;
use App\Models\Students;
use App\Models\Trainer;
use App\Models\TrainerAllocationNew;

class ExternalSessionAttendeeService
{
    /**
     * Resolve the current list of email recipients for a session, based on
     * its live attendees/levels/batches/notify_recipients configuration.
     *
     * Kept out of the controller so queued jobs can re-resolve a fresh
     * recipient list at send time instead of relying on a list captured
     * when the job was first dispatched.
     */
    public function resolve(ExternalSession $external_session): array
    {
        $recipients   = [];
        $attendeeType = $external_session->attendee_type ?? 'schools';

        $schoolIds = $external_session->attendees === 'all'
            ? School::pluck('id')->toArray()
            : explode(',', $external_session->attendees);

        if (isPartnerUser()) {
            $partnerCountryId = partnerCountryId();
            if (empty($partnerCountryId)) {
                $schoolIds = [];
            } else {
                $schoolIds = \App\Models\School::query()
                    ->whereIn('id', $schoolIds)
                    ->where('country_id', $partnerCountryId)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        $sessionBatches = null;
        if (!is_null($external_session->batches)) {
            $sessionBatches = $external_session->batches === 'all' ? 'all' : explode(',', $external_session->batches);
        }

        $notifyRecipients = $external_session->notify_recipients;
        $allowedTypes     = !empty($notifyRecipients)
            ? explode(',', $notifyRecipients)
            : [];

        // stored in notify_recipients
        $includeSchools  = $attendeeType !== 'trainer' && in_array('school',  $allowedTypes);
        $includeStudents = $attendeeType !== 'trainer' && in_array('student', $allowedTypes);
        $includeTrainers = in_array('trainer', $allowedTypes);

        // ── School recipients ────────────────────────────────────────────────────
        if ($includeSchools) {
            $schools = School::with('countrynew')->whereIn('id', $schoolIds)->get();
            foreach ($schools as $school) {
                if (!empty($school->official_email_id)) {
                    $recipients[] = [
                        'email'     => $school->official_email_id,
                        'name'      => $school->school_name,
                        'type'      => 'school',
                        'school_id' => $school->id,
                        'timezone'  => $school->countrynew?->timezone ?? 'UTC',
                    ];
                }
            }
        }

        // ── Student recipients ───────────────────────────────────────────────────
        if ($includeStudents) {
            // Levels filter applies only for schools-type sessions;
            // trainer-type sessions have no level restriction.
            $sessionLevels = ($attendeeType !== 'trainer' && $external_session->levels === 'all')
                ? 'all'
                : ($attendeeType !== 'trainer'
                    ? explode(',', $external_session->levels)
                    : 'all');

            $studentQuery = Students::select(['id', 'user_id', 'school_id', 'country_id', 'grade_id', 'school_batch_id'])
                ->with([
                    'user'    => fn($q) => $q->select(['id', 'name', 'email', 'username']),
                    'country' => fn($q) => $q->select(['id', 'timezone']),
                ])
                ->whereIn('school_id', $schoolIds);

            if (!is_null($sessionBatches) && $sessionBatches !== 'all') {
                $studentQuery->whereIn('school_batch_id', $sessionBatches);
            }

            foreach ($studentQuery->get() as $student) {
                $studentLevels = explode(',', $student->grade_id);
                $send = ($sessionLevels === 'all' || count(array_intersect($studentLevels, $sessionLevels)) > 0);

                if (!$send) continue;

                $toEmail = CommonHelper::getRecipientEmailByUserId($student->user->id, false);
                if (empty($toEmail)) continue;

                $timezone = 'UTC';
                if ($student->country?->timezone) {
                    $timezone = $student->country->timezone;
                } else {
                    $sch = School::select(['id', 'country_id'])
                        ->with(['countrynew' => fn($q) => $q->select(['id', 'timezone'])])
                        ->find($student->school_id);
                    if ($sch?->countrynew?->timezone) {
                        $timezone = $sch->countrynew->timezone;
                    }
                }
                $recipients[] = [
                    'email' => $toEmail,
                    'name'  => $student->user->name,
                    'type'  => 'student',
                    'student_id' => $student->id,
                    'school_id'  => $student->school_id,
                    'timezone' => $timezone,
                ];
            }
        }

        // ── Trainer recipients ───────────────────────────────────────────────────
        if ($includeTrainers) {
            if ($attendeeType === 'trainer') {
                // Specific trainers selected, or all trainers from the selected schools
                if (!empty($external_session->trainer_ids) && $external_session->trainer_ids !== 'all') {
                    $trainerIds = explode(',', $external_session->trainer_ids);
                } else {
                    $trainerIds = TrainerAllocationNew::whereIn('school_id', $schoolIds)
                        ->pluck('trainer_id')
                        ->toArray();
                }
            } else {
                // Schools-type: original trainer resolution (by batch or by school)
                if (!is_null($sessionBatches) && $sessionBatches !== 'all') {
                    $trainerIds = TrainerAllocationNew::whereIn('school_id', $schoolIds)
                        ->whereIn('school_batch_id', $sessionBatches)
                        ->pluck('trainer_id')
                        ->toArray();
                } else {
                    $trainerIds = $external_session->attendees === 'all'
                        ? Trainer::pluck('id')->toArray()
                        : TrainerAllocationNew::whereIn('school_id', explode(',', $external_session->attendees))
                            ->pluck('trainer_id')
                            ->toArray();
                }
            }

            $trainers = Trainer::with('country')
                ->whereHas('user', fn($q) => $q->where('suspend', 2))
                ->whereIn('id', $trainerIds)
                ->get();

            foreach ($trainers as $trainer) {
                if (!empty($trainer->official_email_id)) {
                    $recipients[] = [
                        'email' => $trainer->official_email_id,
                        'name' => $trainer->trainer_name,
                        'type' => 'trainer',
                        'trainer_id' => $trainer->id,
                        'timezone' => $trainer->country?->timezone ?? 'UTC',
                    ];
                }
            }
        }

        return $recipients;
    }
}
