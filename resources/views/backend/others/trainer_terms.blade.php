@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="mb-2 row">
          <div class="col-sm-6">
            <h1 class="m-0">Add Trainers Terms of Use & Privacy Policy</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
              <li class="breadcrumb-item active">Add Trainers Terms of Use & Privacy Policy</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header --> 
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
      <form action="{{ route('backend.updatetrainerterms.updateTrainerTerms') }}" method="POST" enctype="multipart/form-data">
        @csrf
          <div class="row">
             <div class="col-md-12">
              <div class="card card-primary">
                @if(session()->has('success'))
                  <div class="alert alert-success" style="text-align: center;">
                      {{ session()->get('success') }}
                  </div>
                @endif
                <div class="card-header"> 
                </div> 
                <div class="card-body">
                    <div class="form-group">
                      <label for="eventstart">Description</label>
                      <textarea id="compose-textarea" class="form-control" name="trainer_description">{{$trainerterm['setting_value']}}</textarea>
                    </div> 
                </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div> 
              </div> 
           </div> 
          </div>
        </form> 
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  
@endsection