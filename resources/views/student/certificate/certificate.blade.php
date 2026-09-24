@extends('backend.layouts.app')
@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Certification</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Certification</li>
        </ol>
    </div>


    <!-- Main content -->
    <section>

        @if (session()->has('success'))
        <div class="alert alert-success" style="text-align: center;">
            {{ session()->get('success') }}
        </div>
        @endif
        @if (session()->has('failed'))
        <div class="alert alert-danger" style="text-align: center;">
            {{ session()->get('failed') }}
        </div>
        @endif

        <div class="card">
            <div class="card-body CertificationBanner"></div>
        </div>

        <div class="card">
            <div class="card-body">
                @if(!empty($studentLevelCertificate)) 
                <div class="CustomBody">
                    <div class="row">
                        @foreach($studentLevelCertificate as $gradeId => $certificate)
                            <div class="col-lg-4 col-md-4 col-sm-6 col-12 mb-4">
                                <div class="InnerContentCard h-100">
                                    <div class="Certification-box">
                                        @if($certificate['assessment'])
                                            <img src="{{ asset('image/certificate.jpg') }}">
                                        @else
                                            <img src="{{ asset('image/certificate.jpg') }}">
                                            <img src="{{ asset('image/badge1.png') }}" class="certification-badge">
                                        @endif
                                    </div>
                                    <div class=" p-0">
                                        <div class="p-3 text-center">
                                            <h5 class="card-title text-center w-100 d-block">{{ $certificate['grade_name'] }}</h5>
                                            @if($certificate['assessment'])
                                                <a href="{{ route('student.request-certificate', $gradeId) }}" class="btn btn-primary">Download a Certificate</a> 
                                            @else
                                                <button class="btn btn-primary disabled">Download a Certificate</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="card-title">
                    <h5>No Any Level is Assigned to You yet. Please contact system administrator.</h5>
                </div>
                @endif
            </div>
        </div>
    </section>
</div>

<script>

    // Get all child elements of the parent
    const childElements = document.querySelectorAll('.CustomBody .InnerContentCard');

    // Assign a random color to each child element
    childElements.forEach((child) => {
        child.style.setProperty('--random-color', getRandomColor());
    });
    
    // Function to get a random color from the predefined set
    function getRandomColor() {
        const colorsArray = getComputedStyle(document.documentElement).getPropertyValue('--colors').trim();
        const colors = colorsArray.split(', ');
        const randomIndex = Math.floor(Math.random() * colors.length);
        return colors[randomIndex];
    }


</script>

@endsection