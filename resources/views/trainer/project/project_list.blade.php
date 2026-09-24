@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   <!-- Page Title  -->
   <div class="pageTitle">
      <h2>Projects List</h2>
      <ol class="breadcrumb">
         <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
         <li class="breadcrumb-item active">Projects</li>
      </ol>
   </div>

   <!-- Main content -->
   <section>
           
      @if(Session::has('message'))
         <div class="alert alert-success">
            {{ Session::get('message') }}
         </div>
      @endif

      <div class="card">
            <div class="card-header">
               <div class="row w-100">
                  <div class="col-md-3">
                     <select id="filter_school" class="form-control" style="width:100%;">
                        <option value="">-- Select School --</option>
                        @foreach($allocated_school_list->pluck('getSchool')->filter()->unique('id') as $school)
                           <option value="{{ $school->id }}" @if (request()->school_id == $school->id) selected @endif>
                              {{ $school->school_name }}
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-3">
                     <select id="filter_batch" class="form-control" style="width:100%;" data-label="Batch" @if(!request()->school_id) disabled @endif>
                        <option value="">-- Select Batch --</option>
                        @if(isset($batches) && $batches->count())
                           @foreach($batches as $batch)
                              <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>
                                 {{ $batch->batch_name }}
                              </option>
                           @endforeach
                        @endif
                     </select>
                  </div>
                  <div class="col-md-3">
                     <select id="filter_student" class="form-control" style="width:100%;" data-label="Student" @if(!request()->school_id) disabled @endif>
                        <option value="">-- Select Student --</option>
                        @if(isset($students) && $students->count())
                           @foreach($students as $student)
                              <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                 {{ $student->name }}
                              </option>
                           @endforeach
                        @endif
                     </select>
                  </div>
                  <div class="col-md-3">
                     <select id="filter_status" class="form-control" style="width:100%;">
                        <option value="">-- Select Status --</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="feedback_draft" {{ request('status') === 'feedback_draft' ? 'selected' : '' }}>Draft</option>
                        <option value="feedback_published" {{ request('status') === 'feedback_published' ? 'selected' : '' }}>Published</option>
                        <option value="feedback_approved" {{ request('status') === 'feedback_approved' ? 'selected' : '' }}>Approved</option>
                        <option value="feedback_resubmitted" {{ request('status') === 'feedback_resubmitted' ? 'selected' : '' }}>Re-submitted</option>
                     </select>
                  </div>
               </div>
               <div class="trainer-project-toolbar mt-3 d-flex flex-wrap justify-content-end align-items-center">
                  <label class="trainer-project-select-all mr-3 mb-2 mb-md-0" for="selectAllProjects">
                     <input type="checkbox" id="selectAllProjects" @if(!request()->school_id) disabled @endif>
                     <span>Select All</span>
                  </label>
                  <button type="submit" form="bulkDownloadForm" id="bulkDownloadButton" class="btn btn-primary mb-2 mb-md-0" @if(!request()->school_id) disabled @endif>
                     <i class="material-icons">download</i> Download Project
                  </button>
               </div>
            </div>

         <form id="bulkDownloadForm" method="POST" action="{{ route('trainer.download-selected-projects') }}">
            @csrf
         </form>

         <div class="card-body">
            <div class="projectGid">
               @include('trainer.project.partials.project_grid', ['projects' => $projects])
            </div>
         </div>
      </div>
   </section>
</div>

<script>

      var baseListUrl = "{{ route('trainer.list-project') }}";

      function fillSelect(select, items, valueKey, labelKey, selectedValue) {
         select.innerHTML = '<option value="">-- Select ' + select.dataset.label + ' --</option>';
         items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item[valueKey];
            opt.textContent = item[labelKey];
            if (String(item[valueKey]) === String(selectedValue)) {
               opt.selected = true;
            }
            select.appendChild(opt);
         });
      }

      function updateBulkControls() {
         var schoolSelect = document.getElementById('filter_school');
         var selectAll = document.getElementById('selectAllProjects');
         var bulkDownloadButton = document.getElementById('bulkDownloadButton');
         var hasSchool = !!(schoolSelect && schoolSelect.value);
         var hasProjects = document.querySelectorAll('.trainer-project-download-checkbox').length > 0;
         var enabled = hasSchool && hasProjects;

         if (selectAll) {
            selectAll.disabled = !enabled;
            if (!enabled) {
               selectAll.checked = false;
            }
         }

         if (bulkDownloadButton) {
            bulkDownloadButton.disabled = !enabled;
         }
      }

      // Apply filters via AJAX - no full page reload
      function applyFilters() {
         var schoolSelect = document.getElementById('filter_school');
         var batchSelect = document.getElementById('filter_batch');
         var studentSelect = document.getElementById('filter_student');
         var statusSelect = document.getElementById('filter_status');

         var school_id = schoolSelect.value;
         var batch_id = batchSelect.value;
         var student_id = studentSelect.value;
         var status = statusSelect.value;

         // Prevent filtering batch/student without their parent (school) selection
         if (!school_id && batch_id) {
            batchSelect.value = '';
            batch_id = '';
         }
         if (!school_id && student_id) {
            studentSelect.value = '';
            student_id = '';
         }

         var params = [];
         if (school_id) params.push('school_id=' + school_id);
         if (batch_id) params.push('batch_id=' + batch_id);
         if (student_id) params.push('student_id=' + student_id);
         if (status) params.push('status=' + status);

         var url = baseListUrl + (params.length ? '?' + params.join('&') : '');

         fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
               document.querySelector('.projectGid').innerHTML = data.html;
               document.getElementById('selectAllProjects').checked = false;

               fillSelect(batchSelect, data.batches || [], 'id', 'batch_name', batch_id);
               batchSelect.disabled = !school_id;

               fillSelect(studentSelect, data.students || [], 'id', 'name', student_id);
               studentSelect.disabled = !school_id;

               updateBulkControls();
            })
            .catch(function () {
               window.location.href = baseListUrl;
            });
      }

      // Attach listeners
      document.getElementById('filter_school').addEventListener('change', function() {
         document.getElementById('filter_batch').value = '';
         document.getElementById('filter_student').value = '';
         applyFilters();
      });

      document.getElementById('filter_batch').addEventListener('change', applyFilters);

      document.getElementById('filter_student').addEventListener('change', applyFilters);

      document.getElementById('filter_status').addEventListener('change', applyFilters);

      document.getElementById('selectAllProjects').addEventListener('change', function () {
         document.querySelectorAll('.trainer-project-download-checkbox').forEach(function (checkbox) {
            checkbox.checked = this.checked;
         }, this);
      });

      document.addEventListener('change', function (event) {
         if (!event.target.classList.contains('trainer-project-download-checkbox')) {
            return;
         }

         var checkboxes = Array.from(document.querySelectorAll('.trainer-project-download-checkbox'));
         var checkedCount = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
         var selectAll = document.getElementById('selectAllProjects');
         if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
         }

         updateBulkControls();
      });

      document.getElementById('bulkDownloadForm').addEventListener('submit', function (event) {
         var selected = document.querySelectorAll('.trainer-project-download-checkbox:checked');
         if (!selected.length) {
            event.preventDefault();
            alert('Please select at least one project to download.');
         }
      });

      updateBulkControls();

</script>

@endsection
