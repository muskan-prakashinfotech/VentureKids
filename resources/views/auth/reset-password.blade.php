<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>

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

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><img src="{{ asset(school_logo_cover_name()['schoolLogoPath']) }}"></a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Reset Password</p>
                @include('flash::message')

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

                <form role="form" method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <!-- redirectTo URL -->
                    <input type="hidden" name="redirectTo" value="{{ request()->redirectTo }}">
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                    <div class="mb-3 input-group">
                        <input type="text" class="form-control" placeholder="Username or Email" name="email"
                            value="{{ old('email') }}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 input-group">
                        <input type="password" class="form-control" id="current-password" placeholder="Password"
                            name="password" value="{{ old('password') }}">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="cursor-pointer fas fa-eye"
                                    onclick="changePasswordType(this, '#current-password')"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 input-group">
                        <input type="password" class="form-control" id="confirmation-password"
                            placeholder="Password Confirmation" name="password_confirmation">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="cursor-pointer fas fa-eye"
                                    onclick="changePasswordType(this, '#confirmation-password')"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit"
                                class="btn btn-primary btn-block">{{ __('Reset Password') }}</button>
                        </div>
                    </div>
                </form>
                <p class="my-1">
                    <a href="{{ route('login') }}">Login</a>
                </p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('asset/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('asset/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('asset/dist/js/adminlte.min.js') }}"></script>
    <script>
        function changePasswordType(ev, id) {
            let current = $(id).attr('type');
            if (current == 'password') {
                $(ev).removeClass('fa-eye');
                $(ev).addClass('fa-eye-slash');
                $(id).attr('type', 'text');
            } else {
                $(ev).addClass('fa-eye');
                $(ev).removeClass('fa-eye-slash');
                $(id).attr('type', 'password');
            }
        }
    </script>
</body>

</html>
