<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-slate-100">
<div class="relative isolate overflow-hidden">
    <nav class="max-w-6xl mx-auto flex items-center justify-between px-6 py-6">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo-addaraen.jpeg') }}" alt="Logo Addaraen" class="h-10 w-10 rounded-full border border-emerald-400/80 object-cover shadow-lg">
            <span class="text-lg font-semibold tracking-wide">{{ config('app.name') }}</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="px-5 py-2 rounded-lg bg-emerald-500 text-sm font-semibold hover:bg-emerald-400">Masuk</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 pb-20 pt-10 grid gap-12 lg:grid-cols-2">
        <section class="space-y-6">
            <div>
                <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-3 py-1 text-sm font-medium text-emerald-300">Platform Jurnal & Absensi Pondok Pesantren Addaraen</span>
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold leading-tight">Catat aktivitas mengajar dan absensi siswa dalam satu aplikasi</h1>
            <p class="text-lg text-slate-300">
                Kelola data siswa, pantau kehadiran, dan susun laporan bulanan lengkap dengan export PDF & Excel. Masuk menggunakan akun guru yang telah disediakan.
            </p>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-6 py-3 text-base font-semibold hover:bg-emerald-400">Masuk Sekarang</a>
                <!-- <div class="text-sm text-slate-400">
                    Email demo: <span class="text-slate-200">guru@example.com</span><br>
                    Password: <span class="text-slate-200">password</span>
                </div> -->
            </div>
        </section>
        <section class="relative bg-slate-900/60 border border-slate-700 rounded-3xl p-8 shadow-xl backdrop-blur">
            <h2 class="text-xl font-semibold text-slate-200">Fitur Utama</h2>
            <ul class="mt-6 space-y-4 text-sm text-slate-300">
                <li class="flex items-start gap-3">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <p><strong>Pencatatan Jurnal Mengajar Digital</strong> Guru dapat mencatat mata pelajaran, topik, waktu, dan catatan mengajar langsung di aplikasi — rapi dan terdokumentasi otomatis.</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                    <p><strong>Absensi Siswa Terintegrasi</strong> Kehadiran siswa dicatat langsung dalam satu sistem, dengan status Hadir, Sakit, Izin, atau Alpa. Data langsung terhubung dengan laporan.</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                    <p><strong>Rekap Laporan Bulanan Otomatis</strong> Semua data jurnal mengajar dan absensi direkap otomatis setiap akhir bulan. Laporan dapat diekspor dalam format PDF & Excel untuk memudahkan administrasi.</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-1 h-2.5 w-2.5 rounded-full bg-fuchsia-400"></span>
                    <p><strong>Akses Aman & Mudah</strong> Setiap guru memiliki akun pribadi, data tersimpan dengan aman, dan bisa diakses dari perangkat apa saja.</p>
                </li>
            </ul>
        </section>
    </main>
</div>
</body>
</html>
