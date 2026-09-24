<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Forgot Password | VentureKids</title>
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
    </style>
</head>

<body class="bg-white">

    <main class="min-h-screen lg:grid lg:grid-cols-2">

        <!-- ===================== LEFT: FORGOT PASSWORD FORM ===================== -->
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
                    Forgot Your Password?
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    No worries! Enter your username or email and we&rsquo;ll send you a reset link.
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
                <form class="mt-8 space-y-5" method="POST" action="{{ route('password.email') }}" novalidate>
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
                            placeholder="Username or Email"
                            required
                            autofocus
                            class="block w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                        >
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-700 hover:shadow-indigo-500/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Email Password Reset Link
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-500">
                    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">
                        Back To Login
                    </a>
                </p>
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

</body>

</html>
