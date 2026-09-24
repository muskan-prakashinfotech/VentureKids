<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sign In | VentureKids</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="{{ asset('img/favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind (compiled project bundle, JIT-scans this file) -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <style>
        body { font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: 'Quicksand', 'Poppins', ui-sans-serif, sans-serif; }

        @keyframes float-slow {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-14px) rotate(1deg); }
        }
        @keyframes float-med {
            0%, 100% { transform: translateY(0) rotate(3deg); }
            50% { transform: translateY(-10px) rotate(-1deg); }
        }
        @keyframes float-fast {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(20px, -25px) scale(1.08); }
            66% { transform: translate(-15px, 15px) scale(0.95); }
        }
        .animate-float-slow { animation: float-slow 6s ease-in-out infinite; }
        .animate-float-med { animation: float-med 5s ease-in-out infinite; }
        .animate-float-fast { animation: float-fast 4s ease-in-out infinite; }
        .animate-blob { animation: blob 9s ease-in-out infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>

<body class="bg-white">

    <main class="min-h-screen lg:grid lg:grid-cols-2">

        <!-- ===================== LEFT: LOGIN FORM ===================== -->
        <section class="flex items-center justify-center px-6 py-12 sm:px-10 lg:px-16 xl:px-24">
            <div class="w-full max-w-md">

                <!-- Brand -->
                <div class="flex items-center gap-3 mb-10">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-teal-500 shadow-lg shadow-indigo-500/30" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-6 w-6 text-white" fill="none">
                            <path d="M4 20 14 10" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" />
                            <path d="M9 5h10v10" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div class="leading-tight">
                        <p class="font-display text-xl font-extrabold tracking-tight text-slate-900">
                            VENTURE<span class="text-indigo-600">KIDS</span>
                        </p>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-teal-600">
                            Tomorrow&rsquo;s Founders
                        </p>
                    </div>
                </div>

                <!-- Headline -->
                <h1 class="font-display text-3xl font-bold text-slate-900 sm:text-4xl">
                    Welcome Back, Innovator!
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    Sign in to keep building your business journey.
                </p>

                <!-- Session status -->
                @if (session()->has('status'))
                    <div class="mt-6 flex items-start gap-2 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800" role="status">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0l-3.5-3.5a1 1 0 111.4-1.4L8.5 12l6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session()->get('status') }}</span>
                    </div>
                @endif

                <!-- Validation errors -->
                @if (isset($errors) && $errors->any())
                    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form -->
                <form class="mt-8 space-y-5" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf
                    <input type="hidden" name="redirectTo" value="{{ request()->redirectTo }}">

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Username or Email Address
                        </label>
                        <input
                            id="email"
                            type="text"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="username"
                            placeholder="Username or Email Address"
                            required
                            autofocus
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="current-password" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                id="current-password"
                                type="password"
                                name="password"
                                value="{{ old('password') }}"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                required
                                class="block w-full rounded-xl border border-slate-200 px-4 py-3 pr-11 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                            >
                            <button
                                type="button"
                                onclick="togglePasswordVisibility()"
                                aria-label="Show password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-indigo-600 focus:outline-none"
                            >
                                <svg id="icon-eye" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7" />
                                </svg>
                                <svg id="icon-eye-off" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18M10.6 10.7a3 3 0 004.1 4.2M6.6 6.7C4.5 8.1 3 10.2 2.25 12c0 0 3 6.75 9.75 6.75 1.8 0 3.3-.47 4.55-1.17M9.9 5.4A9.8 9.8 0 0112 5.25c6.75 0 9.75 6.75 9.75 6.75-.4.9-1.06 2.02-2 3.13" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember" class="flex items-center gap-2 text-sm text-slate-600">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/40"
                            >
                            Remember me
                        </label>

                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">
                            Forgot Password?
                        </a>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-700 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Sign In
                    </button>
                </form>

                @if (Route::has('register'))
                    <p class="mt-8 text-center text-sm text-slate-500">
                        Not a member?
                        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Join Us</a>
                    </p>
                @endif
            </div>
        </section>

        <!-- ===================== RIGHT: GAMIFIED SHOWCASE ===================== -->
        <section class="relative hidden overflow-hidden bg-gradient-to-br from-indigo-600 via-blue-600 to-teal-500 lg:block" aria-hidden="true">
            <img
                src="{{ asset('img/login-banner-cropped.png') }}"
                alt=""
                class="h-full w-full object-cover"
            >
        </section>

    </main>

    <script>
        function togglePasswordVisibility() {
            var input = document.getElementById('current-password');
            var eye = document.getElementById('icon-eye');
            var eyeOff = document.getElementById('icon-eye-off');
            var btn = event.currentTarget;
            var isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            eye.classList.toggle('hidden', isHidden);
            eyeOff.classList.toggle('hidden', !isHidden);
            btn.setAttribute('aria-pressed', String(isHidden));
            btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        }
    </script>

</body>

</html>
