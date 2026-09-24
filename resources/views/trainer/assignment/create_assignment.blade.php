@extends('backend.layouts.app')

@section('content')

<link rel="stylesheet" href="{{asset('asset/plugins/dropzone/min/dropzone.min.css')}}">

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="mb-2 row">
          <div class="col-sm-6">
            {{-- <h1 class="m-0">Student Assignment</h1> --}}
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">{{ __('admin/student_communication.home') }}</a></li>
              <li class="breadcrumb-item active">Student Assignment</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->


    <!-- Main content -->
    <section class="content">

    @if(Session::has('message'))
    <div class="alert alert-success">
        {{ Session::get('message') }}
    </div>
    @endif
      <div class="container-fluid">
        <form id="multiAssignment" method="POST" action="{{ route('trainer.store-assignment') }}" enctype="multipart/form-data">
          @csrf
           <div class="card">
            <div class="card-header">
                <h3 class="card-title">Student Assignment</h3>
                <div class="card-tools">
                    <a href="{{ route('trainer.assigment.index') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                </div>
            </div>
             <div class="card-body">
                 <div class="form-group">
                 <label>Assignment Title</label>
                 <input type="text" name="assignment_title" id="" class="form-control" required>
                 @error('assignment_title')
                      <strong class="text-danger">{{ $message }}</strong>
                 @enderror
               </div>
               <div class="form-group">
                   <label for="schoolname">{{ __('admin/student_communication.select_school') }}</label>
                   <select class="form-control" name="school_id" required>
                       <option value="">---Select---</option>
                       @foreach ($school_list as $school)
                           <option value="{{ $school['get_school']['id'] }}">{{ $school['get_school']['school_name'] }}</option>
                       @endforeach
                   </select>
                   @error('school_id')
                       <strong class="text-danger">{{ $message }}</strong>
                   @enderror
               </div>
               <div class="form-group">
                    <label for="schoolname">{{ __('admin/student_communication.select_level') }}</label>
                    <select class="form-control" name="grade_id" id="grade_id" required>
                        <option value="">---Select---</option>
                        @if(isset($filteredLevels['primary']))
                            <optgroup label="Levels">
                            @foreach ($filteredLevels['primary'] as $k => $level)
                                <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                        @if(isset($filteredLevels['add-ons']))
                            <optgroup label="Content Add-Ons">
                            @foreach ($filteredLevels['add-ons'] as $k => $level)
                                <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                    @error('grade_id')
                        <strong class="text-danger">{{ $message }}</strong>
                    @enderror
                </div>
               <div class="form-group">
                 <label>{{ __('admin/student_communication.create_assignment') }}</label>

                  <div id="image_upload" class="dropzone">
                      <div class="dz-message needsclick">
                          <div class="mb-3">
                              <i class="display-4 text-muted mdi mdi-cloud-upload-outline"></i>
                          </div>
                          <h4>{{ __('admin/student_communication.drop_file') }}</h4>
                      </div>
                  </div>

               </div>
               <div class="form-group">
                 <label>{{ __('admin/student_communication.add_comments') }}</label>
                 <textarea class="form-control" name="comment"></textarea>
                 @error('comment')
                    <strong class="text-danger">{{ $message }}</strong>
                 @enderror
               </div>
               <div class="form-group">
                  <label for="upload_icon">Upload Icon</label>
                  <input type="file" class="form-control" id="upload_icon" name="upload_icon" accept="image/*" required>
                  @error('upload_icon')
                      <strong class="text-danger">{{ $message }}</strong>
                  @enderror
              </div>
              <div class="form-group">
                  <label for="display_order">Display Order</label>
                  <input type="number" class="form-control" id="display_order" name="display_order"  min="1">
              </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('admin/student_communication.submit') }}</button>
              </div>
           </div>
        </form>
      </div>
    </section>
  </div>

  <!-- dropzonejs -->
  <script src="{{asset('asset/plugins/dropzone/min/dropzone.min.js')}}"></script>

  <script> 
        $(document).ready(function() {
            $("#upload_icon").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val(''); 
                }
            });
        });

      // Dropzone.options.imageUpload= {
      //   maxFilesize  : 1,
      //   acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf"
      // }

      var myDropzone = new Dropzone("#image_upload", {
          url: "{{ route('backend.multi-assignment') }}",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          parallelUploads: 1,
          uploadMultiple: true,
          acceptedFiles: '.png,.jpg,.jpeg,.doc,.docx,.pdf',
          autoProcessQueue: true
      });

      myDropzone.on("success", (file, response) => {
          // console.log(file);
          // console.log(response);
          $('#multiAssignment').append('<input type="hidden" name="multi_assignment[]" value="'+response+'">');


      });

  </script>

@endsection
