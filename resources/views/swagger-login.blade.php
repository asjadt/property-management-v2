<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Swagger Login | PropManager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-950 text-slate-100 font-sans antialiased flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-indigo-600 items-center justify-center mb-4 shadow-lg shadow-indigo-900/40">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">API Documentation</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to access Swagger UI</p>
        </div>

        {{-- Card --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">

            @if ($message = Session::get('error'))
            <div class="mb-5 flex items-start gap-3 bg-rose-500/10 border border-rose-500/30 rounded-lg px-4 py-3">
                <svg class="w-4 h-4 text-rose-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-rose-300 text-sm font-medium">{{ $message }}</p>
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-5 bg-rose-500/10 border border-rose-500/30 rounded-lg px-4 py-3">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                    <li class="text-rose-300 text-sm flex items-center gap-2">
                        <span class="w-1 h-1 rounded-full bg-rose-400 shrink-0"></span>
                        {{ $error }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="post" action="{{ url('/swagger-login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Email address
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        required
                        class="w-full px-3.5 py-2.5 rounded-lg bg-slate-800 border border-slate-700
                               text-white placeholder-slate-500 text-sm
                               focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500
                               transition-colors"
                        placeholder="you@example.com"
                    />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Password
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        class="w-full px-3.5 py-2.5 rounded-lg bg-slate-800 border border-slate-700
                               text-white placeholder-slate-500 text-sm
                               focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500
                               transition-colors"
                        placeholder="••••••••"
                    />
                </div>

                <button
                    type="submit"
                    name="login"
                    class="w-full py-2.5 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700
                           text-white font-semibold text-sm transition-colors
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900
                           mt-1"
                >
                    Sign in to Swagger
                </button>
            </form>
        </div>

        <p class="text-center text-slate-600 text-xs mt-6">
            PropManager &mdash; Property Management System
        </p>
    </div>

</body>
</html>
