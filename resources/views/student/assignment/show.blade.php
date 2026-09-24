@extends('backend.layouts.app')

@section('content')
   <div class="content-wrapper">
      <!-- Page Title  -->
      <div class="pageTitle">
         <h2>{{ $student_communications->title }}</h2>
         <ol class="breadcrumb">
               <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
               <li class="breadcrumb-item active text-capitalize">Assignment / {{ $student_communications->title }}</li>
         </ol>
      </div>

        <!-- Main content -->
      <section>
         <div class="card">
            <div class="card-header">
           <div>
            @if(!empty($submission)  && empty($resubmitMode)  && $category !== 'self_learning')
               <a href="{{ route('student.assignment.resubmit', array_merge(['student_communications' => $student_communications->id], request()->query())) }}" 
                  class="btn btn-sm btn-warning btn-resubmit">
                  <i class="material-icons">refresh</i> Resubmit Assignment
               </a>
            @endif
           </div>
               <h3 class="card-title"></h3>
               <a href="{{ route('student.assignment', request()->query()) }}" class="btn btn-sm btn-warning">
                  <i class="material-icons">west</i>
                  Back
               </a>
            </div>

            <div class="card-body">

               <div class="row">
                  <div class="col-lg-12">
                     @if (session()->has('success'))
                     <div class="alert alert-success" style="text-align: center;">
                           {{ session()->get('success') }}
                     </div>
                     @endif
                  </div>
               </div>
               @if($category == 'self_learning' && !empty($student_communications->scorm_file))
               <div class="row">
                  <div class="col-12">
                     <iframe  width="100%" height="600" class="col border-0 m-0 p-0 scorm-iframe" id="scormIframe"></iframe>
                  </div>
               </div>
               @elseif (isset($submission) && empty($resubmitMode))
               <div class="maincontent">
                  <div class="row">
                     <div class="col-md-6">
                        <div class="card m-0">
                           <div class="card-body">
                              @if (!empty($submission->file))
                              <iframe src="{{ asset('tenants/' . $submission->file) }}" class="w-100"
                                    style="height: 500px" frameBorder="0"></iframe>
                              @elseif(!empty($submission->link))
                              <iframe src="{{ $submission->link }}" class="w-100" style="height: 500px" frameBorder="0"></iframe>
                              @else
                              <button class="btn btn-primary">Assignment submitted manually!</button>
                              @endif
                           </div>
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="p-3">
                           <div class="card">
                              <div class="card-body">
                                 @if(!empty($submission->feedback))
                                 <p for="exampleInputPassword1" class="font-weight-bold">Trainer Feedback</p>
                                 <div class="assignment-feedback">
                                    <p>
                                    @if(strlen($submission->feedback) > 300)
                                       {!! nl2br(substr($submission->feedback, 0, 300)) !!}<span id="dots-feedback">...</span><span id="more-feedback">{!! nl2br(substr($submission->feedback, 300, strlen($submission->feedback))) !!}</span>
                                    @else 
                                       {!! nl2br($submission->feedback) !!}
                                    @endif
                                    </p>
                                    @if(strlen($submission->feedback) > 300)
                                    <a href="javascript:void(0);" onclick="showFeedback()" id="btn-feedback">Read more</a>
                                    @endif
                                 </div>
                                 <hr/>
                                 @endif
                                 @if(!empty($submission->comment))
                                 <p for="exampleInputPassword1" class="font-weight-bold">Possible Improvement</p>
                                 <div class="assignment-comment">
                                    <p>
                                       @if(strlen($submission->comment) > 300)
                                          {!! nl2br(substr($submission->comment, 0, 300)) !!}<span id="dots-comment">...</span><span id="more-comment">{!! nl2br(substr($submission->comment, 300, strlen($submission->comment))) !!}</span>
                                       @else 
                                          {!! nl2br($submission->comment) !!}
                                       @endif
                                    </p>
                                    @if(strlen($submission->comment) > 300)
                                    <a href="javascript:void(0);" onclick="showComment()" id="btn-comment">Read more</a>
                                    @endif
                                 </div>
                                 <hr/>
                                 @endif
                                 @if(empty($submission->feedback) && empty($submission->comment))
                                 <p for="exampleInputPassword1" class="font-weight-bold">No feedback available!</p>
                                 @endif
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  @else
                  <div class="maincontent">
                     <div class="row align-items-center">
                        <div class="col-lg-5">
                           <div class="card">
                              <div class="card-body bg-light">
                                 <div class="iframeBox">
                                       <iframe @if(!empty($assignmentsfiles)) src="{{ asset($assignmentsfiles->attachment) }}" @endif width="100%" height="500" frameBorder="0"></iframe>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <div class="col-lg-7">
                           <div class="p-3 innerForms">
                              <form action="{{ route('student.assigment.submit', $student_communications->id) }} "method="POST" enctype="multipart/form-data" class="text-center">
                                 @csrf
                                 <div class="row">
                                    <div class="col-lg-12">
                                       <div class="form-group fileuploadDesign m-0">
                                          <label for="uploadfile" class="dottedBox gap-0">Upload File
                                             <p><span class="d-block text-left">Notes:</span></p>
                                             <ul>
                                                <li>Only pdf file is allowed.</li>
                                                <li>File size must be less than 10 MB.</li>
                                                <li>Edit PDF Online <a href=" https://www.sejda.com/pdf-editor" target="_blank">click here</a>.</li>
                                                <li><a href="#" data-toggle="modal" data-target="#worksheet-instructions">How to enter data in the worksheet for submission</a></li>
                                             </ul> 
                                            
                                             <input type="file" name="file" class="form-control-file" id="uploadfile" accept=".pdf" required> 
                                             @error('file')
                                                   <p class="text-danger">{{ $message }}</p>
                                             @enderror
                                          </label>
                                       </div>
                                    </div>
                                    <div class="col-lg-12 mt-4">
                                       <div class="form-group dottedBox">
                                          <label for="exampleFormControlFile2">File link</label>
                                          <input type="url" class="form-control" name="link" placeholder="Enter File link" id="link">
                                          @error('link')
                                             <p class="text-danger">{{ $message }}</p>
                                          @enderror
                                       </div>
                                    </div>

                                    <div class="col-lg-12 mt-4">
                                       <div class="text-center">
                                          <button type="submit" class="text-center btn btn-primary mw-150 btn-assignment-submit">Submit</button>
                                       </div>
                                    </div>
                                 </div>
      
                                 <!-- <a @if(!empty($assignmentsfiles)) href="{{ asset($assignmentsfiles->attachment) }}" @endif target="_blank" class="btn btn-success ThemeBtn" data-dismiss="modal">Download Assignment</a> -->
                              </form>
                           </div>
                        </div>
                     </div>
                  </div>
               @endif
               </div>
            </div>
         </div>
      </section>
        <!-- /.content -->
   </div>
   <div class="modal fade" id="worksheet-instructions" aria-hidden="true">
      <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Instructions for Using Sejda to Complete Your Worksheet Digitally (No Printing Needed)
                  </h5>
               </div>
               <div class="modal-body">
                  To make things easier and more eco-friendly, we're moving away from printed worksheets. Follow these steps to fill out your worksheet digitally using Sejda, and submit it online instead of handing in a printed version.<br><br>
                  <ol>
                     <li>Step 1: Access the Worksheet
                        <ul>
                           <li>Download the worksheet file from the course platform (you will get it digitally, no printed copy will be distributed).</li>
                        </ul>
                     </li>

                     <li>Step 2: Open Sejda
                        <ul>
                           <li>Go to <a href=" https://www.sejda.com/pdf-editor" target="_blank">Sejda</a> in your web browser.</li>
                           <li>Click on <b>Edit a PDF</b> to start editing your worksheet directly in the browser.</li>
                        </ul>
                     </li>
                     <li>Step 3: Upload Your Worksheet
                        <ul>
                           <li>Click the <b>Upload PDF file</b> button.</li>
                           <li>Select the worksheet PDF from your downloads or saved files.</li>
                        </ul>
                     </li>
                     <li>Step 4: Complete the Worksheet
                        <ul>
                           <li>Once the PDF is uploaded, Sejda will let you edit the file.</li>
                           <li>Use the <b>Text</b> tool to type your answers directly into the worksheet. Click on any blank field to enter your response.</li>
                           <li>For any checkboxes or multiple-choice questions, use the <b>Checkmark</b> or <b>Circle</b> tool as needed. No need to print the worksheet at any stage!</li>
                        </ul>
                     </li>
                     <li>Step 5: Save Your Completed Worksheet
                        <ul>
                           <li>After you’ve completed all the questions, click the <b>Apply changes</b> button at the bottom right.</li>
                           <li>This will generate a new version of the PDF with your responses.</li>
                           <li>Download the updated PDF to your device.</li>
                        </ul>
                     </li>
                     <li>Step 6: Submit Your Worksheet
                        <ul>
                           <li>Upload the updated version of your worksheet to the course platform or the submission portal your teacher has set up.</li>
                        </ul>
                     </li>
                  </ol>
               </div>
            </div>
      </div>
   </div>

    <!-- @if (!isset($submission))
        <div class="modal fade" id="instructions" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <input type="hidden" id="assignment_id" name="assignment" value="{{ request()->get('assignment') }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Instructions</h5>
                    </div>
                    @foreach($assignments as $val)
                    @if($val->id == request()->get('assignment'))
                    <div class="modal-body">
                        <h6 id="review">{!! $val->comment !!}</h6>
                    </div>
                    @endif
                    @endforeach
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Submit Assignment</button>


                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#instructions').modal({
                    backdrop: 'static',
                    keyboard: false
                }, 'hide');
            });
        </script>
    @endif -->

   <script>
      $("#uploadfile").on("change", function() {
         var fileExtension = ['pdf'];
         if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            alert("Only pdf file is allowed.");
            $(this).val(''); 
         } else {
            var file = this.files[0];
            var mbSize = Math.round((file.size / 1024));   
            if(mbSize >= 10240) {
               alert("File too Big, please select a file less than 10mb");
               $(this).val(''); 
            }
         } 
      });

      $('#link').on('blur', function() {
         var url = $(this).val();
         if (url != '') {
            try {
               const newUrl = new URL(url);
               return newUrl.protocol === 'http:' || newUrl.protocol === 'https:';
            } catch (err) {
               alert('Invalid URL');
               $(this).val(''); 
               return false;
            }
         }
      });

      $('.btn-assignment-submit').on('click', function() {
         if($("#uploadfile").val().length) {
            $(this).addClass('disable-btn-assignment-submit');
         }
      });
      

      function showFeedback() {
         var dots = document.getElementById("dots-feedback");
         var moreText = document.getElementById("more-feedback");
         var btnText = document.getElementById("btn-feedback");

         if (dots.style.display === "none") {
            dots.style.display = "inline";
            btnText.innerHTML = "Read more";
            moreText.style.display = "none";
         } else {
            dots.style.display = "none";
            btnText.innerHTML = "Read less";
            moreText.style.display = "inline";
         }
      }

      function showComment() {
         var dots = document.getElementById("dots-comment");
         var moreText = document.getElementById("more-comment");
         var btnText = document.getElementById("btn-comment");

         if (dots.style.display === "none") {
            dots.style.display = "inline";
            btnText.innerHTML = "Read more";
            moreText.style.display = "none";
         } else {
            dots.style.display = "none";
            btnText.innerHTML = "Read less";
            moreText.style.display = "inline";
         }
      }
      $('.btn-resubmit').on('click', function(e) {
    e.preventDefault();

    let url = $(this).attr('href');

    if (confirm("Do you really want to resubmit the assignment?")) {
        window.location.href = url; //take student to resubmit page
    }
});
      
   </script>


