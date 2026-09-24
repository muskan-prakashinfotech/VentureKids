@extends('backend.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Profile</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            
                            <div class="edit_profile_wrap">
                                <div class="edit_profile_logo">
                                    @if(isset($school) && $school->school_logo)
                                        @if ($school->tenant_id)
                                            <img src="{{ asset('tenants/'.$school->school_logo) }}">
                                        @else
                                            <img src="{{ asset('image/school/' . $school->school_logo) }}">
                                        @endif
                                    @else
                                        <img src="{{ asset('img/logoscholl.png') }}">
                                    @endif
                                </div>
                                <div class="schoolStatus">
                                    @if(isset($school) && $school->school_name)
                                        <p><strong class="schoolEstablish">School Name -</strong> {{$school->school_name}}</p>
                                    @endif
                                    @if(isset($school) && $school->school_address) 
                                    <p><strong class="schoolEstablish">Address -</strong> {{$school->school_address}}</p>
                                    @endif
                                    @if(isset($school) && $school->year_establish) 
                                    <p><strong class="schoolEstablish">Establish -</strong> {{$school->year_establish}}</p>
                                    @endif
                                </div>
                                <div class="edit_profile_button">
                                    <a href="{{ route('school.profile-edit') }}" class="btn btn-sm btn-primary">
                                        <i class="material-icons">person</i>
                                        Edit Profile
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="profileStatus mb-3">
                                        <strong class="schoolEstablish">School Contact Person Full Name</strong>
                                        @if(isset($school) && $school->principle_name)
                                            <h6>{{$school->principle_name}}</h6>
                                        @endif
                                    </div>

                                    <div class="profileStatus mb-3">
                                        <strong class="schoolEstablish">Email ID</strong>
                                        @if(isset($school) && $school->user->email)
                                            <h6>{{$school->user->email}}</h6>
                                        @endif
                                    </div>

                                    <div class="profileStatus mb-3">
                                        <strong class="schoolEstablish">Contact Number</strong>
                                        @if(isset($school) && $school->contact_number)
                                            <h6>{{$school->contact_number}}</h6>
                                        @endif
                                    </div>

                                    <div class="profileStatus mb-3">
                                        <strong class="schoolEstablish">Student Licenses</strong>
                                        @if(isset($school) && $school->students->count())
                                            <h6>{{$school->students->count()}} / {{$school->number_of_student}}</h6>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    @if(isset($school) && $school->incharge_name)
                                        <div class="profileStatus mb-3">
                                            <strong class="schoolEstablish">Activity In charge Name</strong>
                                            <h6>{{$school->incharge_name}}</h6>
                                        </div>
                                    @endif
                                    @if(isset($school) && $school->venturekids_representative)
                                        {{-- <div class="profileStatus mb-3">
                                            <strong class="schoolEstablish">VentureKids Coach</strong>
                                            <h6>{{$school->venturekids_representative}}</h6>
                                        </div> --}}
                                    @endif
                                </div>
                                <div class="col-lg-6">
                                    @if(isset($school) && $school->school_cover_image)
                                        <div class="profile_status_right_img">
                                        @if ($school->tenant_id)
                                            <img src="{{ asset('tenants/' . $school->school_cover_image) }}" class="w-100">
                                        @else
                                            <img src="{{ asset('image/school/cover_image/' . $school->school_cover_image) }}" class="w-100">
                                        @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection