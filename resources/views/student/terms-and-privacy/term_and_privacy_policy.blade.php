@extends('backend.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Terms & Privacy Policy</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Terms & Privacy Policy</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        @if(session()->has('success'))
        <div class="alert alert-success" style="text-align: center;">
            {{ session()->get('success') }}
        </div>
        @endif
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Agreement</h3>
                <a href="{{ route('student.dashboard')}}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    Our user agreement has been updated. Please read our user agreement.
                </div>

                <form action="{{route('student.savetermsandprivacypolicy')}}" method="post">
                    @csrf
                    {!!$terms['setting_value']!!}
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1" value="1"
                            name="termandcondition" <?php if($user['termandcondition']==1){echo "checked";}?>>
                        <label class="form-check-label" for="exampleCheck1">I accept the <a href="#">terms of
                                use</a> and <a href="#">privacy pollicy</a></label>
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
    </section>
</div>
@endsection