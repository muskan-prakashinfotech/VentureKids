@extends('backend.layouts.app')

@section('content')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <div class="pageTitle">
        <h2>Terms of use & Privacy Policy</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">{{ __('admin.home') }}</a></li>
            <li class="breadcrumb-item active">Terms of use & Privacy Policy</li>
        </ol>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
      <div class="container-fluid p-0">
            @if(session()->has('success'))
                  <div class="alert alert-success" style="text-align: center;">
                      {{ session()->get('success') }}
                  </div>
            @endif
         <div class="card">
          <div class="card-header">
            <h3 class="card-title">User Agreement</h3>
            <a href="{{ route('school.dashboard') }}" class="btn btn-sm btn-warning float-right"> <i  class="material-icons">west</i> Back</a>
          </div>
           <div class="card-body">
              <div class="alert alert-success terms-updated-alert">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                Our user agreement has been upgraded. Please read and accept the terms & conditions.
              </div>
            <form action="{{route('school.save-privacy-police')}}" method="post">
             @csrf
            <!-- <h2><strong>Terms and Conditions</strong></h2> -->
            {!!$terms['setting_value']!!}
              <div class="mb-2 form-check">
                  <input type="checkbox" class="form-check-input" id="exampleCheck1"  value="1" name="termandcondition" <?php if($user['termandcondition']==1){echo "checked";}?>>
                  <label class="form-check-label" for="exampleCheck1">I accept the <a href="#">terms of use</a>  and <a href="#">privacy pollicy</a></label>
                  <div>
                  @error('termandcondition')
                    <strong class="text-danger">{{ $message }}</strong>
                  @enderror
                  </div>
              </div>

              <button type="sumbit" class="btn btn-primary">Confirm and Continue</button>
            </form>
           </div>
         </div>
      </div><!-- /.container-fluid -->

    </section>
    <br/>
  </div>
@endsection
