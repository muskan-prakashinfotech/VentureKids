@forelse($projects as $project)
   <div class="projectBox">
      <div class="projectHead">
         <h2>{{ $project->display_title ?: 'Untitled Project' }}</h2>
         <label class="trainer-project-card-check">
            <input
               type="checkbox"
               class="trainer-project-download-checkbox"
               name="project_ids[]"
               value="{{ $project->id }}"
               form="bulkDownloadForm"
            >
         </label>
      </div>
      <div class="description">
         <p class="mb-1"><i class="material-icons align-middle" style="font-size:16px;">person</i> {{ optional($project->student)->name ?: 'Unknown Student' }}</p>
         <p class="mb-0"><i class="material-icons align-middle" style="font-size:16px;">school</i> {{ optional(optional($project->student)->school)->school_name ?: 'No School' }}</p>
      </div>

      <div class="projectFooter">
         <a href="{{ route('trainer.view-submission', $project->id) }}" class="btn btn-warning btn-sm">
               View Details
               <i class="material-icons">east</i>
         </a>
         <span class="trainer-project-status-chip {{ $project->display_status_class ?? 'trainer-project-status-submitted' }}">
            {{ $project->display_status_label ?? 'Submitted' }}
         </span>
      </div>
   </div>
@empty
   @if(!empty($showSchoolPrompt))
      <p>Please select a school to view projects.</p>
   @else
      <p>No submitted projects found.</p>
   @endif
@endforelse
