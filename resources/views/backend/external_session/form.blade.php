<form action="{{ $route }}" method="POST" class="card">
    <div class="card-header">
        <h4 class="card-title">{{ isset($external_session) ? 'Edit Session' : 'Add Session' }}</h4>
        <a href="{{ route('backend.external_session.list') }}" class="btn btn-warning float-right">
            <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
        </a>
    </div>
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="card-body table-responsive">
        @csrf
        <div class="form-group">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $external_session->title ?? '') }}" required>
            @error('title') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        @php
            $now = now()->timezone('Asia/Singapore')->format('Y-m-d\TH:i');
        @endphp

        <div class="form-group">
            <label for="date_time" class="form-label">Date & Time</label>
            <input type="datetime-local" name="date_time" class="form-control" value="{{ old('date_time', isset($external_session) ? \Carbon\Carbon::parse($external_session->date_time, 'UTC')->timezone('Asia/Singapore')->format('Y-m-d\TH:i') : '') }}" required min="{{ $now }}">
            @error('date_time') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        @php
            $recurrenceType = old('recurrence_type', $external_session->recurrence_type ?? 'none');
            $recurrenceInterval = old('recurrence_interval', $external_session->recurrence_interval ?? 1);
            $recurrenceDays = old('recurrence_days', isset($external_session) && !empty($external_session->recurrence_days) ? explode(',', $external_session->recurrence_days) : []);
            $recurrenceEndDate = old('recurrence_end_date', $external_session->recurrence_end_date ?? '');
            $recurrenceCount = old('recurrence_count', $external_session->recurrence_count ?? '');
            $recurrenceCustomDates = old('recurrence_custom_dates', isset($external_session) && !empty($external_session->recurrence_custom_dates) ? explode(',', $external_session->recurrence_custom_dates) : []);
        @endphp

        <div class="card card-secondary mb-3">
            <div class="card-header">
                <h5 class="card-title">Recurrence</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label d-block">Repeat</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="recurrence_type" id="recurrence_none" value="none" {{ $recurrenceType === 'none' ? 'checked' : '' }}>
                        <label class="form-check-label" for="recurrence_none">Does not repeat</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="recurrence_type" id="recurrence_daily" value="daily" {{ $recurrenceType === 'daily' ? 'checked' : '' }}>
                        <label class="form-check-label" for="recurrence_daily">Daily</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="recurrence_type" id="recurrence_weekly" value="weekly" {{ $recurrenceType === 'weekly' ? 'checked' : '' }}>
                        <label class="form-check-label" for="recurrence_weekly">Weekly</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="recurrence_type" id="recurrence_custom" value="custom" {{ $recurrenceType === 'custom' ? 'checked' : '' }}>
                        <label class="form-check-label" for="recurrence_custom">Custom</label>
                    </div>
                    {{-- Monthly recurrence is no longer used
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="recurrence_type" id="recurrence_monthly" value="monthly" {{ $recurrenceType === 'monthly' ? 'checked' : '' }}>
                        <label class="form-check-label" for="recurrence_monthly">Monthly</label>
                    </div>
                    --}}
                    @error('recurrence_type') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <div class="form-group" id="recurrence_options" style="display: {{ $recurrenceType === 'none' ? 'none' : 'block' }};">
                    <div id="recurrence_interval_group" style="display: {{ $recurrenceType === 'daily' ? 'block' : 'none' }};">
                        <label for="recurrence_interval" class="form-label">Repeat every</label>
                        <div class="input-group mb-2">
                            <input type="number" min="1" name="recurrence_interval" id="recurrence_interval" class="form-control" value="{{ $recurrenceInterval }}">
                            <div class="input-group-append">
                                <span class="input-group-text">interval(s)</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="recurrence_days_group" style="display: {{ $recurrenceType === 'weekly' ? 'block' : 'none' }};">
                        <label class="form-label">Repeat on</label>
                        <div class="form-row">
                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="recurrence_days[]" id="recurrence_day_{{ $day }}" value="{{ $day }}" {{ in_array($day, $recurrenceDays) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="recurrence_day_{{ $day }}">{{ ucfirst($day) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('recurrence_days') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group" id="recurrence_custom_dates_group" style="display: {{ $recurrenceType === 'custom' ? 'block' : 'none' }};">
                        <label class="form-label">Custom Dates</label>
                        <div id="recurrence_custom_dates_list">
                            @forelse($recurrenceCustomDates as $date)
                                <div class="input-group mb-2 recurrence_custom_date_row">
                                    <input type="date" name="recurrence_custom_dates[]" class="form-control" value="{{ $date }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-danger remove_custom_date_btn">&times;</button>
                                    </div>
                                </div>
                            @empty
                                <div class="input-group mb-2 recurrence_custom_date_row">
                                    <input type="date" name="recurrence_custom_dates[]" class="form-control">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-danger remove_custom_date_btn">&times;</button>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" id="add_custom_date_btn">+ Add more</button>
                        @error('recurrence_custom_dates') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div id="recurrence_schedule_bounds_group" style="display: {{ $recurrenceType === 'custom' ? 'none' : 'block' }};">
                        <div class="form-group">
                            <label for="recurrence_end_date" class="form-label">End Date</label>
                            <input type="date" name="recurrence_end_date" id="recurrence_end_date" class="form-control" value="{{ $recurrenceEndDate }}">
                            @error('recurrence_end_date') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="recurrence_count" class="form-label">Occurrences</label>
                            <input type="number" min="1" name="recurrence_count" id="recurrence_count" class="form-control" value="{{ $recurrenceCount }}">
                            <small class="form-text text-muted">Leave blank to continue until end date.</small>
                            @error('recurrence_count') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Session Type --}}
        @php
            $currentSessionType = (int) old('session_type', $external_session->session_type ?? 1);
            $currentAttendeeType = old('attendee_type', $external_session->attendee_type ?? 'schools');
            $isTrainerMode = $currentAttendeeType === 'trainer';
        @endphp
        <div class="form-group">
            <label class="form-label d-block">Session Type</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="session_type" id="session_offline"
                    value="0" {{ $currentSessionType === 0 ? 'checked' : '' }}>
                <label class="form-check-label" for="session_offline">Offline</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="session_type" id="session_online"
                    value="1" {{ $currentSessionType === 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="session_online">Online</label>
            </div>
            @error('session_type') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- Zoom Link Section (visible only for online sessions) --}}
        <div id="zoom_link_section" style="{{ $currentSessionType === 0 ? 'display:none;' : '' }}">
            <div class="form-group">
                <label class="form-label">Zoom Link</label>
                <input type="text" name="zoom_link" id="zoom_link" class="form-control"
                    value="{{ old('zoom_link', $external_session->zoom_link ?? '') }}"
                    pattern="https://(www\.)?zoom\.us/(j|s)/[0-9]+"
                    title="Zoom link must be like https://zoom.us/j/123456789"
                    {{ ($currentSessionType === 1 && !$isTrainerMode) ? 'required' : '' }}>
                @error('zoom_link') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div class="form-group form-check">
                <input type="checkbox" name="send_zoom_link" class="form-check-input" id="send_zoom_link"
                    {{ old('send_zoom_link', $external_session->send_zoom_link ?? !$isTrainerMode) ? 'checked' : '' }}>
                <label class="form-check-label" for="send_zoom_link">Send Zoom Link in Email</label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Agenda</label>
            <textarea name="agenda" class="form-control">{{ old('agenda', $external_session->agenda ?? '') }}</textarea>
            @error('agenda') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- Speaker --}}
        <div class="form-group">
            <label class="form-label">Trainer</label>
            <input type="text" name="speaker" class="form-control"
                value="{{ old('speaker', $external_session->speaker ?? '') }}" required>
            @error('speaker') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- ======================== ATTENDEES ======================== --}}
        @php
            $selectedSchools     = (isset($external_session) && $external_session->attendees !== 'all')
                ? explode(',', $external_session->attendees)
                : [];
            $isAllSchools        = isset($external_session) && $external_session->attendees === 'all';
        @endphp

        <div class="form-group">
            <label class="form-label d-block"><strong>Attendees</strong></label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="attendee_type" id="attendee_schools"
                    value="schools" {{ !$isTrainerMode ? 'checked' : '' }}>
                <label class="form-check-label" for="attendee_schools">Schools</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="attendee_type" id="attendee_trainer"
                    value="trainer" {{ $isTrainerMode ? 'checked' : '' }}>
                <label class="form-check-label" for="attendee_trainer">Trainer</label>
            </div>
            @error('attendee_type') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- All Schools Checkbox --}}
        <div class="form-check mb-2">
            <input type="checkbox" name="all_schools" id="all_schools" class="form-check-input"
                {{ old('all_schools', $isAllSchools) ? 'checked' : '' }}>
            <label class="form-check-label" for="all_schools">All Schools</label>
        </div>

        {{-- Schools Multiselect (shared for both attendee types) --}}
        <div class="form-group">
            <select name="schools[]" multiple class="form-control" id="schools"
                {{ $isAllSchools ? 'disabled' : '' }}>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}"
                        {{ in_array($school->id, old('schools', $selectedSchools)) ? 'selected' : '' }}>
                        {{ $school->school_name }}
                    </option>
                @endforeach
            </select>
            @error('schools') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- Trainers Section (trainer mode only, loaded via AJAX when school is selected) --}}
        @php
            $showTrainersSection = $isTrainerMode && !$isAllSchools && count($selectedSchools) > 0;
            $isAllTrainers       = $isTrainerMode && isset($external_session) && $external_session->trainer_ids === 'all';
        @endphp
        <div class="form-group" id="trainers_section" style="{{ $showTrainersSection ? '' : 'display:none;' }}">
            <label>Select Trainer(s)</label>
            <div class="form-check mb-2">
                <input type="checkbox" name="all_trainers" id="all_trainers" class="form-check-input"
                    {{ old('all_trainers', $isAllTrainers) ? 'checked' : '' }}>
                <label class="form-check-label" for="all_trainers">All Trainers</label>
            </div>
            <select name="trainer_ids[]" id="trainer_ids" class="form-control" multiple
                {{ old('all_trainers', $isAllTrainers) ? 'disabled' : '' }}></select>
            @error('trainer_ids') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- Levels Section (schools mode only) --}}
        @php
            $selectedLevels = (isset($external_session) && !empty($external_session->levels) && $external_session->levels !== 'all')
                ? explode(',', $external_session->levels)
                : [];
            $isAllLevels    = isset($external_session) && $external_session->levels === 'all';
        @endphp
        <div class="form-group" id="levels_section" style="{{ $isTrainerMode ? 'display:none;' : '' }}">
            <label>Select Level(s)</label>
            <div class="form-check mb-2">
                <input type="checkbox" name="all_levels" id="all_levels" class="form-check-input"
                    {{ old('all_levels', $isAllLevels) ? 'checked' : '' }}>
                <label class="form-check-label" for="all_levels">All</label>
            </div>
            <select name="levels[]" id="levels" class="form-control" multiple
                {{ $isAllLevels ? 'disabled' : '' }}>
                @if($primaryLevels->count())
                    <optgroup label="Levels">
                        @foreach($primaryLevels as $level)
                            <option value="{{ $level->id }}"
                                {{ in_array($level->id, old('levels', $selectedLevels ?? [])) ? 'selected' : '' }}>
                                {{ $level->grade }}
                            </option>
                        @endforeach
                    </optgroup>
                @endif
                @if($addonLevels->count())
                    <optgroup label="Content Add-Ons">
                        @foreach($addonLevels as $level)
                            <option value="{{ $level->id }}"
                                {{ in_array($level->id, old('levels', $selectedLevels ?? [])) ? 'selected' : '' }}>
                                {{ $level->grade }}
                            </option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
            @error('levels') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- Batches Section (shared, shown when school is selected) --}}
        @php
            $selectedBatches = (isset($external_session) && !empty($external_session->batches) && $external_session->batches !== 'all')
                ? explode(',', $external_session->batches)
                : [];
            $isAllBatches    = isset($external_session) && $external_session->batches === 'all';
        @endphp
        <div class="form-group" id="batches_section" style="display:none;">
            <label>Select Batch(es)</label>
            <div class="form-check mb-2">
                <input type="checkbox" name="all_batches" id="all_batches" class="form-check-input"
                    {{ old('all_batches', $isAllBatches) ? 'checked' : '' }}>
                <label class="form-check-label" for="all_batches">All</label>
            </div>
            <select name="batches[]" id="batches" class="form-control" multiple
                {{ $isAllBatches ? 'disabled' : '' }}></select>
            @error('batches') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- ======================== EMAIL NOTIFICATIONS ======================== --}}
        @php
            // Both modes: unchecked by default for new sessions
            $defaultNotify    = [];
            $savedNotify      = isset($external_session) && !empty($external_session->notify_recipients)
                ? explode(',', $external_session->notify_recipients)
                : $defaultNotify;
            $notifyRecipients = old('notify_recipients', $savedNotify);
        @endphp
        <div class="form-group mt-3" id="notify_section">
            <label class="form-label d-block"><strong>Send Session Notifications To</strong></label>

            {{-- Trainers — visible for BOTH attendee types --}}
            <div class="form-check">
                <input type="checkbox" name="notify_recipients[]" value="trainer"
                    class="form-check-input" id="notify_trainer"
                    {{ in_array('trainer', $notifyRecipients) ? 'checked' : '' }}>
                <label class="form-check-label" for="notify_trainer">Trainers</label>
            </div>

            {{-- School / Students / All Recipients — visible for Schools attendee type only --}}
            <div id="notify_schools_options" style="{{ $isTrainerMode ? 'display:none;' : '' }}">
                <div class="form-check">
                    <input type="checkbox" name="notify_recipients[]" value="school"
                        class="form-check-input" id="notify_school"
                        {{ in_array('school', $notifyRecipients) ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_school">Schools</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="notify_recipients[]" value="student"
                        class="form-check-input" id="notify_student"
                        {{ in_array('student', $notifyRecipients) ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_student">Students</label>
                </div>
                <div class="form-check mt-1">
                    <input type="checkbox" class="form-check-input" id="notify_all">
                    <label class="form-check-label" for="notify_all"><strong>All Recipients</strong></label>
                </div>
            </div>

            @error('notify_recipients') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('backend.external_session.list') }}" class="btn btn-secondary">Back</a>

        @if(isset($external_session) && $external_session->exists)
            @if($external_session->is_cancelled)
                <a class="btn btn-secondary disabled" aria-disabled="true">Session Cancelled</a>
            @else
                <a href="{{ route('backend.external_session.cancel', $external_session->id) }}"
                    class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to cancel this session?')">
                    Cancel Session
                </a>
            @endif
        @endif
    </div>

    <script>
    (function () {
        // ── DOM refs ──────────────────────────────────────────────────────────────
        const zoomLinkSection    = document.getElementById('zoom_link_section');
        const sendZoomLinkCheckbox = document.getElementById('send_zoom_link');
        const allCheckbox        = document.getElementById('all_schools');
        const selectBox          = document.getElementById('schools');
        const trainersSection    = document.getElementById('trainers_section');
        const trainerSelectBox   = document.getElementById('trainer_ids');
        const allTrainersCheckbox = document.getElementById('all_trainers');
        const levelsSection      = document.getElementById('levels_section');
        const allLevelsCheckbox  = document.getElementById('all_levels');
        const selectLevelBox     = document.getElementById('levels');
        const batchesSection     = document.getElementById('batches_section');
        const allBatchesCheckbox = document.getElementById('all_batches');
        const selectBatchBox     = document.getElementById('batches');
        const notifySchoolsOptions = document.getElementById('notify_schools_options');
        const notifyTrainerCb      = document.getElementById('notify_trainer');
        const notifySchoolCb       = document.getElementById('notify_school');
        const notifyStudentCb      = document.getElementById('notify_student');
        const notifyAllCheckbox    = document.getElementById('notify_all');
        const recurrenceOptions    = document.getElementById('recurrence_options');
        const recurrenceDaysGroup  = document.getElementById('recurrence_days_group');
        const recurrenceIntervalGroup = document.getElementById('recurrence_interval_group');
        const recurrenceIntervalInput = document.getElementById('recurrence_interval');
        const recurrenceCustomDatesGroup = document.getElementById('recurrence_custom_dates_group');
        const recurrenceCustomDatesList  = document.getElementById('recurrence_custom_dates_list');
        const recurrenceScheduleBoundsGroup = document.getElementById('recurrence_schedule_bounds_group');
        const addCustomDateBtn = document.getElementById('add_custom_date_btn');

        // ── Recurrence Type ───────────────────────────────────────────────────────
        document.querySelectorAll('input[name="recurrence_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                recurrenceOptions.style.display          = this.value === 'none' ? 'none' : 'block';
                recurrenceDaysGroup.style.display         = this.value === 'weekly' ? 'block' : 'none';
                recurrenceIntervalGroup.style.display     = this.value === 'daily' ? 'block' : 'none';
                recurrenceCustomDatesGroup.style.display  = this.value === 'custom' ? 'block' : 'none';
                recurrenceScheduleBoundsGroup.style.display = this.value === 'custom' ? 'none' : 'block';
                if (this.value !== 'daily') recurrenceIntervalInput.value = 1;
            });
        });

        // ── Custom Recurrence Dates ──────────────────────────────────────────────
        if (addCustomDateBtn && recurrenceCustomDatesList) {
            addCustomDateBtn.addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'input-group mb-2 recurrence_custom_date_row';
                row.innerHTML = '<input type="date" name="recurrence_custom_dates[]" class="form-control">' +
                    '<div class="input-group-append">' +
                    '<button type="button" class="btn btn-outline-danger remove_custom_date_btn">&times;</button>' +
                    '</div>';
                recurrenceCustomDatesList.appendChild(row);
            });

            recurrenceCustomDatesList.addEventListener('click', function (e) {
                if (!e.target.classList.contains('remove_custom_date_btn')) return;
                const rows = recurrenceCustomDatesList.querySelectorAll('.recurrence_custom_date_row');
                if (rows.length > 1) {
                    e.target.closest('.recurrence_custom_date_row').remove();
                } else {
                    e.target.closest('.recurrence_custom_date_row').querySelector('input').value = '';
                }
            });
        }

        function currentAttendeeType() {
            const r = document.querySelector('input[name="attendee_type"]:checked');
            return r ? r.value : 'schools';
        }

        // ── Session Type ──────────────────────────────────────────────────────────
        const zoomLinkInput = document.getElementById('zoom_link');

        // Zoom link is required only for online sessions with school attendees;
        // trainer sessions may not have a zoom link, so it stays optional there.
        function updateZoomRequirement() {
            var isOnline = document.querySelector('input[name="session_type"]:checked').value === '1';
            zoomLinkInput.required = isOnline && currentAttendeeType() !== 'trainer';
        }

        document.querySelectorAll('input[name="session_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isOnline = this.value === '1';
                zoomLinkSection.style.display = isOnline ? 'block' : 'none';
                if (!isOnline) zoomLinkInput.value = '';
                updateZoomRequirement();
            });
        });

        // ── Attendee Type ─────────────────────────────────────────────────────────
        document.querySelectorAll('input[name="attendee_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (this.value === 'schools') {
                    levelsSection.style.display   = 'block';
                    allLevelsCheckbox.disabled    = false;
                    selectLevelBox.disabled       = allLevelsCheckbox.checked;
                    // Hide trainers section
                    trainersSection.style.display = 'none';
                    trainerSelectBox.innerHTML    = '';
                    trainerSelectBox.disabled     = true;
                    // Show school/student/all-recipients options, reset to unchecked
                    notifySchoolsOptions.style.display = 'block';
                    notifyTrainerCb.checked   = false;
                    notifySchoolCb.checked    = false;
                    notifyStudentCb.checked   = false;
                    notifyAllCheckbox.checked = false;
                    updateZoomRequirement();
                } else {
                    // Trainer mode
                    levelsSection.style.display  = 'none';
                    allLevelsCheckbox.disabled   = true;
                    selectLevelBox.disabled      = true;
                    // Hide school/student/all-recipients options; uncheck all notify
                    // recipients (a hidden checkbox that stays checked still submits
                    // its value, so this must be reset, not just visually hidden)
                    notifySchoolsOptions.style.display = 'none';
                    notifyTrainerCb.checked  = false;
                    notifySchoolCb.checked   = false;
                    notifyStudentCb.checked  = false;
                    notifyAllCheckbox.checked = false;
                    // Reset school selection
                    allCheckbox.checked   = false;
                    selectBox.disabled    = false;
                    Array.from(selectBox.options).forEach(function (o) { o.selected = false; });
                    // Reset trainers and batches
                    trainersSection.style.display = 'none';
                    trainerSelectBox.innerHTML    = '';
                    trainerSelectBox.disabled     = false;
                    allTrainersCheckbox.checked   = false;
                    batchesSection.style.display  = 'none';
                    selectBatchBox.innerHTML      = '';
                    allBatchesCheckbox.disabled   = false;
                    selectBatchBox.disabled       = false;
                    // Zoom link is optional for trainer sessions and the
                    // link is often not set up front, so default the
                    // "send zoom link" checkbox off (still editable).
                    sendZoomLinkCheckbox.checked = false;
                    updateZoomRequirement();
                }
            });
        });

        // ── All Schools Checkbox ──────────────────────────────────────────────────
        allCheckbox.addEventListener('change', function () {
            selectBox.disabled = this.checked;
            if (this.checked) {
                Array.from(selectBox.options).forEach(function (o) { o.selected = false; });
                batchesSection.style.display = 'none';
                allBatchesCheckbox.disabled  = true;
                selectBatchBox.disabled      = true;
                selectBatchBox.innerHTML     = '';
                // Hide trainer list when all schools checked
                if (currentAttendeeType() === 'trainer') {
                    trainersSection.style.display = 'none';
                    trainerSelectBox.innerHTML    = '';
                    trainerSelectBox.disabled     = true;
                }
            } else {
                allBatchesCheckbox.disabled = false;
                var ids = Array.from(selectBox.selectedOptions).map(function (o) { return o.value; });
                if (ids.length > 0) {
                    fetchBatches(ids);
                    if (currentAttendeeType() === 'trainer') {
                        trainersSection.style.display = 'block';
                        trainerSelectBox.disabled     = false;
                        fetchTrainers(ids);
                    }
                }
            }
        });

        // ── Schools Select Change ─────────────────────────────────────────────────
        selectBox.addEventListener('change', function () {
            var ids = Array.from(this.selectedOptions).map(function (o) { return o.value; });
            if (ids.length > 0) {
                batchesSection.style.display = 'block';
                fetchBatches(ids);
                if (currentAttendeeType() === 'trainer') {
                    trainersSection.style.display = 'block';
                    trainerSelectBox.disabled     = false;
                    fetchTrainers(ids);
                }
            } else {
                batchesSection.style.display = 'none';
                selectBatchBox.innerHTML     = '';
                if (currentAttendeeType() === 'trainer') {
                    trainersSection.style.display = 'none';
                    trainerSelectBox.innerHTML    = '';
                }
            }
        });

        // ── All Trainers Checkbox ─────────────────────────────────────────────────
        allTrainersCheckbox.addEventListener('change', function () {
            trainerSelectBox.disabled = this.checked;
            Array.from(trainerSelectBox.options).forEach(function (o) { o.selected = false; });
        });

        // ── All Levels ────────────────────────────────────────────────────────────
        allLevelsCheckbox.addEventListener('change', function () {
            selectLevelBox.disabled = this.checked;
            if (this.checked) {
                Array.from(selectLevelBox.options).forEach(function (o) { o.selected = false; });
            }
        });

        // ── All Batches ───────────────────────────────────────────────────────────
        allBatchesCheckbox.addEventListener('change', function () {
            selectBatchBox.disabled = this.checked;
            if (this.checked) {
                Array.from(selectBatchBox.options).forEach(function (o) { o.selected = false; });
            }
        });

        // ── Notify All Recipients (schools mode only) ─────────────────────────────
        notifyAllCheckbox.addEventListener('change', function () {
            notifyTrainerCb.checked = this.checked;
            notifySchoolCb.checked  = this.checked;
            notifyStudentCb.checked = this.checked;
        });
        [notifyTrainerCb, notifySchoolCb, notifyStudentCb].forEach(function (cb) {
            cb.addEventListener('change', function () {
                notifyAllCheckbox.checked = notifyTrainerCb.checked && notifySchoolCb.checked && notifyStudentCb.checked;
            });
        });

        // ── AJAX: Fetch Batches ───────────────────────────────────────────────────
        function fetchBatches(schoolIds) {
            var prev   = Array.from(selectBatchBox.selectedOptions).map(function (o) { return o.value; });
            var params = new URLSearchParams();
            schoolIds.forEach(function (id) { params.append('school_ids[]', id); });
            selectBatchBox.innerHTML = '<option disabled>Loading...</option>';

            return fetch('{{ route("backend.external_session.batches_by_schools") }}?' + params.toString())
                .then(function (r) { return r.json(); })
                .then(function (batches) {
                    selectBatchBox.innerHTML = '';
                    if (!batches.length) {
                        selectBatchBox.innerHTML = '<option disabled>No batches found</option>';
                        selectBatchBox.disabled  = true;
                        return;
                    }
                    if (!allBatchesCheckbox.checked) selectBatchBox.disabled = false;
                    batches.forEach(function (b) {
                        var opt       = document.createElement('option');
                        opt.value     = b.id;
                        opt.textContent = b.school_name ? b.batch_name + ' (' + b.school_name + ')' : b.batch_name;
                        if (prev.includes(String(b.id))) opt.selected = true;
                        selectBatchBox.appendChild(opt);
                    });
                })
                .catch(function () {
                    selectBatchBox.innerHTML = '<option disabled>Error loading batches</option>';
                });
        }

        // ── AJAX: Fetch Trainers ──────────────────────────────────────────────────
        function fetchTrainers(schoolIds) {
            var prev   = Array.from(trainerSelectBox.selectedOptions).map(function (o) { return o.value; });
            var params = new URLSearchParams();
            schoolIds.forEach(function (id) { params.append('school_ids[]', id); });
            trainerSelectBox.innerHTML = '<option disabled>Loading...</option>';
            // Reset all-trainers checkbox on each reload
            allTrainersCheckbox.checked  = false;
            trainerSelectBox.disabled    = false;

            return fetch('{{ route("backend.external_session.trainers_by_school") }}?' + params.toString())
                .then(function (r) { return r.json(); })
                .then(function (trainers) {
                    trainerSelectBox.innerHTML = '';
                    if (!trainers.length) {
                        trainerSelectBox.innerHTML = '<option disabled>No trainers found for selected school(s)</option>';
                        trainerSelectBox.disabled  = true;
                        return;
                    }
                    trainerSelectBox.disabled = false;
                    trainers.forEach(function (t) {
                        var opt       = document.createElement('option');
                        opt.value     = t.id;
                        opt.textContent = t.school_name ? t.trainer_name + ' (' + t.school_name + ')' : t.trainer_name;
                        if (prev.includes(String(t.id))) opt.selected = true;
                        trainerSelectBox.appendChild(opt);
                    });
                })
                .catch(function () {
                    trainerSelectBox.innerHTML = '<option disabled>Error loading trainers</option>';
                });
        }

        // ── DOMContentLoaded: Initialise edit/validation-error state ─────────────
        document.addEventListener('DOMContentLoaded', function () {
            updateZoomRequirement();

            // Sync "All Recipients" checkbox (only meaningful in schools mode)
            if (currentAttendeeType() === 'schools') {
                notifyAllCheckbox.checked = notifyTrainerCb.checked && notifySchoolCb.checked && notifyStudentCb.checked;
            }

            var isAllSchools  = allCheckbox.checked;
            var attendeeType  = currentAttendeeType();
            var selectedIds   = Array.from(selectBox.selectedOptions).map(function (o) { return o.value; });

            // If trainer mode, disable levels
            if (attendeeType === 'trainer') {
                allLevelsCheckbox.disabled = true;
                selectLevelBox.disabled    = true;
            }

            if (!isAllSchools && selectedIds.length > 0) {
                var preSelectedBatches  = @json(old('batches', $selectedBatches ?? []));
                var preSelectedTrainers = @json(old('trainer_ids', $preSelectedTrainers ?? []));

                // Load & pre-select batches
                batchesSection.style.display = 'block';
                fetchBatches(selectedIds).then(function () {
                    preSelectedBatches.forEach(function (id) {
                        var opt = selectBatchBox.querySelector('option[value="' + id + '"]');
                        if (opt) opt.selected = true;
                    });
                });

                // Load & pre-select trainers (trainer mode only)
                if (attendeeType === 'trainer') {
                    var isAllTrainers = {{ $isAllTrainers ? 'true' : 'false' }};
                    trainersSection.style.display = 'block';
                    trainerSelectBox.disabled     = false;
                    fetchTrainers(selectedIds).then(function () {
                        if (isAllTrainers) {
                            allTrainersCheckbox.checked = true;
                            trainerSelectBox.disabled   = true;
                        } else {
                            preSelectedTrainers.forEach(function (id) {
                                var opt = trainerSelectBox.querySelector('option[value="' + id + '"]');
                                if (opt) opt.selected = true;
                            });
                        }
                    });
                }
            }
        });
    })();
    </script>
</form>