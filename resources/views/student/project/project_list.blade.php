@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   <!-- Page Title  -->
   <div class="pageTitle">
      <h2>All Projects</h2>
      <ol class="breadcrumb">
         <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
         <li class="breadcrumb-item active">All Projects</li>
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
            <h3 class="card-title">Projects List</h3>
            <a href="{{ route('student.add-project') }}" class="ml-auto btn btn-sm btn-primary"> + Add Projects</a>
         </div>

         <div class="card-body">
            <div class="projectGid">
                  @foreach($projects as $project)
                  <div class="projectBox">
                     <div class="projectHead">
                        <h2>{{ $project->title}}</h2>
                     </div>
                     <div class="description"></div>

                     <div class="projectFooter">
                        <div class="project-status">
                           @if($project->is_publish == 1)
                              @if(in_array($project->project_status, [0,2]))
                                 <button class="btn btn-sm btn-warning">
                                    AWAITING FEEDBACK
                                 </button>
                                 <a href="{{ route('student.view-project', $project->id) }}" class="btn btn-sm btn-primary" >
                                    VIEW PROJECT
                                 </a>
                              @elseif($project->project_status == 1)
                                 <button class="btn btn-sm btn-success">
                                    FEEDBACK RECEIVED
                                 </button>
                                 <a href="{{ route('student.view-project', $project->id) }}" class="btn btn-sm btn-primary">
                                    VIEW PROJECT
                                 </a>
                              @endif
                           @else 
                              <a class="btn btn-sm btn-warning" >
                                    DRAFT SAVED
                                 </a>
                           @endif
                        </div>
                        <div class="proj-edit-del-action">
                           @if($project->is_publish == 0 && $project->project_status == 0)
                              <a href="{{ route('student.edit-project', $project->id) }}" class="btn btn-warning iconBtn">
                                 <i class="fas fa-edit"></i>
                              </a>
                              <a href="javascript:void(0);" class="btn btn-danger iconBtn projectDelete" data-project-id="{{ $project->id }}">
                                 <i class="fas fa-trash"></i>
                              </a>
                           @endif
                        </div>
                     </div>
                  </div>
                  @endforeach
            </div>
         </div>
      </div>

   </section>
</div>

   @if (session('confetti_visible') == 1)
      <script>
        var confetti_sound_path = "{{asset('asset/dist/audio/great-job-speech.mp3')}}";
      </script>
      <script src="{{asset('asset/dist/js/confetti.js')}}"></script>
      <script>
         // Call Confetti Animation 
         poof();
      </script>
   @endif

<script>
      
   jQuery(document).ready(function() {
      $( ".projectDelete" ).on( "click", function() {    
         if(confirm("Are you sure want to Delete this Project?")) {
            $(this).addClass('disabled'); 
            $.ajax({
               url: "{{ route('student.delete-project') }}",
               headers: {
                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               data: {
                  projectId: $(this).attr('data-project-id')
               },
               method: "POST",
               success: function(res) {
                  if(res.success) {
                     alert(res.message || "Project deleted successfully.");
                     window.location.href = "{{ route('student.list-project') }}";
                  } else {
                     alert(res.message || "Failed to delete project.");
                     $(this).removeClass('disabled');
                  }
               },
               error: function(xhr) {
                  alert("An error occurred while deleting the project.");
                  $(this).removeClass('disabled');
               }
            });
         }
      });
   });

</script>

@endsection
