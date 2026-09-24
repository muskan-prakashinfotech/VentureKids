@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   <!-- Main content -->
   <section>
           
      <div class="card">
         <div class="card-header">
            <h3 class="card-title">Workspace</h3>
            <div class="btns-group">
               <a href="{{ URL::previous() }}" class="btn btn-sm btn-warning">
                  <i class="material-icons">west</i>
                  Back
               </a>
            </div>
         </div>

         <div class="ps-timeline-sec card-body bg-grey-work overflow-hidden workspace_timeline">
            <ol class="ps-timeline">
               <li>
                  <div class="img-handler">
                     <img src="{{ asset('image/workspace/Surveys.svg') }}" />
                  </div>
                  <div class="ps">
                     <p>Strength Finder</p>
                  </div>
               </li>
               <li>
                  <div class="img-handler">
                     <img src="{{ asset('image/workspace/Goal_Setting.svg') }}" />
                  </div>
                  <div class="ps">
                     <p>Goal Setting</p>
                  </div>
               </li>
               <li>
                  <div class="img-handler">
                     <img src="{{ asset('image/workspace/Reflection.svg') }}" />
                  </div>
                  <div class="ps">
                     <p>Resources</p>
                  </div>
               </li>
               <li>
                  <div class="img-handler">
                     <img src="{{ asset('image/workspace/Prototype_Tool.svg') }}" />
                  </div>
                  <div class="ps">
                     <p>Prototype Tool</p>
                  </div>
               </li>
            </ol>
         </div>
      </div>

      <!-- Modal -->
      <div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
         <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content custom_modal">
               <div class="modal-header bg-orange-work">
                  <h4 class="modal-title text-white text-center d-block w-100 d-flex flex-wrap align-items-center justify-content-center" id="staticBackdropLabel"> <img class="mr-4" src="{{ asset('image/workspace/awesome-unlock.svg') }}" width="30px" />  Unlock My Workspace Tools</h4>
                  <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button> -->
               </div>
               <div class="modal-body pt-4 pb-4">
                  <h5 class="text-center">Earn 500 points to unlock tools</h5>
               </div>
               <div class="modal-footer text-center d-flex flex-wrap justify-content-around">
               <!-- <button type="button" class="btn btn-secondary btn-orange-outline" data-dismiss="modal">Cancel</button> -->
               <button type="button" class="btn btn-primary btn-orange">Ok <i class="fa fa-arrow-right ms-3 fs-11"></i> </button>
               </div>
            </div>
         </div>
      </div>

   </section>
</div>
<script>
   $(document).ready(function() {  
      $('.main-sidebar').addClass('sidebar-clickable');
      $("#staticBackdrop").modal('show');
   });
</script>
@endsection
