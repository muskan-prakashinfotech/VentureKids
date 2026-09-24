<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>

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

<body class="" style='background: url("{{ asset(school_logo_cover_name()['schoolCoverPicturePath']) }}"); background-size: cover;background-position: center;'>
    
    <section class="Login_Page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="LoginMain">
                        <div class="LoginLogo">
                            <a href="#"><img width="190" src="{{ asset(school_logo_cover_name()['schoolLogoPath']) }}"></a>
                        </div>

                        <div class="LoginForm">
                            <h3>Sign In</h3>
                            @if (session()->has('status'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('status') }}
                            </div>
                            @endif
                            @if (isset($errors) && $errors->any())
                                <div class="alert alert-danger alert-dismissible fade show StyleModal" role="alert">
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


                            <form role="form" method="POST" action="{{ route('login') }}">
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

                                <div class="mb-3 InputDesign">
                                    <label>Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current-password" placeholder="Password"
                                            name="password" value="{{ old('password') }}">
                                        <div class="input-group-append">
                                            <div class="input-group-text ">
                                                <span class="cursor-pointer fas fa-eye" onclick="changePasswordType(this)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                                        <label class="form-check-label" for="remember">Remember Me</label>
                                    </div>
                                </div>
                                <p class="my-3 text-center">
                                    <a href="{{ route('password.request') }}" class="ForgotPass">Forgot Password ?</a>
                                </p>
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary ThemeBtn">Sign In</button>
                                    </div>
                                </div>
                            </form>

                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="{{ asset('asset/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('asset/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('asset/dist/js/adminlte.min.js') }}"></script>
    <script>
        function changePasswordType(ev) {
            let current = $('#current-password').attr('type');
            if (current == 'password') {
                $(ev).removeClass('fa-eye');
                $(ev).addClass('fa-eye-slash');
                $('#current-password').attr('type', 'text');
            } else {
                $(ev).addClass('fa-eye');
                $(ev).removeClass('fa-eye-slash');
                $('#current-password').attr('type', 'password');
            }
        }
    </script>
</body>

</html>