@if(!empty($student_communications->scorm_file))
<!-- Prettify -->
<script src="{{asset('asset/dist/js/scorm/run_prettify.js')}}"></script>

<script src="{{asset('asset/dist/js/scorm/scorm-again.js')}}"></script>

<script>
    var confetti_sound_path = "{{ asset('asset/dist/audio/great-job-speech.mp3') }}";
</script>

<script src="{{asset('asset/dist/js/confetti.js')}}"></script>

<script>
   var scormFile = '';
   var scormUrl = '';
   var scormCompleted = '{{ (!empty($submission)) ? true : false }}';
   var assignmentId = "{{ $student_communications->id }}";
   var is_allowed = true;

   window.API = new Scorm12API();  

   window.API.on("LMSSetValue.cmi.core.lesson_status", function(CMIElement, value) {
      var lesson_status = window.API.cmi.core.lesson_status;
      var scorm_percentage = window.API.cmi.core.score.raw; 
      if(typeof lesson_status !== "undefined" && (lesson_status == 'completed' || lesson_status == 'passed')) {
         // Call Confetti Animation 
         poof();

         // Store SCORM Completion Reward Points - Motivated Learner 
         if(!scormCompleted) {
            $.ajax({
               url: "{{ route('student.storeScormAssignment') }}",
               headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               data: {
                     assignment_id: assignmentId,
                     scorm_score: scorm_percentage
               },
               method: "POST",
               success: function(response) { 
                  scormCompleted = true; 
               },
               error: function(xhr) {
                  console.error('Error :', xhr.responseText);
               }
            });
         }
      }
   });
   

   $(document).ready(function() {
        var scormFile = "{{$student_communications->scorm_file}}";
        if(scormFile) {
           var scormUrl = `{{ asset('scorm-files/assignment/') }}/${scormFile}/index_lms.html`;
            $("#scormIframe").attr("src", scormUrl);
        }
   });
    
</script>
@endif
@endsection
