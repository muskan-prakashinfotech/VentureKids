<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot your password</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('asset/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('asset/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('asset/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/dist/css/style.css') }}">
    @if (!empty(tenant_css()))
        <link rel="stylesheet" href="{{ asset(tenant_css()) }}">
    @endif
</head>

<body class="" style='background: url("{{ school_logo_cover_name()['schoolCoverPicturePath'] }}"); background-size: cover;background-position: center;'>
    <section class="Login_Page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="LoginMain">
                        <div class="LoginLogo">
                            <a href="#"><img width="190" src="{{ asset(school_logo_cover_name()['schoolLogoPath']) }}"></a>
                        </div>

                        <div class="LoginForm">
                            <h3 style="font-size: 40px">@lang('Forgot password?')</h3>
                            <p class="text-lead text-center"> Please enter your email here and details instructions will be sent to your email inbox.</p>

                                @include('flash::message')
                                @if (Session::has('status'))
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        <p>
                                            <i class="fas fa-bolt"></i> {{ Session::get('status') }}
                                        </p>

                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <p>
                                            <i class="fas fa-exclamation-triangle"></i> @lang('Please fix the following errors & try again!')
                                        </p>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>

                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif


                            <form role="form" method="POST" action="{{ route('password.email') }}">
                                @csrf
                                <input type="hidden" name="redirectTo" value="{{ request()->redirectTo }}">
                                
                                <div class="mb-3 InputDesign">
                                    <label>Username or Email Address</label>
                                    <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Username or Email" name="email"
                                        value="{{ old('email') }}">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-envelope"></span>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12 text-center my-4">
                                        <button type="submit" class="btn btn-primary ThemeBtn">{{ __('Email Password Reset Link') }}</button>
                                    </div>
                                    <div class="col-12 text-center">
                                        <a href="{{ route('login') }}" class="btn btn-primary ThemeBtn">Back To Login</a>
                                    </div>
                                </div>
                            </form>

                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- jQuery -->
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>
</body>

</html>
