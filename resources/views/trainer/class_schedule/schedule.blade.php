@extends('backend.layouts.app')

@section('content')
   <!-- Content Wrapper. Contains page content -->
   <div class="content-wrapper trainer-class-schedule">
      <!-- Page Title  -->
      <div class="pageTitle">
         <h2>Class Schedule</h2>
         <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Class Schedule</li>
         </ol>
      </div>

      <!-- Main content -->
      <section >
         <div class="row">
            <div class="col-md-12">
               <div class="card">
                  <!-- <div class="card-header">
                     <h3 class="card-title">Schedule Calendar</h3>
                     <a href="{{ URL::previous() }}" class="ml-2 btn btn-sm btn-warning">
                        <i class="material-icons">west</i>
                        Back
                     </a>
                  </div> -->

                  <div class="card-body calendar-body">
                     <!-- THE CALENDAR -->
                     <div id="calendar"></div>
                  </div>
               </div>
            </div>
         </div>
      </section>
   </div>

   <!-- Session Modal -->
   <div class="modal fade trainer-class-schedule" id="sessionModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
         <div class="modal-content" style="border-radius:12px; overflow:hidden;">

            <!-- Tabs Header -->
            <div class="modal-header p-0 border-0">
               <ul class="nav nav-tabs w-100 px-3 pt-3 session-modal" id="sessionTabs">
                  <li class="nav-item">
                     <a class="nav-link active" data-toggle="tab" href="#tabObservation">
                        Observation Report
                     </a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" data-toggle="tab" href="#tabSessionReport">
                        Session Report
                     </a>
                  </li>
                  <li class="ml-auto d-flex align-items-center pr-2">
                     <button type="button" class="close mb-0" data-dismiss="modal">
                        <span aria-hidden="true">×</span>
                     </button>
                  </li>
               </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content session-modal">

               <!-- Observation Tab -->
               <div class="tab-pane fade show active" id="tabObservation">

                  <!-- STEP 1 : Select Student ───────────────────────── -->
                  <div id="obsStep1">
                     <div class="px-4 py-2 d-flex align-items-center" style="background:#f0f4ff; border-bottom:1px solid #e0e8ff;">
                        <span style="color:#5c6bc0; font-size:16px; margin-right:8px;">ℹ</span>
                        <small style="color:#5c6bc0; font-weight:500;">Select a student to record observations</small>
                     </div>
                     <!-- Future-session warning — shown when session hasn't started yet -->
                     <div id="obsSessionWarning" style="display:none; background:#fff8e1; border-bottom:1px solid #ffe082; padding:10px 16px;">
                        <div class="d-flex align-items-center" style="gap:8px;">
                           <span style="font-size:16px; color:#f59e0b; flex-shrink:0;">&#9200;</span>
                           <small style="color:#7d5200; font-weight:500; font-size:12px; line-height:1.4;">
                              Observations can be submitted only after the session has started.
                           </small>
                        </div>
                     </div>
                     <div class="p-3">
                        <div id="studentLoading" style="display:none; text-align:center; padding:20px; font-size:13px; color:#888;">
                           <div class="spinner-border spinner-border-sm mr-2" role="status" style="color:#4F46E5;"></div>
                           Loading students...
                        </div>
                        <div id="studentList" class="row mx-0"></div>
                     </div>
                  </div>

                  <!-- STEP 2 : Select Observation ─────────────────────── -->
                  <div id="obsStep2" style="display:none;">
                     <div id="obsSessionBar2" style="display:none;"></div>
                     <div class="d-flex align-items-center px-3 py-3" style="border-bottom:1px solid #f0f0f0;">
                        <div id="obsStudentInfo2" class="obs-student-info"></div>
                     </div>
                     <div class="px-4 pt-3 pb-2">
                        <div class="font-weight-bold" style="font-size:13px;">Select an observation</div>
                        <small class="text-muted" style="font-size:11px;">Click on the stickers below to record an observation</small>
                        <div id="obsStickersGrid" class="mt-3"></div>
                        <div id="obsSelectedWrap" class="mt-3">
                           <div style="font-size:12px; font-weight:600; color:#666; margin-bottom:6px;">Selected observation</div>
                           <div id="obsSelectedTags" class="d-flex flex-wrap" style="gap:6px; min-height:28px;"></div>
                        </div>
                     </div>
                     <div class="d-flex justify-content-between px-4 py-3" style="border-top:1px solid #f0f0f0;">
                        <button class="btn btn-outline-secondary btn-sm px-4" style="font-size:13px;" onclick="obsGoToStep(1)">Cancel</button>
                        <button id="obsNextBtn" class="btn obs-next-btn px-4" onclick="obsNextToEvidence()"
                                style="background:#4F46E5;color:#fff;border:none;border-radius:6px;font-weight:600;">Next: Add Evidence</button>
                     </div>
                  </div>

                  <!-- STEP 3 : Add Evidence ───────────────────────────── -->
                  <div id="obsStep3" style="display:none;">
                     <div id="obsSessionBar3" style="display:none;"></div>
                     <div class="d-flex align-items-center px-3 py-3" style="border-bottom:1px solid #f0f0f0;">
                        <div id="obsStudentInfo3" class="obs-student-info"></div>
                     </div>
                     <!-- Per-observation evidence cards — rendered dynamically by obsRenderEvidenceCards() -->
                     <div id="obsEvidenceCards" class="px-4 py-3" style="overflow-y:auto;max-height:420px;"></div>
                     <div class="d-flex justify-content-between px-4 py-3" style="border-top:1px solid #f0f0f0;">
                        <button class="btn btn-outline-secondary btn-sm px-4" style="font-size:13px;" onclick="obsGoToStep(1)">Cancel</button>
                        <button id="obsSaveBtn" class="btn obs-save-btn px-4" onclick="obsSaveObservation()"
                                style="background:#4F46E5;color:#fff;border:none;border-radius:6px;font-weight:600;">Submit Observation</button>
                     </div>
                  </div>

                  <!-- STEP 4 : Observations Recorded ──────────────────── -->
                  <div id="obsStep4" style="display:none;">
                     <div id="obsSessionBar4" style="display:none;"></div>
                     <div class="d-flex align-items-center px-3 py-3" style="border-bottom:1px solid #f0f0f0;">
                        <div id="obsStudentInfo4" class="obs-student-info"></div>
                     </div>
                     <div class="px-4 py-3">
                        <div class="font-weight-bold mb-3" style="font-size:14px;">Observations Recorded</div>
                        <div id="obsRecordsList"></div>
                     </div>
                     <div class="d-flex justify-content-end px-4 py-3" style="border-top:1px solid #f0f0f0;">
                        <button class="btn obs-done-btn px-5" onclick="obsGoToStep(1)"
                                style="background:#4F46E5;color:#fff;border:none;border-radius:6px;font-weight:600;">Done</button>
                     </div>
                  </div>

               </div>

               <!-- Session Report Tab -->
               <div class="tab-pane fade" id="tabSessionReport">

                  <!-- Back + Title -->
                  <div class="px-4 pt-3 pb-2 d-flex align-items-center" style="border-bottom:1px solid #f0f0f0;">
                     <span style="font-size:20px; cursor:pointer; margin-right:10px;" onclick="$('#sessionModal').modal('hide')">←</span>
                     <h5 class="mb-0 font-weight-bold">Session Report</h5>
                  </div>

                  <!-- Status message — top, always green -->
                  <div id="reportStatusMsg" class="report-status-msg"></div>

                  <!-- Loading overlay -->
                  <div id="reportLoadingOverlay" class="report-loading-overlay">
                     <div class="spinner-border spinner-border-sm" role="status"></div>
                     <p>Loading ...</p>
                  </div>

                  <!-- Form body -->
                  <div id="reportFormBody">

                     <!-- Session Info -->
                     <div class="px-4 pt-3 pb-2" style="background:#fafafa; border-bottom:1px solid #f0f0f0;">
                        <table class="session-info-table">
                           <tr>
                              <td class="label">School:</td>
                              <td class="value" id="reportSchoolName"></td>
                           </tr>
                           <tr>
                              <td class="label">Date:</td>
                              <td class="value" id="reportDate"></td>
                           </tr>
                           <tr>
                              <td class="label">Time:</td>
                              <td class="value" id="reportTime"></td>
                           </tr>
                        </table>
                     </div>

                     <div class="px-4 py-3">

                        <div class="form-group">
                           <label class="font-weight-bold">1. Upload Session Photos <span style="color:#e74c3c;">*</span></label>
                           <div class="d-flex" style="gap:12px;">

                              <div style="flex:1;">
                                 <div class="upload-box" id="uploadBoxWithout"
                                      onclick="document.getElementById('photoWithout').click()">
                                    <div class="upload-icon">⬆</div>
                                    <div class="upload-title">Class in action (without faces)</div>
                                    <div class="upload-hint">Up to 5 · PNG, JPG, JPEG · Max 10MB each</div>
                                 </div>
                                 <input type="file" id="photoWithout" accept=".png,.jpg,.jpeg" multiple style="display:none;"
                                        onchange="handlePhotoSelect(this, 'listWithout', 'without_faces')">
                                 <ul id="listWithout" class="photo-list"></ul>
                              </div>

                              <div style="flex:1;">
                                 <div class="upload-box" id="uploadBoxWith"
                                      onclick="document.getElementById('photoWith').click()">
                                    <div class="upload-icon">⬆</div>
                                    <div class="upload-title">Class in Action (with Faces)</div>
                                    <div class="upload-hint">Up to 5 · PNG, JPG, JPEG · Max 10MB each</div>
                                 </div>
                                 <input type="file" id="photoWith" accept=".png,.jpg,.jpeg" multiple style="display:none;"
                                        onchange="handlePhotoSelect(this, 'listWith', 'with_faces')">
                                 <ul id="listWith" class="photo-list"></ul>
                              </div>

                           </div>
                        </div>

                        <div class="form-group">
                           <label class="font-weight-bold">
                              2. What was done during the session? <span style="color:#e74c3c;">*</span>
                           </label>
                           <textarea id="sessionSummary" class="form-control" rows="4" maxlength="500"
                                     placeholder="Write a brief summary of the activities and key learnings..."
                                     oninput="document.getElementById('summaryCount').textContent = this.value.length"
                                     style="font-size:13px; resize:none;"></textarea>
                           <div class="char-counter">
                              <span id="summaryCount">0</span>/500
                           </div>
                        </div>

                        <div class="form-group">
                           <label class="font-weight-bold">
                              3. Highlights &amp; Feedback
                           </label>
                           <textarea id="highlightsFeedback" class="form-control" rows="4" maxlength="500"
                                     placeholder="Share session highlights, wins, challenges, and any feedback..."
                                     oninput="document.getElementById('highlightsCount').textContent = this.value.length"
                                     style="font-size:13px; resize:none;"></textarea>
                           <div class="char-counter">
                              <span id="highlightsCount">0</span>/500
                           </div>
                        </div>

                        <div class="form-group">
                           <label class="font-weight-bold">
                              4. Learning Outcome
                           </label>
                           <textarea id="learningOutcome" class="form-control" rows="4" maxlength="500"
                                     placeholder="Describe the key learning outcomes from this session..."
                                     oninput="document.getElementById('learningOutcomeCount').textContent = this.value.length"
                                     style="font-size:13px; resize:none;"></textarea>
                           <div class="char-counter">
                              <span id="learningOutcomeCount">0</span>/500
                           </div>
                        </div>

                        <div class="form-group">
                           <label class="font-weight-bold">
                              5. Skill Focus
                           </label>
                           <textarea id="skillFocus" class="form-control" rows="4" maxlength="500"
                                     placeholder="What skills were focused on or developed during this session..."
                                     oninput="document.getElementById('skillFocusCount').textContent = this.value.length"
                                     style="font-size:13px; resize:none;"></textarea>
                           <div class="char-counter">
                              <span id="skillFocusCount">0</span>/500
                           </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                           <!-- <button type="button" class="btn btn-outline-secondary btn-draft px-4"
                                   id="draftBtn" onclick="submitReport(0)">
                              Save as Draft
                           </button> -->
                           <button type="button" class="btn btn-submit px-5"
                                   id="submitBtn" onclick="submitReport(1)">
                              SUBMIT
                           </button>
                        </div>

                     </div>
                  

                  </div>

               </div>

            </div>

         </div>
      </div>
   </div>


   <script>

      $.ajaxSetup({
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
      });

      var newPhotos       = { without_faces: [], with_faces: [] };
      var deletedPhotoIds = [];
      var currentSession  = {};
      var obsDataLoaded    = false;  
      var reportDataLoaded = false;  

      // ── Loading helpers ───────────────────────────────────────────────────────
      function showLoading() {
         document.getElementById('reportLoadingOverlay').style.display = 'block';
         document.getElementById('reportFormBody').style.display       = 'none';
         document.getElementById('reportStatusMsg').style.display      = 'none';
      }

      function hideLoading() {
         document.getElementById('reportLoadingOverlay').style.display = 'none';
         document.getElementById('reportFormBody').style.display       = 'block';
      }

      // ── Status message ────────────────────────────────────────────────────────
      function showStatusMsg(text) {
         var msg           = document.getElementById('reportStatusMsg');
         msg.textContent   = text;
         msg.style.display = 'block';
      }

      function hideStatusMsg() {
         document.getElementById('reportStatusMsg').style.display = 'none';
      }

      // ── Open modal ────────────────────────────────────────────────────────────
      function openSessionModal(event) {
         currentSession = {
            session_id:    event.extendedProps.session_id,
            session_title: event.extendedProps.session_title || event.title || '',
            schools:       [event.extendedProps.school],
            start_time: event.extendedProps.start_time,
            end_time:   event.extendedProps.end_time,
            occurrence_date: event.extendedProps.occurrence_date, // server-computed Y-m-d — keys reports/observations to this specific date
            startDate:  event.start,   // JS Date — used to gate observation submission
            end_iso:    event.end
                           ? event.end.toISOString()
                           : new Date(event.start.getTime() + 60 * 60 * 1000).toISOString(),
            date:       new Date(event.start).toLocaleDateString('en-GB', {
                           day: 'numeric', month: 'long', year: 'numeric'
                        }),
         };

         obsDataLoaded    = false;
         reportDataLoaded = false;

         resetReportForm();
         obsReset();
         showLoading();

         $('#sessionTabs a[href="#tabSessionReport"]').tab('show');

         $('#sessionModal').one('shown.bs.modal', function() {
            loadSessionReportTabData();
         });

         $('#sessionModal').modal('show');
      }

      function fillSessionInfo(school) {
         document.getElementById('reportSchoolName').textContent = school.name || '—';
         document.getElementById('reportDate').textContent       = currentSession.date;
         document.getElementById('reportTime').textContent       = currentSession.start_time + ' to ' + currentSession.end_time;
      }

      // ── Load existing report ──────────────────────────────────────────────────
      function loadExistingReport() {
         var schoolId = currentSession.schools[0] ? currentSession.schools[0].id : '';
         if (!schoolId || !currentSession.session_id) {
            hideLoading();
            enableFormFields();
            return;
         }


         // Check if session is completed
         var now         = new Date();
         var sessionEnd  = new Date(currentSession.end_iso);
         var isCompleted = now > sessionEnd;

         if (!isCompleted) {
            hideLoading();
            disableFormFields('The session report will be available after the session has been completed.');
            return;
         }

         showLoading();

         var url = '{{ route("trainer.session_report.get", ["sessionId" => "__SID__", "schoolId" => "__SCHID__", "sessionDate" => "__SDATE__"]) }}'
                     .replace('__SID__',   currentSession.session_id)
                     .replace('__SCHID__', schoolId)
                     .replace('__SDATE__', currentSession.occurrence_date);

         jQuery.get(url, function(data) {
            hideLoading();

            if (!data) {
               enableFormFields();
               return;
            }

            var isSubmitted = data.status == 1;

            document.getElementById('sessionSummary').value           = data.session_summary     || '';
            document.getElementById('highlightsFeedback').value       = data.highlights_feedback || '';
            document.getElementById('learningOutcome').value          = data.learning_outcome    || '';
            document.getElementById('skillFocus').value               = data.skill_focus         || '';
            document.getElementById('summaryCount').textContent       = (data.session_summary     || '').length;
            document.getElementById('highlightsCount').textContent    = (data.highlights_feedback || '').length;
            document.getElementById('learningOutcomeCount').textContent = (data.learning_outcome  || '').length;
            document.getElementById('skillFocusCount').textContent    = (data.skill_focus         || '').length;

            renderExistingPhotos(data.photos_without_faces || [], 'listWithout', isSubmitted);
            renderExistingPhotos(data.photos_with_faces    || [], 'listWith',    isSubmitted);

            if (isSubmitted) {
               lockSubmittedForm();
            } else {
               enableFormFields();
            }

         }).fail(function() {
            hideLoading();
            enableFormFields();
         });
      }

      // ── Field state helpers ───────────────────────────────────────────────────
      function disableFormFields(message) {
         document.getElementById('sessionSummary').disabled     = true;
         document.getElementById('highlightsFeedback').disabled = true;
         document.getElementById('learningOutcome').disabled    = true;
         document.getElementById('skillFocus').disabled         = true;
         // document.getElementById('draftBtn').disabled           = true;
         document.getElementById('submitBtn').disabled          = true;
         document.getElementById('uploadBoxWithout').style.pointerEvents = 'none';
         document.getElementById('uploadBoxWithout').style.opacity       = '0.5';
         document.getElementById('uploadBoxWith').style.pointerEvents    = 'none';
         document.getElementById('uploadBoxWith').style.opacity          = '0.5';
         if (message) showStatusMsg(message);
      }

      function enableFormFields() {
         hideStatusMsg();
         document.getElementById('sessionSummary').disabled     = false;
         document.getElementById('highlightsFeedback').disabled = false;
         document.getElementById('learningOutcome').disabled    = false;
         document.getElementById('skillFocus').disabled         = false;
         // document.getElementById('draftBtn').disabled           = false;
         document.getElementById('submitBtn').disabled          = false;
         document.getElementById('uploadBoxWithout').style.pointerEvents = '';
         document.getElementById('uploadBoxWithout').style.opacity       = '';
         document.getElementById('uploadBoxWithout').style.display       = '';
         document.getElementById('uploadBoxWith').style.pointerEvents    = '';
         document.getElementById('uploadBoxWith').style.opacity          = '';
         document.getElementById('uploadBoxWith').style.display          = '';
      }

      function lockSubmittedForm() {
         document.getElementById('sessionSummary').disabled     = true;
         document.getElementById('highlightsFeedback').disabled = true;
         document.getElementById('learningOutcome').disabled    = true;
         document.getElementById('skillFocus').disabled         = true;
         // document.getElementById('draftBtn').disabled           = true;
         document.getElementById('submitBtn').disabled          = true;
         document.getElementById('submitBtn').textContent       = 'SUBMITTED';
         document.getElementById('uploadBoxWithout').style.pointerEvents = 'none';
         document.getElementById('uploadBoxWithout').style.opacity       = '0.6';
         document.getElementById('uploadBoxWith').style.pointerEvents    = 'none';
         document.getElementById('uploadBoxWith').style.opacity          = '0.6';
      }

      function resetReportForm() {
         hideStatusMsg();
         document.getElementById('reportFormBody').style.display        = 'block';
         document.getElementById('sessionSummary').value                = '';
         document.getElementById('highlightsFeedback').value            = '';
         document.getElementById('learningOutcome').value               = '';
         document.getElementById('skillFocus').value                    = '';
         document.getElementById('sessionSummary').disabled             = false;
         document.getElementById('highlightsFeedback').disabled         = false;
         document.getElementById('learningOutcome').disabled            = false;
         document.getElementById('skillFocus').disabled                 = false;
         document.getElementById('summaryCount').textContent            = '0';
         document.getElementById('highlightsCount').textContent         = '0';
         document.getElementById('learningOutcomeCount').textContent    = '0';
         document.getElementById('skillFocusCount').textContent         = '0';
         document.getElementById('photoWithout').value                  = '';
         document.getElementById('photoWith').value                     = '';
         document.getElementById('listWithout').innerHTML               = '';
         document.getElementById('listWith').innerHTML                  = '';
         // document.getElementById('draftBtn').disabled                   = false;
         // document.getElementById('draftBtn').style.display              = '';
         document.getElementById('submitBtn').disabled                  = false;
         document.getElementById('submitBtn').textContent               = 'SUBMIT';
         document.getElementById('uploadBoxWithout').style.display      = '';
         document.getElementById('uploadBoxWith').style.display         = '';
         document.getElementById('uploadBoxWithout').style.pointerEvents = '';
         document.getElementById('uploadBoxWithout').style.opacity      = '';
         document.getElementById('uploadBoxWith').style.pointerEvents   = '';
         document.getElementById('uploadBoxWith').style.opacity         = '';
         newPhotos       = { without_faces: [], with_faces: [] };
         deletedPhotoIds = [];
      }

      // ── Photo handling ────────────────────────────────────────────────────────
      function handlePhotoSelect(input, listId, type) {
         var files      = Array.from(input.files);
         var list       = document.getElementById(listId);
         var newCount   = list.querySelectorAll('li[data-new]').length;
         var savedCount = list.querySelectorAll('li[data-saved]').length;
         var slots      = 5 - (newCount + savedCount);

         if (slots <= 0) {
            alert('Maximum 5 photos allowed.');
            input.value = '';
            return;
         }

         files = files.slice(0, slots);

         var valid = [];
         files.forEach(function(file) {
            var ext  = file.name.split('.').pop().toLowerCase();
            var size = file.size / 1024 / 1024;
            if (!['png', 'jpg', 'jpeg'].includes(ext)) {
               alert(file.name + ' is not a valid format. Use PNG, JPG or JPEG.');
               return;
            }
            if (size > 10) {
               alert(file.name + ' exceeds 10MB limit.');
               return;
            }
            valid.push(file);
         });

         newPhotos[type] = newPhotos[type].concat(valid);

         valid.forEach(function(file) {
            var li = document.createElement('li');
            li.setAttribute('data-new', '1');
            li.innerHTML = '<span class="photo-name">&#128196; ' + file.name + '</span>'
                         + '<span class="photo-remove" onclick="removeNewPhoto(this, \'' + type + '\', \'' + file.name + '\')">&#215;</span>';
            list.appendChild(li);
         });

         input.value = '';
      }

      function removeNewPhoto(el, type, fileName) {
         newPhotos[type] = newPhotos[type].filter(function(f) { return f.name !== fileName; });
         el.parentElement.remove();
      }

      function removeExistingPhoto(el, photoId) {
         deletedPhotoIds.push(photoId);
         el.parentElement.remove();
      }

      function renderExistingPhotos(photos, listId, isSubmitted) {
         var list = document.getElementById(listId);
         list.querySelectorAll('li[data-saved]').forEach(function(li) { li.remove(); });

         photos.forEach(function(photo) {
            var li = document.createElement('li');
            li.setAttribute('data-saved', photo.id);
            var removeBtn = isSubmitted
               ? ''
               : '<span class="photo-remove" onclick="removeExistingPhoto(this, ' + photo.id + ')">&#215;</span>';
            li.innerHTML = '<span class="photo-name">&#128196; ' + photo.original_name + '</span>' + removeBtn;
            list.appendChild(li);
         });
      }

      // ── Submit ────────────────────────────────────────────────────────────────
      function submitReport(status) {
         var schoolId      = currentSession.schools[0] ? currentSession.schools[0].id : '';
         var summary       = document.getElementById('sessionSummary').value.trim();
         var highlights    = document.getElementById('highlightsFeedback').value.trim();
         var learningOut   = document.getElementById('learningOutcome').value.trim();
         var skillFocusVal = document.getElementById('skillFocus').value.trim();
         var withoutCount  = document.getElementById('listWithout').querySelectorAll('li').length;
         var withCount     = document.getElementById('listWith').querySelectorAll('li').length;

         if (!summary) {
            alert('Please fill in what was done during the session.');
            document.getElementById('sessionSummary').focus();
            return;
         }
         if (withoutCount === 0) {
            alert('Please upload at least one photo of class in action (without faces).');
            return;
         }
         if (withCount === 0) {
            alert('Please upload at least one photo of class with faces.');
            return;
         }

         var formData = new FormData();
         formData.append('_token',              '{{ csrf_token() }}');
         formData.append('session_id',          currentSession.session_id);
         formData.append('school_id',           schoolId);
         formData.append('session_date',        currentSession.occurrence_date);
         formData.append('session_summary',     summary);
         formData.append('highlights_feedback', highlights);
         formData.append('learning_outcome',    learningOut);
         formData.append('skill_focus',         skillFocusVal);
         formData.append('status',              status);

         if (deletedPhotoIds.length > 0) {
            formData.append('deleted_photo_ids', deletedPhotoIds.join(','));
         }
         newPhotos['without_faces'].forEach(function(file) {
            formData.append('photos_without_faces[]', file);
         });
         newPhotos['with_faces'].forEach(function(file) {
            formData.append('photos_with_faces[]', file);
         });

         // document.getElementById('draftBtn').disabled  = true;
         document.getElementById('submitBtn').disabled = true;

         jQuery.ajax({
            url:         '{{ route("trainer.session_report.store") }}',
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function(res) {
               newPhotos       = { without_faces: [], with_faces: [] };
               deletedPhotoIds = [];
               showStatusMsg(status == 1 ? 'Report submitted successfully!' : 'Draft saved successfully.');
               setTimeout(function() {
                  $('#sessionModal').modal('hide');
               }, 1200);
            },
            error: function(xhr) {
               // document.getElementById('draftBtn').disabled  = false;
               document.getElementById('submitBtn').disabled = false;
               var errors = xhr.responseJSON;
               if (errors && errors.errors) {
                  var msg = Object.values(errors.errors).flat().join('\n');
                  alert('Validation error:\n' + msg);
               } else {
                  alert('Something went wrong. Please try again.');
               }
            }
         });
      }

      // ── Calendar height ───────────────────────────────────────────────────────
      function getCalenderHeight() {
         var table  = document.querySelector('.fc-scrollgrid-sync-table');
         var frames = document.querySelectorAll('.fc-daygrid-day-frame');
         if (table && frames.length > 0) {
            var frameHeight = (table.offsetHeight / 6) - 7;
            frames.forEach(function(frame) {
               frame.style.height    = frameHeight + 'px';
               frame.style.overflowY = 'auto';
            });
         }
      }

      function setupCalendarAdjustments(calendar) {
         getCalenderHeight();
         var resizeHandler = function() { getCalenderHeight(); };
         window.addEventListener('resize', resizeHandler);
         calendar.setOption('datesSet', function() {
            setTimeout(getCalenderHeight, 10);
         });
         window.addEventListener('beforeunload', function() {
            window.removeEventListener('resize', resizeHandler);
         });
      }

      // ── FullCalendar ──────────────────────────────────────────────────────────
      document.addEventListener('DOMContentLoaded', function () {
         var calendarEl = document.getElementById('calendar');
         var calendar   = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
               left:   'prev,next',
               center: 'title',
               right:  'today'
            },
            initialView:   'dayGridMonth',
            editable:      false,
            selectable:    false,
            businessHours: true,
            dayMaxEvents:  false,
            droppable:     false,
            allDaySlot:    false,

            events: function(fetchInfo, successCallback, failureCallback) {
               jQuery.ajax({
                  type: 'get',
                  url:  "{{ route('trainer.trainer_classSchedule') }}",
                  data: {},
                  success: function(events) {
                     successCallback(JSON.parse(events));
                  }
               });
            },

            eventContent: function(arg) {
               var eventStart = arg.event.start;
               var now        = new Date();
               var eventEnd   = new Date(eventStart.getTime() + 60 * 60 * 1000);
               var isPast     = now > eventEnd;

               var sessionTitle = arg.event.extendedProps.session_title || arg.event.title || '';
               var displayTitle = sessionTitle.length > 20 ? sessionTitle.slice(0, 20) + '...' : sessionTitle;
               var startTime = arg.event.extendedProps.start_time;
               var endTime   = arg.event.extendedProps.end_time;
               var zoomLink  = arg.event.extendedProps.zoom_link || '#';
               var joinBtn   = '';

               if (arg.event.extendedProps.session_type) {
                  var btnAttr = isPast ? 'aria-disabled="true"' : 'href="' + zoomLink + '" target="_blank"';
                  var btnTag  = isPast ? 'span' : 'a';
                  joinBtn = '<' + btnTag + ' class="trainer-schedule-join-btn ' + (isPast ? 'disabled' : 'active') + '" ' + btnAttr + '>Join</' + btnTag + '>';
               }

               return {
                  html: '<div class="trainer-schedule-tile">'
                      +    '<div class="trainer-schedule-session-header">'
                      +       '<div class="trainer-schedule-session-title">' + displayTitle + '</div>'
                      +       '<div class="trainer-schedule-session-meta">'
                      +          '<div class="trainer-schedule-session-time"><i class="far fa-clock"></i><span>' + startTime + '</span></div>'
                      +          joinBtn
                      +       '</div>'
                      +    '</div>'
                      + '</div>'
               };
            },

            eventClick: function(info) {
               var clickedEl = info.jsEvent.target;
               if (
                  clickedEl.classList.contains('trainer-schedule-join-btn') ||
                  clickedEl.classList.contains('active')  ||
                  clickedEl.classList.contains('disabled') ||
                  clickedEl.parentElement.classList.contains('trainer-schedule-join-btn')
               ) {
                  return;
               }
               openSessionModal(info.event);
            }
         });

         setupCalendarAdjustments(calendar);
         calendar.render();

         // ── Tab-based lazy loading ─────────────────────────────────────────
         $('#sessionTabs').on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
            if (!currentSession.session_id) return;
            var target = $(this).attr('href');
            if (target === '#tabObservation')   loadObservationTabData();
            if (target === '#tabSessionReport') loadSessionReportTabData();
         });

         $('#sessionModal').on('hidden.bs.modal', function () {
            $('#sessionTabs a[href="#tabObservation"]').tab('show');
         });
      });

      // ── Observation state ─────────────────────────────────────────────────
      var allStudents = [];
      var obsState    = { step: 1, student: null, _pending: null, selectedObs: [], allObs: [], obsLoaded: false, evidencePhotos: {}, evidenceTexts: {} };

      // ── Session start-time gate ───────────────────────────────────────────
      function isSessionStarted() {
         if (!currentSession || !currentSession.startDate) return true; // safe default
         return new Date() >= currentSession.startDate;
      }

      // ── Tab lazy-load helpers ─────────────────────────────────────────────
      // Called by the shown.bs.tab listener; each runs at most once per session.
      function loadObservationTabData() {
         if (obsDataLoaded) return;
         obsDataLoaded = true;
         loadStudents();
         obsEnsureObsLoaded(function () {}); // pre-fetch stickers in background so step 2 renders instantly
      }

      function loadSessionReportTabData() {
         if (reportDataLoaded) return;
         reportDataLoaded = true;
         fillSessionInfo(currentSession.schools[0] || {});
         loadExistingReport();
      }

      // ── Reset observation flow ────────────────────────────────────────────
      function obsReset() {
         allStudents                = [];
         obsState.step              = 1;
         obsState.student           = null;
         obsState._pending          = null;
         obsState.selectedObs       = [];
         obsState.evidencePhotos    = {};
         obsState.evidenceTexts     = {};
         for (var i = 1; i <= 4; i++) {
            var el = document.getElementById('obsStep' + i);
            if (el) el.style.display = i === 1 ? '' : 'none';
         }
         var footer = document.getElementById('obsStep1Footer');
         if (footer) footer.style.display = 'none';
         var nextBtn = document.getElementById('obsStep1NextBtn');
         if (nextBtn) nextBtn.disabled = true;
         var warning = document.getElementById('obsSessionWarning');
         if (warning) warning.style.display = 'none';
         var list = document.getElementById('studentList');
         if (list) { list.style.opacity = ''; list.style.pointerEvents = ''; }
      }

      // ── Navigate between steps ────────────────────────────────────────────
      function obsGoToStep(step) {
         obsState.step = step;
         for (var i = 1; i <= 4; i++) {
            var el = document.getElementById('obsStep' + i);
            if (el) el.style.display = i === step ? '' : 'none';
         }
         if (step === 2) {
            obsRenderStudentHeader(2);
            obsRenderSessionBar(2);
            obsEnsureObsLoaded(function () { obsRenderStickers(); obsRenderTags(); });
         }
         if (step === 3) {
            obsState.evidencePhotos = {};
            obsState.evidenceTexts  = {};
            obsRenderStudentHeader(3);
            obsRenderSessionBar(3);
            obsRenderEvidenceCards();
            document.getElementById('obsSaveBtn').disabled = false;
         }
         if (step === 4) {
            obsRenderStudentHeader(4);
            obsRenderSessionBar(4);
            obsLoadRecords();
         }
      }

      function obsSetViewAllLink(elId) {
         if (!obsState.student) return;
         var url = '{{ route("trainer.student_view", "__STID__") }}'
                   .replace('__STID__', obsState.student.id);
         var el = document.getElementById(elId);
         if (el) el.href = url;
      }

      // ── Session name label (steps 2, 3 & 4) ──────────────────────────────
      function obsRenderSessionBar(step) {
         var el = document.getElementById('obsSessionBar' + step);
         if (!el) return;
         var title = (currentSession && currentSession.session_title) ? currentSession.session_title : '';
         if (!title) { el.style.display = 'none'; return; }
         el.style.display = '';
         el.innerHTML =
            '<div style="padding:10px 16px 2px;">'
            + '<span style="font-size:12px;font-weight:600;color:#888;">Topic: </span>'
            + '<span style="font-size:13px;font-weight:700;color:#111;">' + title + '</span>'
            + '</div>';
      }

      // ── Student header (steps 2–4) ────────────────────────────────────────
      function obsRenderStudentHeader(step) {
         var el = document.getElementById('obsStudentInfo' + step);
         if (!el || !obsState.student) return;
         var s      = obsState.student;
         var colors = ['#7c6fe0','#f5a623','#2d8cff','#e05c5c','#27ae60','#e67e22','#8e44ad','#16a085'];
         var color  = colors[s.id % colors.length];
         var init   = (s.first_name || 'S').charAt(0).toUpperCase();

         // Always render the coloured initial; overlay the photo on top so broken images fall back gracefully
         var avatarHtml = '<div style="position:relative;width:44px;height:44px;flex-shrink:0;">'
            + '<div style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;'
            + 'font-size:17px;font-weight:700;color:#fff;background:' + color + ';">' + init + '</div>'
            + (s.image_url
               ? '<img src="' + s.image_url + '" style="position:absolute;top:0;left:0;width:44px;height:44px;'
               + 'border-radius:50%;object-fit:cover;" onerror="this.style.display=\'none\';">'
               : '')
            + '</div>';

         el.innerHTML = avatarHtml
            + '<div>'
            +   '<div style="font-weight:700;font-size:13px;color:#222;line-height:1.3;">' + (s.name || s.first_name || '') + '</div>'
            +   (s.batch_name ? '<div style="font-size:11px;color:#999;margin-top:2px;">' + s.batch_name + '</div>' : '')
            + '</div>';
      }

      // ── Load observations list (once) ─────────────────────────────────────
      function obsEnsureObsLoaded(callback) {
         if (obsState.obsLoaded) { callback(); return; }
         $.get('{{ route("trainer.observations.list") }}', function (res) {
            obsState.allObs    = res;
            obsState.obsLoaded = true;
            callback();
         }).fail(function () {
            document.getElementById('obsStickersGrid').innerHTML =
               '<p class="text-danger text-center w-100" style="font-size:13px;">Could not load observations.</p>';
         });
      }

      // ── Render sticker grid (grouped: Skill first, then Mindset) ─────────
      function obsRenderStickers() {
         var grid = document.getElementById('obsStickersGrid');
         if (!grid) return;

         var cardBase = 'position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;'
                     + 'cursor:pointer;padding:10px 6px 8px;border-radius:10px;border:2px solid #e8e8e8;background:#fff;'
                     + 'text-align:center;overflow:hidden;min-width:0;min-height:90px;transition:border-color .15s;';
         var cardSel  = 'position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;'
                     + 'cursor:pointer;padding:10px 6px 8px;border-radius:10px;border:2px solid #4F46E5;background:#F0F0FD;'
                     + 'text-align:center;overflow:hidden;min-width:0;min-height:90px;transition:border-color .15s;';

         function buildCard(obs) {
            var sel   = obsState.selectedObs.some(function (o) { return o.id === obs.id; });
            var style = sel ? cardSel : cardBase;
            return '<div onclick="obsToggle(' + obs.id + ')" style="' + style + '">'
                 + (sel
                    ? '<div style="position:absolute;top:5px;right:5px;width:17px;height:17px;background:#27ae60;'
                    + 'border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:9px;font-weight:900;z-index:2;">&#10003;</div>'
                    : '')
                 + '<img src="' + obs.icon_url + '" alt=""'
                 + ' style="width:52px;height:52px;max-width:52px;max-height:52px;object-fit:contain;display:block;flex-shrink:0;margin-bottom:6px;"'
                 + ' onerror="this.style.opacity=0.3;">'
                 + '<div style="font-size:10px;font-weight:500;color:#444;line-height:1.3;word-break:break-word;">' + obs.name + '</div>'
                 + '</div>';
         }

         var skillObs   = obsState.allObs.filter(function (o) { return o.category === 'skill'; });
         var mindsetObs = obsState.allObs.filter(function (o) { return o.category === 'mindset'; });
         var otherObs   = obsState.allObs.filter(function (o) { return o.category !== 'skill' && o.category !== 'mindset'; });

         var gridStyle = 'display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px;';
         var labelStyle = 'font-size:11px;font-weight:700;color:#5c6bc0;letter-spacing:.5px;text-transform:uppercase;margin-bottom:8px;';

         var html = '';

         if (skillObs.length) {
            html += '<div style="' + labelStyle + '">Skill</div>';
            html += '<div style="' + gridStyle + '">';
            skillObs.forEach(function (obs) { html += buildCard(obs); });
            html += '</div>';
         }

         if (mindsetObs.length) {
            html += '<div style="' + labelStyle + (skillObs.length ? 'margin-top:32px;' : '') + '">Mindset</div>';
            html += '<div style="' + gridStyle + '">';
            mindsetObs.forEach(function (obs) { html += buildCard(obs); });
            html += '</div>';
         }

         if (otherObs.length) {
            html += '<div style="' + gridStyle + (skillObs.length || mindsetObs.length ? 'margin-top:18px;' : '') + '">';
            otherObs.forEach(function (obs) { html += buildCard(obs); });
            html += '</div>';
         }

         grid.innerHTML = html || '<p class="text-muted text-center w-100" style="font-size:13px;">No observations available.</p>';

         var nextBtn = document.getElementById('obsNextBtn');
         if (nextBtn) nextBtn.style.display = obsState.allObs.length ? '' : 'none';
         var selectedWrap = document.getElementById('obsSelectedWrap');
         if (selectedWrap) selectedWrap.style.display = obsState.allObs.length ? '' : 'none';
      }

      // ── Render selected tags ──────────────────────────────────────────────
      function obsRenderTags() {
         var el = document.getElementById('obsSelectedTags');
         if (!el) return;
         if (!obsState.selectedObs.length) { el.innerHTML = ''; return; }
         var html = '';
         obsState.selectedObs.forEach(function (obs) {
            html += '<div style="display:inline-flex;align-items:center;background:#fff8e1;border:1px solid #ffe082;'
                  + 'border-radius:20px;padding:4px 10px 4px 7px;font-size:12px;font-weight:500;color:#555;gap:6px;'
                  + 'overflow:hidden;max-width:100%;flex-shrink:0;">'
                  + '<img src="' + obs.icon_url + '" alt="" style="width:18px;height:18px;max-width:18px;max-height:18px;'
                  + 'object-fit:contain;display:block;flex-shrink:0;">'
                  + '<span style="white-space:nowrap;">' + obs.name + '</span>'
                  + '<span style="cursor:pointer;color:#999;font-size:15px;line-height:1;flex-shrink:0;margin-left:2px;"'
                  + ' onclick="obsRemoveTag(' + obs.id + ')">&#215;</span>'
                  + '</div>';
         });
         el.innerHTML = html;
      }

      function obsToggle(obsId) {
         var idx = obsState.selectedObs.findIndex(function (o) { return o.id === obsId; });
         if (idx >= 0) {
            obsState.selectedObs.splice(idx, 1);
         } else {
            var obs = obsState.allObs.find(function (o) { return o.id === obsId; });
            if (!obs) return;
            var category    = obs.category || null;
            var countInCat  = obsState.selectedObs.filter(function (o) { return o.category === category; }).length;
            if (countInCat >= 3) {
               var label = category === 'skill' ? 'Skill' : (category === 'mindset' ? 'Mindset' : '');
               alert('Maximum 3 ' + (label ? label + ' ' : '') + 'observations can be selected.');
               return;
            }
            obsState.selectedObs.push(obs);
         }
         obsRenderStickers();
         obsRenderTags();
      }

      function obsRemoveTag(obsId) {
         obsState.selectedObs = obsState.selectedObs.filter(function (o) { return o.id !== obsId; });
         obsRenderStickers();
         obsRenderTags();
      }

      function obsNextToEvidence() {
         if (!obsState.selectedObs.length) {
            alert('Please select at least one observation.');
            return;
         }
         obsGoToStep(3);
      }

      // ── Render per-observation evidence cards (step 3) ───────────────────
      function obsRenderEvidenceCards() {
         var container = document.getElementById('obsEvidenceCards');
         if (!container) return;
         var html = '';
         obsState.selectedObs.forEach(function (obs) {
            html += '<div style="border:1px solid #e8e8e8;border-radius:10px;padding:16px;margin-bottom:14px;background:#fff;">'

               // Card header: icon + name + "Change One" button
               + '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #f2f2f2;">'
               +   '<div style="display:flex;align-items:center;gap:10px;">'
               +     '<img src="' + obs.icon_url + '" alt="" style="width:32px;height:32px;max-width:32px;max-height:32px;object-fit:contain;flex-shrink:0;">'
               +     '<div style="font-weight:700;font-size:13px;color:#222;">' + obs.name + '</div>'
               +   '</div>'
               // +   '<button onclick="obsShowChangePicker(' + obs.id + ')" '
               // +     'style="background:none;border:1px solid #e0d5ff;border-radius:20px;padding:3px 12px;font-size:11px;color:#7c6fe0;font-weight:600;cursor:pointer;white-space:nowrap;flex-shrink:0;">'
               // +     'Change'
               // +   '</button>'
               + '</div>'

               // Inline picker (hidden; populated when "Change One" is clicked)
               + '<div id="obsChangePicker_' + obs.id + '" style="display:none;background:#f8f8f8;border-radius:8px;padding:12px;margin-bottom:14px;border:1px solid #eee;"></div>'

               // Evidence textarea
               + '<div style="margin-bottom:12px;">'
               +   '<label style="font-size:12px;font-weight:600;color:#444;display:block;margin-bottom:4px;">'
               +     'Evidence <span style="color:#e74c3c;">*</span>'
               +   '</label>'
               +   '<small style="display:block;font-size:11px;color:#999;margin-bottom:6px;">Write a short note about what the student did.</small>'
               +   '<textarea id="obsNote_' + obs.id + '" class="form-control" rows="3" maxlength="300"'
               +     ' placeholder="E.g., showed creativity by coming up with a unique idea..."'
               +     ' style="font-size:13px;resize:none;"'
               +     ' oninput="document.getElementById(\'obsCnt_' + obs.id + '\').textContent=this.value.length;"></textarea>'
               +   '<div style="font-size:11px;color:#aaa;text-align:right;margin-top:2px;"><span id="obsCnt_' + obs.id + '">0</span>/300</div>'
               + '</div>'

               // Rephrase button + preview
               + '<div style="text-align:right;margin-bottom:8px;">'
               +   '<button type="button" id="obsRephraseBtn_' + obs.id + '" onclick="obsRephrase(' + obs.id + ')" '
               +     'style="background:none;border:1px solid #d0c8ff;border-radius:20px;padding:3px 12px;font-size:11px;color:#7c6fe0;font-weight:600;cursor:pointer;white-space:nowrap;">'
               +     '&#10024; Rephrase professionally'
               +   '</button>'
               + '</div>'
               + '<div id="obsRephrasePreview_' + obs.id + '" style="display:none;margin-bottom:12px;background:#f5f5ff;border:1px solid #d0c8ff;border-radius:8px;padding:10px 12px;">'
               +   '<div style="font-size:11px;font-weight:600;color:#7c6fe0;margin-bottom:5px;">Suggested rephrasing:</div>'
               +   '<div id="obsRephraseText_' + obs.id + '" style="font-size:12px;color:#333;line-height:1.5;"></div>'
               +   '<div style="display:flex;gap:8px;margin-top:8px;">'
               +     '<button type="button" onclick="obsAcceptRephrase(' + obs.id + ')" style="background:#7c6fe0;color:#fff;border:none;border-radius:6px;padding:4px 14px;font-size:11px;font-weight:600;cursor:pointer;">Use this</button>'
               +     '<button type="button" onclick="obsDismissRephrase(' + obs.id + ')" style="background:#fff;color:#666;border:1px solid #ccc;border-radius:6px;padding:4px 14px;font-size:11px;font-weight:600;cursor:pointer;">Keep original</button>'
               +   '</div>'
               + '</div>'

               // Photo upload
               + '<div>'
               +   '<label style="font-size:12px;font-weight:600;color:#444;display:block;margin-bottom:6px;">'
               +     'Photo / Work Sample <span style="font-size:11px;font-weight:400;color:#aaa;">(Optional)</span>'
               +   '</label>'
               +   '<div id="obsUpBox_' + obs.id + '"'
               +     ' onclick="document.getElementById(\'obsPhInp_' + obs.id + '\').click()"'
               +     ' style="border:2px dashed #ccc;border-radius:8px;padding:12px 16px;text-align:center;cursor:pointer;background:#fafafa;">'
               +     '<div style="font-size:20px;color:#7c6fe0;line-height:1;">&#11014;</div>'
               +     '<div style="font-size:12px;color:#666;font-weight:500;margin-top:4px;">Upload photo</div>'
               +   '</div>'
               +   '<input type="file" id="obsPhInp_' + obs.id + '" accept=".png,.jpg,.jpeg" style="display:none;"'
               +     ' onchange="obsHandleEvidencePhoto(' + obs.id + ',this)">'
               +   '<div id="obsPhThumb_' + obs.id + '" class="d-none" style="align-items:center;gap:8px;margin-top:8px;">'
               +     '<img id="obsPhPrev_' + obs.id + '" src="" style="max-height:54px;border-radius:6px;border:1px solid #eee;">'
               +     '<span id="obsPhName_' + obs.id + '" style="font-size:11px;color:#666;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>'
               +     '<span style="cursor:pointer;color:#c0392b;font-size:16px;line-height:1;flex-shrink:0;" onclick="obsRemoveEvidencePhoto(' + obs.id + ')">&#215;</span>'
               +   '</div>'
               + '</div>'
               + '</div>';
         });
         container.innerHTML = html;
      }

      // ── Save/restore evidence state across card re-renders ────────────────
      function obsSaveEvidenceTexts() {
         obsState.selectedObs.forEach(function (obs) {
            var ta = document.getElementById('obsNote_' + obs.id);
            if (ta) obsState.evidenceTexts[obs.id] = ta.value;
         });
      }

      function obsRestoreEvidenceState() {
         // Restore text
         Object.keys(obsState.evidenceTexts).forEach(function (id) {
            var ta = document.getElementById('obsNote_' + id);
            if (!ta) return;
            ta.value = obsState.evidenceTexts[id];
            var cnt = document.getElementById('obsCnt_' + id);
            if (cnt) cnt.textContent = ta.value.length;
         });
         // Restore photo previews
         Object.keys(obsState.evidencePhotos).forEach(function (id) {
            var file = obsState.evidencePhotos[id];
            if (!file) return;
            var upBox = document.getElementById('obsUpBox_' + id);
            var thumb = document.getElementById('obsPhThumb_' + id);
            var prev  = document.getElementById('obsPhPrev_' + id);
            var name  = document.getElementById('obsPhName_' + id);
            if (upBox)  upBox.style.display = 'none';
            if (name)   name.textContent = file.name;
            if (thumb)  { thumb.className = ''; thumb.style.cssText = 'display:flex;align-items:center;gap:8px;margin-top:8px;'; }
            if (prev && !prev.src) {
               var r = new FileReader();
               r.onload = (function (el) { return function (e) { el.src = e.target.result; }; })(prev);
               r.readAsDataURL(file);
            }
         });
      }

      // ── Change One: inline sticker picker inside a card ───────────────────
      function obsShowChangePicker(obsId) {
         var picker = document.getElementById('obsChangePicker_' + obsId);
         if (!picker) return;
         if (picker.style.display !== 'none') { picker.style.display = 'none'; return; }

         // Available = all observations EXCEPT those selected by OTHER cards
         var otherIds = obsState.selectedObs.filter(function (o) { return o.id !== obsId; }).map(function (o) { return o.id; });
         var available = obsState.allObs.filter(function (o) { return otherIds.indexOf(o.id) === -1; });

         var html = '<div style="font-size:11px;color:#777;font-weight:500;margin-bottom:8px;">Select a different observation:</div>'
                  + '<div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:5px;">';
         available.forEach(function (obs) {
            var isCurrent = obs.id === obsId;
            var cardStyle = 'position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;'
                          + 'padding:6px 4px 4px;border-radius:8px;text-align:center;overflow:hidden;min-height:68px;'
                          + (isCurrent ? 'border:2px solid #4F46E5;background:#F0F0FD;' : 'border:1px solid #e8e8e8;background:#fff;');
            html += '<div onclick="obsChangeOne(' + obsId + ',' + obs.id + ')" style="' + cardStyle + '">'
                  + (isCurrent
                     ? '<div style="position:absolute;top:3px;right:3px;width:14px;height:14px;background:#27ae60;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:7px;font-weight:900;">&#10003;</div>'
                     : '')
                  + '<img src="' + obs.icon_url + '" alt="" style="width:34px;height:34px;max-width:34px;max-height:34px;object-fit:contain;display:block;margin-bottom:3px;">'
                  + '<div style="font-size:9px;font-weight:500;color:#444;line-height:1.2;">' + obs.name + '</div>'
                  + '</div>';
         });
         html += '</div>';
         picker.innerHTML = html;
         picker.style.display = 'block';
      }

      function obsChangeOne(oldObsId, newObsId) {
         // Just close if same observation tapped
         if (oldObsId === newObsId) {
            var p = document.getElementById('obsChangePicker_' + oldObsId);
            if (p) p.style.display = 'none';
            return;
         }
         // Preserve existing evidence for other cards
         obsSaveEvidenceTexts();

         // Swap the observation in the list
         var idx = obsState.selectedObs.findIndex(function (o) { return o.id === oldObsId; });
         if (idx < 0) return;
         var newObs = obsState.allObs.find(function (o) { return o.id === newObsId; });
         if (!newObs) return;

         obsState.selectedObs[idx] = newObs;
         // Clear evidence for the replaced slot (keep others intact)
         delete obsState.evidencePhotos[oldObsId];
         delete obsState.evidenceTexts[oldObsId];
         delete obsState.evidencePhotos[newObsId];
         delete obsState.evidenceTexts[newObsId];

         obsRenderEvidenceCards();
         obsRestoreEvidenceState();
      }

      // ── Per-observation photo handlers ────────────────────────────────────
      function obsHandleEvidencePhoto(obsId, input) {
         var file = input.files[0];
         if (!file) return;
         var ext = file.name.split('.').pop().toLowerCase();
         if (!['png','jpg','jpeg'].includes(ext)) {
            alert('Please upload a PNG, JPG, or JPEG file.');
            input.value = ''; return;
         }
         if (file.size > 10 * 1024 * 1024) {
            alert('File exceeds 10 MB limit.');
            input.value = ''; return;
         }
         obsState.evidencePhotos[obsId] = file;
         var reader = new FileReader();
         reader.onload = function (e) {
            document.getElementById('obsPhPrev_' + obsId).src = e.target.result;
            document.getElementById('obsPhName_' + obsId).textContent = file.name;
            var thumb = document.getElementById('obsPhThumb_' + obsId);
            thumb.className = '';
            thumb.style.cssText = 'display:flex;align-items:center;gap:8px;margin-top:8px;';
            document.getElementById('obsUpBox_' + obsId).style.display = 'none';
         };
         reader.readAsDataURL(file);
      }

      function obsRemoveEvidencePhoto(obsId) {
         delete obsState.evidencePhotos[obsId];
         document.getElementById('obsPhInp_' + obsId).value = '';
         var thumb = document.getElementById('obsPhThumb_' + obsId);
         thumb.className = 'd-none';
         thumb.style.display = '';
         document.getElementById('obsUpBox_' + obsId).style.display = '';
      }

      // ── Save observation (one record per observation) ─────────────────────
      function obsSaveObservation() {
         // Validate every evidence field is filled
         for (var k = 0; k < obsState.selectedObs.length; k++) {
            var obs  = obsState.selectedObs[k];
            var note = document.getElementById('obsNote_' + obs.id);
            if (!note || !note.value.trim()) {
               alert('Please add evidence for "' + obs.name + '".');
               if (note) note.focus();
               return;
            }
         }
         var schoolId = currentSession.schools[0] ? currentSession.schools[0].id : '';
         if (!schoolId) { alert('No school found for this session.'); return; }

         var formData = new FormData();
         formData.append('_token',              '{{ csrf_token() }}');
         formData.append('student_id',          obsState.student.id);
         formData.append('school_id',           schoolId);
         formData.append('external_session_id', currentSession.session_id);
         formData.append('session_date',        currentSession.occurrence_date);
         formData.append('obs_count',           obsState.selectedObs.length);

         obsState.selectedObs.forEach(function (obs, i) {
            formData.append('obs_' + i + '_id',   obs.id);
            formData.append('obs_' + i + '_note', document.getElementById('obsNote_' + obs.id).value.trim());
            if (obsState.evidencePhotos[obs.id]) {
               formData.append('obs_' + i + '_photo', obsState.evidencePhotos[obs.id]);
            }
         });

         document.getElementById('obsSaveBtn').disabled = true;

         $.ajax({
            url:         '{{ route("trainer.student_observation.store") }}',
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function () {
               markStudentObserved(obsState.student.id);
               obsGoToStep(1);
            },
            error: function (xhr) {
               document.getElementById('obsSaveBtn').disabled = false;
               var errors = xhr.responseJSON;
               if (errors && errors.errors) {
                  alert('Validation error:\n' + Object.values(errors.errors).flat().join('\n'));
               } else {
                  alert('Could not save observation. Please try again.');
               }
            }
         });
      }

      // ── Load & render saved records (step 4) ──────────────────────────────
      function obsLoadRecords() {
         var schoolId = currentSession.schools[0] ? currentSession.schools[0].id : '';
         if (!schoolId || !obsState.student) return;
         var url = '{{ route("trainer.student_observations.get", ["sessionId"=>"__SID__","schoolId"=>"__SCHID__","studentId"=>"__STID__","sessionDate"=>"__SDATE__"]) }}'
                   .replace('__SID__',   currentSession.session_id)
                   .replace('__SCHID__', schoolId)
                   .replace('__STID__',  obsState.student.id)
                   .replace('__SDATE__', currentSession.occurrence_date);
         var list = document.getElementById('obsRecordsList');
         list.innerHTML = '<p class="text-center text-muted" style="font-size:13px;">Loading...</p>';
         $.get(url, function (records) {
            if (!records || !records.length) {
               list.innerHTML = '<p class="text-muted text-center" style="font-size:13px;">No observations recorded yet.</p>';
               return;
            }
            var html = '';
            records.forEach(function (rec) {
               var names     = rec.observations.map(function (o) { return o.name; }).join(', ');
               var firstIcon = rec.observations.length ? rec.observations[0].icon_url : '';

               html += '<div style="border:1px solid #eee;border-radius:10px;padding:14px 14px;margin-bottom:10px;'
                     + 'display:flex;gap:12px;align-items:flex-start;">';

               // Left: observation icon — fully inline-sized to prevent overflow
               html += '<div style="flex-shrink:0;width:38px;height:38px;margin-top:2px;">'
                     + (firstIcon
                        ? '<img src="' + firstIcon + '" alt="" style="width:38px;height:38px;max-width:38px;max-height:38px;object-fit:contain;display:block;" onerror="this.style.opacity=0.3;">'
                        : '')
                     + '</div>';

               // Middle: text body
               html += '<div style="flex:1;min-width:0;">'
                     +   '<div style="font-weight:700;font-size:13px;color:#222;line-height:1.3;">' + names + '</div>'
                     +   '<div style="font-size:11px;color:#aaa;margin-top:3px;">' + (rec.session_date_formatted || rec.created_at || '') + '</div>'
                     +   (rec.short_note
                            ? '<div style="font-size:12px;color:#555;margin-top:6px;word-break:break-word;line-height:1.5;">' + rec.short_note + '</div>'
                            : '')
                     + '</div>';

               // Right: evidence photo
               if (rec.image_url) {
                  html += '<img src="' + rec.image_url + '" alt="evidence"'
                        + ' style="width:72px;height:72px;border-radius:8px;object-fit:cover;flex-shrink:0;">';
               }

               html += '</div>';
            });
            list.innerHTML = html;
         }).fail(function () {
            list.innerHTML = '<p class="text-danger text-center" style="font-size:13px;">Could not load observations.</p>';
         });
      }

      // ── Load students ─────────────────────────────────────────────────────
      function loadStudents() {
         var schoolId = currentSession.schools[0] ? currentSession.schools[0].id : '';
         if (!schoolId || !currentSession.session_id) return;
         $('#studentLoading').show();
         $('#studentList').html('');
         var url = '{{ route("trainer.session.students", ["sessionId" => "__SID__", "schoolId" => "__SCHID__", "sessionDate" => "__SDATE__"]) }}'
                     .replace('__SID__',   currentSession.session_id)
                     .replace('__SCHID__', schoolId)
                     .replace('__SDATE__', currentSession.occurrence_date);

         $.get(url, function (res) {
            allStudents = res;
            $('#studentLoading').hide();
            var avatarColors = ['#7c6fe0','#f5a623','#2d8cff','#e05c5c','#27ae60','#e67e22','#8e44ad','#16a085'];
            var html = '';
            res.forEach(function (student, index) {
               var color   = avatarColors[index % avatarColors.length];
               var initial = student.first_name.charAt(0).toUpperCase();
               var avatarHtml;
               if (student.image_url) {
                  avatarHtml = '<img src="' + student.image_url + '" class="student-avatar" '
                             + 'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">'
                             + '<div class="student-avatar-initial" style="display:none; background:' + color + ';">' + initial + '</div>';
               } else {
                  avatarHtml = '<div class="student-avatar-initial" style="background:' + color + ';">' + initial + '</div>';
               }
               var observedBadge = student.has_observation
                  ? '<span class="student-observed-badge" title="Observation recorded">&#10003;</span>'
                  : '';
               html += '<div class="col-md-3 col-4 mb-3">'
                  +    '<label class="student-card w-100" data-student-id="' + student.id + '" onclick="selectStudent(' + index + ', this)">'
                  +       '<div class="student-box">'
                  +          '<div class="student-avatar-wrap">' + avatarHtml + observedBadge + '</div>'
                  +          '<div class="student-name">' + student.first_name + '</div>'
                  +       '</div>'
                  +    '</label>'
                  + '</div>';
            });
            if (!html) html = '<p class="text-muted text-center w-100 mt-3">No students found.</p>';
            $('#studentList').html(html);

            var started = isSessionStarted();
            var warnEl  = document.getElementById('obsSessionWarning');
            var listEl  = document.getElementById('studentList');

            if (started) {
               if (warnEl)  warnEl.style.display  = 'none';
               if (listEl)  { listEl.style.opacity = ''; listEl.style.pointerEvents = ''; }
               // Footer will become visible once a student is selected (via selectStudent)
            } else {
               // Future session — show warning, dim student list, keep footer hidden
               if (warnEl)  warnEl.style.display  = '';
               if (listEl)  { listEl.style.opacity = '0.45'; listEl.style.pointerEvents = 'none'; }
               // obsStep1Footer stays hidden — no selection or Next is possible
            }
         }).fail(function () {
            $('#studentLoading').hide();
            $('#studentList').html('<p class="text-muted text-center w-100 mt-3">Could not load students.</p>');
         });
      }

      // ── Rephrase anecdote ─────────────────────────────────────────────────
      function obsRephrase(obsId) {
         var ta   = document.getElementById('obsNote_' + obsId);
         var btn  = document.getElementById('obsRephraseBtn_' + obsId);
         var text = ta ? ta.value.trim() : '';
         if (!text) {
            alert('Please write the evidence note first before rephrasing.');
            if (ta) ta.focus();
            return;
         }
         var obs     = obsState.allObs.find(function (o) { return o.id === obsId; });
         var obsName = obs ? obs.name : '';

         btn.disabled   = true;
         btn.textContent = 'Rephrasing...';

         jQuery.ajax({
            url:         '{{ route("trainer.rephrase_anecdote") }}',
            type:        'POST',
            contentType: 'application/json',
            data:        JSON.stringify({ _token: '{{ csrf_token() }}', text: text, observation_name: obsName }),
            success: function (res) {
               btn.disabled  = false;
               btn.innerHTML = '&#10024; Rephrase professionally';
               if (res.rephrased) {
                  document.getElementById('obsRephraseText_' + obsId).textContent = res.rephrased;
                  document.getElementById('obsRephrasePreview_' + obsId).style.display = '';
               }
            },
            error: function () {
               btn.disabled  = false;
               btn.innerHTML = '&#10024; Rephrase professionally';
               alert('Could not rephrase at this time. Please try again.');
            }
         });
      }

      function obsAcceptRephrase(obsId) {
         var ta      = document.getElementById('obsNote_' + obsId);
         var text    = document.getElementById('obsRephraseText_' + obsId).textContent;
         if (ta) {
            ta.value = text;
            var cnt = document.getElementById('obsCnt_' + obsId);
            if (cnt) cnt.textContent = text.length;
         }
         obsDismissRephrase(obsId);
      }

      function obsDismissRephrase(obsId) {
         var preview = document.getElementById('obsRephrasePreview_' + obsId);
         if (preview) preview.style.display = 'none';
      }

      // ── Mark a student's card with the "observation recorded" tick ─────────
      function markStudentObserved(studentId) {
         var student = allStudents.find(function (s) { return s.id === studentId; });
         if (student) student.has_observation = true;

         var card = document.querySelector('#studentList .student-card[data-student-id="' + studentId + '"]');
         if (!card || card.querySelector('.student-observed-badge')) return;
         var wrap = card.querySelector('.student-avatar-wrap');
         if (wrap) wrap.insertAdjacentHTML('beforeend', '<span class="student-observed-badge" title="Observation recorded">&#10003;</span>');
      }

      function selectStudent(index, el) {
         if (!isSessionStarted()) return; // defensive guard (UI is already disabled)
         var student = allStudents[index];
         if (!student) return;
         document.querySelectorAll('#studentList .student-card').forEach(function (c) {
            c.classList.remove('selected');
         });
         if (el) el.classList.add('selected');

         obsState.student     = student;
         obsState._pending    = student;
         obsState.selectedObs = [];

         var schoolId = currentSession.schools[0] ? currentSession.schools[0].id : '';
         if (!schoolId) {
            obsGoToStep(2);
            return;
         }

         var checkUrl = '{{ route("trainer.student_observations.get", ["sessionId"=>"__SID__","schoolId"=>"__SCHID__","studentId"=>"__STID__","sessionDate"=>"__SDATE__"]) }}'
                        .replace('__SID__',   currentSession.session_id)
                        .replace('__SCHID__', schoolId)
                        .replace('__STID__',  student.id)
                        .replace('__SDATE__', currentSession.occurrence_date);

         $.get(checkUrl, function (records) {
            if (obsState.student && obsState.student.id === student.id) {
               obsGoToStep(records && records.length > 0 ? 4 : 2);
            }
         }).fail(function () {
            if (obsState.student && obsState.student.id === student.id) {
               obsGoToStep(2);
            }
         });
      }

   </script>

@endsection
