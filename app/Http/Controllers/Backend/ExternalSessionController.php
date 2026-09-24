<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreOrUpdateExternalSessionRequest;
use App\Models\ExternalSession; 
use App\Models\School; 
use App\Jobs\QueueExternalSessionEmails;
use App\Jobs\QueueExternalSessionReminderEmails;
use App\Jobs\QueueSessionCancelledEmails;
use App\Services\ExternalSessionAttendeeService;
use Illuminate\Support\Facades\Bus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ExternalSessionController extends Controller
{
    protected function getSessionFormOptions()
    {
        $schools = \App\Models\School::select(['id', 'school_name'])
            ->whereHas('user', function($query) {
                $query->where('suspend', 2);
            });

        if (isPartnerUser()) {
            applyCountryScope($schools, 'country_id');
        }

        $schools = $schools->orderBy('school_name')->get();

        $primaryLevels = \App\Models\Grade::select(['id', 'grade'])->where('is_primary', 1)->get();
        $addonLevels = \App\Models\Grade::select(['id', 'grade'])->where('is_primary', 0)->get();

        return [$schools, $primaryLevels, $addonLevels];
    }

    public function index()
    {
        $external_session = ExternalSession::whereNull('parent_session_id')
            ->when(isPartnerUser(), function ($query) {
                    $query->where('created_by', auth()->id());
                })
            ->orderBy('date_time', 'desc')
            ->get();
        return view('backend.external_session.index', compact('external_session'));
    }

    public function create()
    {
        [$schools, $primaryLevels, $addonLevels] = $this->getSessionFormOptions();

        $preSelectedTrainers = [];

        return view('backend.external_session.create', compact('schools', 'primaryLevels', 'addonLevels', 'preSelectedTrainers'));
    }

    public function duplicate(ExternalSession $external_session)
    {
        $this->ensurePartnerOwnsSession($external_session);

        [$schools, $primaryLevels, $addonLevels] = $this->getSessionFormOptions();

        $form_session = clone $external_session;
        $form_session->exists = false;
        unset($form_session->id, $form_session->email_job_id, $form_session->email_reminder_job_id, $form_session->created_at, $form_session->updated_at);

        return view('backend.external_session.create', compact('schools', 'primaryLevels', 'addonLevels', 'form_session'));
    }

    // Store new external session
    public function store(StoreOrUpdateExternalSessionRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        // Session type
        $data['session_type'] = (int) $request->input('session_type');

        // For offline sessions zoom_link is not required; store '' to satisfy NOT NULL constraint
        if ($data['session_type'] === 0) {
            $data['zoom_link']      = '';
            $data['send_zoom_link'] = false;
        } else {
            $data['send_zoom_link'] = $request->has('send_zoom_link');
        }

        $data['zoom_link'] = $data['zoom_link'] ?? '';

        // Attendee type
        $data['attendee_type'] = $request->input('attendee_type', 'schools');

        // Attendees (school IDs) — shared for both types
        if ($request->has('all_schools')) {
            $data['attendees'] = 'all';
        } else {
            $data['attendees'] = implode(',', $request->input('schools', []));
        }

        // Levels — only for schools type
        if ($data['attendee_type'] === 'schools') {
            if ($request->has('all_levels')) {
                $data['levels'] = 'all';
            } else {
                $data['levels'] = implode(',', $request->input('levels', []));
            }
            $data['trainer_ids'] = null;
        } else {
            // Trainer type: no levels, store trainer selection
            $data['levels'] = '';
            if ($request->has('all_schools') || $request->has('all_trainers')) {
                $data['trainer_ids'] = 'all';
            } else {
                $trainerIds = $request->input('trainer_ids', []);
                $data['trainer_ids'] = !empty($trainerIds) ? implode(',', $trainerIds) : null;
            }
        }

        // Batches
        $data['batches'] = null;
        if ($request->has('all_batches')) {
            $data['batches'] = 'all';
        } elseif($request->has('batches')) { // Only consider batches if not all schools (and thus batches) are selected
            $selectedBatches = $request->input('batches', []);
            $data['batches'] = implode(',', $selectedBatches);
        }

        // Notify recipients — school/student notifications only make sense
        // for schools-type attendees; trainer-type sessions can only notify trainers
        $notifyRecipients = $request->input('notify_recipients', []);
        if ($data['attendee_type'] === 'trainer') {
            $notifyRecipients = array_intersect($notifyRecipients, ['trainer']);
        }
        $data['notify_recipients'] = !empty($notifyRecipients) ? implode(',', $notifyRecipients) : null;

        $data['date_time'] = \Carbon\Carbon::parse($request->input('date_time'), 'Asia/Singapore')->timezone('UTC');
        $this->applyRecurrenceData($data, $request);

        $external_session = ExternalSession::create($data);

        $this->scheduleReminderChains($external_session);

        return redirect()->route('backend.external_session.list')->with('message', 'Session created successfully.');
    }

    public function edit(ExternalSession $external_session)
    {
        $this->ensurePartnerOwnsSession($external_session);
        
        if ($external_session->is_cancelled) {
            return redirect()
                ->route('backend.external_session.list')
                ->with('message', 'Cancelled sessions cannot be edited.');
        }

        [$schools, $primaryLevels, $addonLevels] = $this->getSessionFormOptions();

        $batches = collect();
        if ($external_session->attendees && $external_session->attendees !== 'all') {
            $schoolIds = explode(',', $external_session->attendees);
            $batches   = \App\Models\SchoolBatch::withoutGlobalScope(\App\Scopes\SchoolBatchScope::class)
                ->select(['id', 'batch_name', 'school_id'])
                ->whereIn('school_id', $schoolIds)
                ->get();
        }

        $preSelectedTrainers = [];
        if (($external_session->attendee_type ?? 'schools') === 'trainer'
            && !empty($external_session->trainer_ids)
            && $external_session->trainer_ids !== 'all'
        ) {
            $preSelectedTrainers = explode(',', $external_session->trainer_ids);
        }

        return view('backend.external_session.edit', compact(
            'external_session', 'schools', 'primaryLevels', 'addonLevels', 'batches', 'preSelectedTrainers'
        ));
    }

    public function update(StoreOrUpdateExternalSessionRequest $request, $id)
    {
        $external_session = ExternalSession::findOrFail($id);
        $this->ensurePartnerOwnsSession($external_session);

        if ($external_session->is_cancelled) {
            return redirect()
                ->route('backend.external_session.list')
                ->with('message', 'Cancelled sessions cannot be edited.');
        }

        $data = $request->validated();
        $data['created_by'] = $external_session->created_by;

        // Session type
        $data['session_type'] = (int) $request->input('session_type');

        if ($data['session_type'] === 0) {
            $data['zoom_link']      = '';
            $data['send_zoom_link'] = false;
        } else {
            $data['send_zoom_link'] = $request->has('send_zoom_link');
        }

        $data['zoom_link'] = $data['zoom_link'] ?? '';

        // Attendee type
        $data['attendee_type'] = $request->input('attendee_type', 'schools');

        if ($request->has('all_schools')) {
            $data['attendees'] = 'all';
        } else {
            $selectedSchools = $request->input('schools', []);
            $data['attendees'] = implode(',', $selectedSchools); // Convert to comma-separated string
        }

        if ($data['attendee_type'] === 'schools') {
            if ($request->has('all_levels')) {
                $data['levels'] = 'all';
            } else {
                $data['levels'] = implode(',', $request->input('levels', []));
            }
            $data['trainer_ids'] = null;
        } else {
            $data['levels'] = '';
            if ($request->has('all_schools') || $request->has('all_trainers')) {
                $data['trainer_ids'] = 'all';
            } else {
                $trainerIds = $request->input('trainer_ids', []);
                $data['trainer_ids'] = !empty($trainerIds) ? implode(',', $trainerIds) : null;
            }
        }

        $data['batches'] = null;
        if ($request->has('all_batches')) {
            $data['batches'] = 'all';
        } elseif($request->has('batches')) {
            $selectedBatches = $request->input('batches', []);
            $data['batches'] = implode(',', $selectedBatches);
        }

        // Notify recipients — school/student notifications only make sense
        // for schools-type attendees; trainer-type sessions can only notify trainers
        $notifyRecipients = $request->input('notify_recipients', []);
        if ($data['attendee_type'] === 'trainer') {
            $notifyRecipients = array_intersect($notifyRecipients, ['trainer']);
        }
        $data['notify_recipients'] = !empty($notifyRecipients) ? implode(',', $notifyRecipients) : null;

        $data['date_time'] = \Carbon\Carbon::parse($request->input('date_time'), 'Asia/Singapore')->timezone('UTC');
        $this->applyRecurrenceData($data, $request);

        $jobsNeedReschedule = $data['date_time']->format('Y-m-d H:i:s') !== Carbon::parse($external_session->date_time, 'UTC')->format('Y-m-d H:i:s')
            || $data['attendees']    !== $external_session->attendees
            || $data['attendee_type'] !== ($external_session->attendee_type ?? 'schools')
            || $data['trainer_ids']  !== $external_session->trainer_ids
            || $data['levels']       !== $external_session->levels
            || $data['batches']      !== $external_session->batches
            || $data['recurrence_type'] !== ($external_session->recurrence_type ?? 'none')
            || $data['recurrence_interval'] !== ($external_session->recurrence_interval ?? 1)
            || $data['recurrence_days'] !== $external_session->recurrence_days
            || $data['recurrence_end_date'] !== $external_session->recurrence_end_date
            || $data['recurrence_count'] !== $external_session->recurrence_count
            || $data['recurrence_custom_dates'] !== $external_session->recurrence_custom_dates
            || $data['notify_recipients'] !== $external_session->notify_recipients;

        $external_session->update($data);

        if ($jobsNeedReschedule) {

            // Cancel old jobs if they exist
            if ($external_session->email_job_id) {
                DB::table('jobs')->where('id', $external_session->email_job_id)->delete();
            }
            if ($external_session->email_reminder_job_id) {
                DB::table('jobs')->where('id', $external_session->email_reminder_job_id)->delete();
            }

            $external_session->is_cancelled = false;
            $this->scheduleReminderChains($external_session);
        }

        return redirect()->route('backend.external_session.list')->with('message', 'Session updated successfully.');  
    }

    // Delete external session
    public function destroy($id)
    {
        $external_session = ExternalSession::findOrFail($id);
        $this->ensurePartnerOwnsSession($external_session);

        $childSessions = ExternalSession::where('parent_session_id', $external_session->id)->get();
        $sessionsToDelete = $childSessions->push($external_session);

        foreach ($sessionsToDelete as $session) {
            if ($session->email_job_id) {
                DB::table('jobs')->where('id', $session->email_job_id)->delete();
            }
            if ($session->email_reminder_job_id) {
                DB::table('jobs')->where('id', $session->email_reminder_job_id)->delete();
            }
        }

        ExternalSession::where('parent_session_id', $external_session->id)->delete();
        $external_session->delete();

        return redirect()->route('backend.external_session.list')->with('message', 'Session deleted successfully.'); 
    }

    public function getBatchesBySchools(Request $request)
    {
        $schoolIds = $request->input('school_ids', []);

        if (isPartnerUser()) {
            $schoolIds = $this->filterPartnerSchoolIds($schoolIds);
        }

        $batches = \App\Models\SchoolBatch::withoutGlobalScope(\App\Scopes\SchoolBatchScope::class)
            ->select(['school_batch.id', 'school_batch.batch_name', 'school_batch.school_id', 'schools.school_name'])
            ->join('schools', 'schools.id', '=', 'school_batch.school_id')
            ->when(!empty($schoolIds), fn($q) => $q->whereIn('school_batch.school_id', $schoolIds))
            ->get();

        return response()->json($batches);
    }

    public function getTrainersBySchool(Request $request)
    {
        $schoolIds = $request->input('school_ids', []);

        if (isPartnerUser()) {
            $schoolIds = $this->filterPartnerSchoolIds($schoolIds);
        }

        $trainers = \App\Models\Trainer::join('trainer_allocation_new', 'trainers.id', '=', 'trainer_allocation_new.trainer_id')
            ->join('schools', 'schools.id', '=', 'trainer_allocation_new.school_id')
            ->whereHas('user', fn($q) => $q->where('suspend', 2))
            ->when(!empty($schoolIds), fn($q) => $q->whereIn('trainer_allocation_new.school_id', $schoolIds))
            ->select([
                'trainers.id',
                'trainers.trainer_name',
                'schools.school_name',
                'trainer_allocation_new.school_id',
            ])
            ->distinct()
            ->get();

        return response()->json($trainers);
    }

    public function fetchSessionAttendees($external_session)
    {
        return app(ExternalSessionAttendeeService::class)->resolve($external_session);
    }

    // Cancel external session
    public function cancel($id)
    {
        $session = ExternalSession::findOrFail($id);
        
        // Cancel old job if exists
        if ($session->email_job_id) {
            DB::table('jobs')->where('id', $session->email_job_id)->delete();
        }
        if ($session->email_reminder_job_id) {
            DB::table('jobs')->where('id', $session->email_reminder_job_id)->delete();
        }

        $session->is_cancelled = true;
        $session->email_job_id = null;
        $session->email_reminder_job_id = null;
        $session->save();

        // Send cancellation email
        $this->sendCancellationEmails($session);

        return redirect()->route('backend.external_session.list')->with('message', 'Session has been cancelled and emails have been sent.');
    }

    protected function sendCancellationEmails($session)
    {
        $recipients = $this->fetchSessionAttendees($session);

        if(count($recipients)) {
            safeDispatchAction('external session cancellation emails', [
                'external_session_id' => $session->id ?? null,
                'recipient_count' => count($recipients),
            ], function () use ($session, $recipients) {
                QueueSessionCancelledEmails::dispatch($session, $recipients);
            });
        }

    }

    public function calendar()
    {
        return view('backend.external_session.calendar');
    }

    public function calendarData(Request $request)
    {
        $from = $request->query('start');
        $to = $request->query('end');

        try {
            $from = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        } catch (\Exception $e) {
            $from = Carbon::now()->subMonth()->startOfDay();
        }

        try {
            $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->addMonth()->endOfDay();
        } catch (\Exception $e) {
            $to = Carbon::now()->addMonth()->endOfDay();
        }

        $sessions = ExternalSession::whereNull('parent_session_id')
            ->where('is_cancelled', false)
            ->when(isPartnerUser(), function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->orderBy('date_time', 'asc')
            ->get();

        $events = ExternalSession::occurrencesForSessions($sessions, $from, $to)
            ->map(function ($entry) {
                $session = $entry['session'];
                $dateTime = $entry['date_time']->copy();
                return [
                    'id' => $session->id . '_' . $dateTime->format('YmdHi'),
                    'title' => $session->title,
                    'start' => $dateTime->toIso8601String(),
                    'end' => $dateTime->copy()->addHour()->toIso8601String(),
                    'extendedProps' => [
                        'session_id' => $session->id,
                        'speaker' => $session->speaker,
                        'agenda' => $session->agenda,
                        'zoom_link' => $session->zoom_link,
                        'session_type' => $session->session_type,
                        'recurrence_summary' => $session->recurrenceSummary(),
                        'is_recurring' => $session->isRecurring(),
                    ],
                ];
            });

        return response()->json($events);
    }
    
    protected function applyRecurrenceData(array &$data, Request $request)
    {
        $data['recurrence_type'] = $request->input('recurrence_type', 'none');

        if ($data['recurrence_type'] === 'none') {
            $data['recurrence_interval'] = 1;
            $data['recurrence_days'] = null;
            $data['recurrence_end_date'] = null;
            $data['recurrence_count'] = null;
            $data['recurrence_custom_dates'] = null;
        } elseif ($data['recurrence_type'] === 'custom') {
            $data['recurrence_interval'] = 1;
            $data['recurrence_days'] = null;
            $data['recurrence_end_date'] = null;
            $data['recurrence_count'] = null;

            $customDates = $request->input('recurrence_custom_dates', []);
            $customDates = collect(is_array($customDates) ? $customDates : [])
                ->filter()
                ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
                ->unique()
                ->sort()
                ->values();
            $data['recurrence_custom_dates'] = $customDates->isNotEmpty() ? $customDates->implode(',') : null;
        } else {
            $data['recurrence_interval'] = $data['recurrence_type'] === 'daily'
                ? max(1, (int) $request->input('recurrence_interval', 1))
                : 1;
            $recurrenceDays = $request->input('recurrence_days', []);
            $data['recurrence_days'] = is_array($recurrenceDays) ? implode(',', $recurrenceDays) : $recurrenceDays;
            $data['recurrence_end_date'] = $request->input('recurrence_end_date') ?: null;
            $data['recurrence_count'] = $request->input('recurrence_count') ?: null;
            $data['recurrence_custom_dates'] = null;
        }

        $data['parent_session_id'] = null;
        $data['is_exception'] = false;
        $data['original_date_time'] = null;
        $data['is_cancelled'] = false;
    }

    /**
     * Dispatch the first link of the self-chaining reminder-email jobs
     * (2-days-before and 1-hour-before) starting from the next active
     * occurrence. Each job checks itself before sending and, when it runs,
     * dispatches the next link for whatever the next active occurrence is
     * at that time — so cancelling a single occurrence later on just needs
     * to mark it cancelled; the chain skips over it on its own.
     */
    protected function scheduleReminderChains(ExternalSession $external_session): void
    {
        if (empty($external_session->notify_recipients)) {
            $external_session->email_job_id = null;
            $external_session->email_reminder_job_id = null;
            $external_session->save();
            return;
        }

        $anchor = $external_session->nextOccurrenceFrom(Carbon::now('UTC'));

        if (!$anchor) {
            $external_session->save();
            return;
        }

        $sendAt = $anchor->copy()->timezone('Asia/Singapore')->subDays(2)->timezone('UTC');
        $job = new QueueExternalSessionEmails($external_session, $anchor);
        $job->delay($sendAt);
        $external_session->email_job_id = Bus::dispatch($job);

        $sendAt = $anchor->copy()->timezone('Asia/Singapore')->subHour()->timezone('UTC');
        $job = new QueueExternalSessionReminderEmails($external_session, $anchor);
        $job->delay($sendAt);
        $external_session->email_reminder_job_id = Bus::dispatch($job);

        $external_session->save();
    }

    // Delete a single occurrence of a recurring session
    public function deleteOccurrence(Request $request)
    {
        $sessionId = $request->input('session_id');
        $occurrenceDateTime = $request->input('occurrence_date_time');

        if (!$sessionId || !$occurrenceDateTime) {
            return response()->json(['error' => 'Missing session_id or occurrence_date_time'], 400);
        }

        try {
            $parentSession = ExternalSession::findOrFail($sessionId);

            // Parse the occurrence datetime (ISO 8601 format from frontend)
            $occurrenceDateTime = Carbon::parse($occurrenceDateTime, 'UTC');

            if ($parentSession->recurrence_type === 'custom') {
                $occurrenceDateStr = $occurrenceDateTime->format('Y-m-d');
                $remainingDates = array_values(array_diff($parentSession->recurrenceCustomDatesArray(), [$occurrenceDateStr]));
                $parentSession->recurrence_custom_dates = !empty($remainingDates) ? implode(',', $remainingDates) : null;
                $parentSession->save();
            }

            // Create an exception record (child session) to mark this occurrence as cancelled
            $exception = ExternalSession::create([
                'parent_session_id' => $parentSession->id,
                'is_exception' => true,
                'original_date_time' => $occurrenceDateTime,
                'is_cancelled' => true,
                'title' => $parentSession->title,
                'session_type' => $parentSession->session_type,
                'agenda' => $parentSession->agenda,
                'date_time' => $occurrenceDateTime,
                'zoom_link' => $parentSession->zoom_link,
                'send_zoom_link' => $parentSession->send_zoom_link,
                'speaker' => $parentSession->speaker,
                'attendees' => $parentSession->attendees,
                'attendee_type' => $parentSession->attendee_type,
                'trainer_ids' => $parentSession->trainer_ids,
                'levels' => $parentSession->levels,
                'batches' => $parentSession->batches,
                'notify_recipients' => $parentSession->notify_recipients,
                'recurrence_type' => 'none',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Occurrence deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete occurrence: ' . $e->getMessage()
            ], 500);
        }
    }

    private function ensurePartnerOwnsSession(?ExternalSession $externalSession): void
    {
        if (!isPartnerUser()) {
            return;
        }

        if (!$externalSession || (int) $externalSession->created_by !== (int) auth()->id()) {
            abort(403, 'Access denied.');
        }
    }

    private function filterPartnerSchoolIds(array $schoolIds): array
    {
        $partnerCountryId = partnerCountryId();
        if (empty($partnerCountryId)) {
            return [];
        }

        $allowedSchoolIds = \App\Models\School::query()
            ->whereIn('id', $schoolIds)
            ->where('country_id', $partnerCountryId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $allowedSchoolIds;
    }

}
