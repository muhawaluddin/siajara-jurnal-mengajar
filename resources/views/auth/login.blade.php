<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900">
<div class="w-full max-w-md bg-white/95 shadow-2xl rounded-2xl p-8 backdrop-blur">
    <div class="text-center mb-6">
        <img src="{{ asset('logo-addaraen.jpeg') }}" alt="Logo Addaraen" class="mx-auto mb-3 h-16 w-16 rounded-full border border-emerald-500/40 object-cover shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800">Masuk ke {{ config('app.name') }}</h1>
        <p class="text-sm text-slate-500 mt-2">Aplikasi Absensi & Jurnal Mengajar.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg p-4 mb-6">
            <p class="font-semibold">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside space-y-1 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-600">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autofocus
                   class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2.5 text-slate-800 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-600">Password</label>
            <input id="password" name="password" type="password" required
                   class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2.5 text-slate-800 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50" />
        </div>

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="ml-2 text-sm text-slate-600">Ingat saya</span>
            </label>
            <a href="#" class="text-sm text-emerald-600 hover:text-emerald-500">Lupa password?</a>
        </div>

        <button type="submit"
                class="w-full inline-flex justify-center items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-white font-semibold shadow hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">
            Masuk
        </button>
    </form>

    <!-- <p class="mt-6 text-center text-xs text-slate-400">Email demo: <span class="font-semibold text-slate-600">guru@example.com</span> | Password: <span class="font-semibold text-slate-600">password</span></p> -->
</div>
</body>
</html>
