<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="base_url" content="{{ url('/') }}" />
  <title>{{$app_title}}</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Google Fonts: VentureKids brand (Poppins/Quicksand) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('asset/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{asset('asset/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{asset('asset/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
 <!-- DataTables -->
  <link rel="stylesheet" href="{{asset('asset/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('asset/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('asset/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">

  <!-- BS Stepper -->
  <link rel="stylesheet" href="{{asset('asset/plugins/bs-stepper/css/bs-stepper.min.css')}}">

  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('asset/dist/css/adminlte.min.css')}}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{asset('asset/plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
  <link rel="stylesheet" href="{{asset('asset/plugins/fullcalendar/main.css')}}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{asset('asset/plugins/daterangepicker/daterangepicker.css')}}">

  <link rel="stylesheet" href="{{asset('asset/plugins/dropzone/min/dropzone.min.css')}}">
  <!-- Theme style -->
  <!-- summernote -->
  <link rel="stylesheet" href="{{asset('asset/plugins/summernote/summernote-bs4.min.css')}}">
  <link rel="stylesheet" href="{{asset('asset/dist/css/intlTelInput.min.css')}}">
  <link rel="stylesheet" href="{{asset('asset/dist/css/style.css')}}">
  <!-- VentureKids brand theme override -->
  <link rel="stylesheet" href="{{asset('asset/dist/css/venturekids-theme.css')}}?v={{ file_exists(public_path('asset/dist/css/venturekids-theme.css')) ? filemtime(public_path('asset/dist/css/venturekids-theme.css')) : '1' }}">

    @if (!empty(tenant_css()))
        <link rel="stylesheet" href="{{ asset(tenant_css()) }}">
    @endif

  <!-- jQuery -->
  <script src="{{asset('asset/plugins/jquery/jquery.min.js')}}"></script>

  <script src="{{asset('asset/dist/js/countrypicker.js')}}"></script>
  <!-- Switch Alert -->
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  @livewireStyles()

  <style>
    .card-title{
        line-height: 38px !important;
        font-weight: bold !important;
    }
</style>
<!-- Mixpanel Analytics START -->
<script type="text/javascript">
  (function (f, b) { if (!b.__SV) { var e, g, i, h; window.mixpanel = b; b._i = []; b.init = function (e, f, c) { function g(a, d) { var b = d.split("."); 2 == b.length && ((a = a[b[0]]), (d = b[1])); a[d] = function () { a.push([d].concat(Array.prototype.slice.call(arguments, 0))); }; } var a = b; "undefined" !== typeof c ? (a = b[c] = []) : (c = "mixpanel"); a.people = a.people || []; a.toString = function (a) { var d = "mixpanel"; "mixpanel" !== c && (d += "." + c); a || (d += " (stub)"); return d; }; a.people.toString = function () { return a.toString(1) + ".people (stub)"; }; i = "disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking start_batch_senders people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split( " "); for (h = 0; h < i.length; h++) g(a, i[h]); var j = "set set_once union unset remove delete".split(" "); a.get_group = function () { function b(c) { d[c] = function () { call2_args = arguments; call2 = [c].concat(Array.prototype.slice.call(call2_args, 0)); a.push([e, call2]); }; } for ( var d = {}, e = ["get_group"].concat( Array.prototype.slice.call(arguments, 0)), c = 0; c < j.length; c++) b(j[c]); return d; }; b._i.push([e, f, c]); }; b.__SV = 1.2; e = f.createElement("script"); e.type = "text/javascript"; e.async = !0; e.src = "undefined" !== typeof MIXPANEL_CUSTOM_LIB_URL ? MIXPANEL_CUSTOM_LIB_URL : "file:" === f.location.protocol && "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//) ? "https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js" : "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js"; g = f.getElementsByTagName("script")[0]; g.parentNode.insertBefore(e, g); } })(document, window.mixpanel || []);
  
    // Near entry of your product, init Mixpanel
  mixpanel.init('c4216f7bac4e47d1c91527ff1fa73f28', {debug: true, track_pageview: true, persistence: 'localStorage'});

</script>
<!-- Mixpanel Analytics END -->
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  @php
    $loader_path = 'asset/images/logo-icon.png';
    if(!empty(tenant_loader())) {
      $loader_path = tenant_loader();
    }
  @endphp
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{asset_v($loader_path)}}" alt="Loading..." height="60" width="60">
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand NewVershionHeader">


    <a class="toggleBar" data-widget="pushmenu" href="#" role="button">
      <span></span>
      <span></span>
      <span></span>
    </a>

    <div class="UserDetails">
      @if(isset($pageTitle) && $pageTitle!='')
      <h3>Welcome Back, @switch(auth()->user()->group) @case(1) {{ Session::get('admin_name') }} @break @case(2) {{ Session::get('school_name') }} @break @case(3) {{ Session::get('trainer_name') }} @break @case(4) {{ Session::get('student_name') }} @break @default @endswitch</h3>
      <p class="m-0">You're doing great this week, Keep it up!</p>
      @endif
    </div>

    @php
      $profie_pic = asset_v('img/default_image.png');

      switch(auth()->user()->group) {
        case 1:
          $profie_pic = asset_v(Session::get('admin_image'));
          break;
        case 2:
          $school_logo = App\Models\School::select('school_logo', 'tenant_id')->find(Session::get('school_id'));
          if($school_logo && !empty($school_logo->school_logo) && $school_logo->school_logo != 'no_image') {
            $profie_pic = asset_v('image/school/' . $school_logo->school_logo);
            if ($school_logo->tenant_id) {
              $profie_pic = asset('tenants/'.$school_logo->school_logo);
            }
          }
          break;
        case 3:
          $trainer_image = App\Models\Trainer::select('image')->find(Session::get('trainer_id'));
          if($trainer_image && !empty($trainer_image->image) && $trainer_image->image != 'no_image') {
            $profie_pic = asset_v('image/trainer/' . $trainer_image->image);
          }
          break;
        case 4:
          $student_image = App\Models\Students::with('school')->select('school_id', 'image')->find(Session::get('student_id'));
          if($student_image && !empty($student_image->image) && $student_image->image != 'no_image') {
            $profie_pic = asset_v($student_image->image);
            if ($student_image->school->tenant_id) {
              $profie_pic = asset('tenants/'.$student_image->image);
            }
          }
          break;
        default:
      }
    @endphp

    <!-- Right navbar links -->
    <ul class="ml-auto navbar-nav NewVersionRightNav">
        @if(auth()->user()->group == 4 && request()->route()->getName() == 'student.dashboard')
          <li class="nav-item">
              <button type="button" class="btn btn-orange mr-2 how-works-btn" id="howItWorksBtn"
                  data-video-url="{{ optional(App\Models\HowItWorks::latest()->first())->video_path }}">
                    How It Works
              </button>
          </li>
        @endif
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        @if(Session::get('user_group') == 2)
            <a class="nav-link" href="{{ route('school.school-notification') }}">
              <i class="far fa-bell"></i>
              {{--<span class="badge badge-warning navbar-badge Counter">{{$notifications}}</span>--}}
            </a>
        @elseif(Session::get('user_group') == 3)
            <a class="nav-link" href="{{ route('trainer.trainer-notification') }}">
              <i class="far fa-bell"></i>
              {{--<span class="badge badge-warning navbar-badge Counter">{{$notifications}}</span>--}}
            </a>
        @elseif(Session::get('user_group') == 4)
            <a class="nav-link" href="{{ route('student.student-notification') }}">
              <i class="far fa-bell"></i>
              {{--<span class="badge badge-warning navbar-badge Counter">{{$notifications}}</span>--}}
            </a>
        @endif
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
              <img class="h-100 img-fluid img-circle w-100"
                  src="{{ $profie_pic }}" alt="User profile picture">
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <!-- <span class="dropdown-item dropdown-header">15 Notifications</span> -->
          <!-- <div class="dropdown-divider"></div> -->
          @switch(auth()->user()->group)
            @case(4)
              <a class="dropdown-item" href="{{ route('student.profile-photo') }}">
                <i class="c-icon cil-account-logout"></i>&nbsp;@lang('Upload Profile Photo')
              </a>
              <a class="dropdown-item" href="{{ route('student.change-password') }}">
                <i class="c-icon cil-account-logout"></i>&nbsp;@lang('Change Password')
              </a>
            @break

            @default

          @endswitch
          <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="c-icon cil-account-logout"></i>&nbsp;
                    @lang('Logout')
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;"> @csrf </form>
          <!-- <div class="dropdown-divider"></div> -->
        </div>
      </li>
    </ul>
  </nav>
