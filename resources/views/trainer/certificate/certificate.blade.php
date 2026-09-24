@extends('backend.layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Certification</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
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
                <div class="CustomBody">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-4 col-sm-6 col-12 mb-4">
                            <div class="InnerContentCard h-100">
                                <div class="Certification-box @if($downloadCertificate) trainer-certification-box @endif">
                                    @if($downloadCertificate)
                                    <img src="{{ asset('image/certificate.jpg') }}">
                                    @else
                                    <img src="{{ asset('image/certificate.jpg') }}">
                                    <img src="{{ asset('image/badge2.png') }}" class="certification-badge">
                                    @endif
                                </div>
                                <div class=" p-0">
                                    <div class="p-3 text-center">
                                        @if($downloadCertificate)
                                        <a href="{{ route('trainer.request-certificate') }}" class="btn btn-primary">Download a Certificate</a>
                                        @else
                                            <button class="btn btn-primary disabled">Download a Certificate</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>

    // Get all child elements of the parent
    const childElements = document.querySelectorAll('.CustomBody .InnerContentCard');

    // Assign a random color to each child element
    // childElements.forEach((child) => {
    //     child.style.setProperty('--random-color', getRandomColor());
    // });
    
    // Function to get a random color from the predefined set
    function getRandomColor() {
        const colorsArray = getComputedStyle(document.documentElement).getPropertyValue('--colors').trim();
        const colors = colorsArray.split(', ');
        const randomIndex = Math.floor(Math.random() * colors.length);
        return colors[randomIndex];
    }


</script>

@endsection