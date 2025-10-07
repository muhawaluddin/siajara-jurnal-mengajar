<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none !important;}</style>
</head>
<body
    x-data="{
        sidebarExpanded: window.innerWidth >= 1024,
        mobileMenuOpen: false,
        toggleSidebar() { this.sidebarExpanded = ! this.sidebarExpanded; },
        handleResize() {
            if (window.innerWidth < 640) {
                this.sidebarExpanded = false;
            }
            if (window.innerWidth >= 1024) {
                this.sidebarExpanded = true;
            }
            if (window.innerWidth >= 768) {
                this.mobileMenuOpen = false;
            }
        }
    }"
    x-init="handleResize()"
    @resize.window="handleResize()"
    class="min-h-screen bg-slate-100 text-slate-800 flex flex-col">
@php($user = auth()->user())

<header class="bg-emerald-700 border-b border-emerald-600 text-white">
    <div class="relative mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo-addaraen.jpeg') }}" alt="Logo Addaraen" class="h-10 w-10 rounded-full border border-white/40 object-cover shadow-md">
            <div>
                <h1 class="text-xl font-semibold text-white">{{ config('app.name') }}</h1>
                <p class="text-sm text-emerald-100/90">Aplikasi Absensi &amp; Jurnal Ponpes Addaraen</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button"
                    class="inline-flex items-center justify-center rounded-full border border-white/30 bg-white/10 p-2 text-white shadow-sm transition hover:bg-white/20 lg:hidden"
                    @click="mobileMenuOpen = ! mobileMenuOpen">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6h16.5M3.75 12h16.5M3.75 18h16.5"/></svg>
                <span class="sr-only">Toggle menu utama</span>
            </button>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = ! open" @blur="open = false"
                    class="flex items-center rounded-full border border-white/30 bg-white/10 p-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=047857&color=ffffff" alt="Avatar" class="h-8 w-8 rounded-full">
                    <svg class="h-4 w-4 text-white ml-1" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    <span class="sr-only">Toggle menu profil</span>
                </button>

                <div x-show="open" x-transition.origin.top.right x-cloak
                     class="absolute right-0 mt-2 w-48 rounded-xl border border-emerald-700 bg-emerald-900 shadow-lg text-emerald-100">
                    <div class="px-4 py-3 text-sm">
                        <p class="font-semibold text-emerald-50">{{ $user->name }}</p>
                        <p class="text-xs text-emerald-300/80">{{ $user->email }}</p>
                    </div>
                    <div class="border-t border-emerald-700"> 
                        <a href="#" class="block px-4 py-2 text-sm text-emerald-200 hover:bg-emerald-800">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-emerald-800/80">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div x-show="mobileMenuOpen" x-transition x-cloak @click.away="mobileMenuOpen = false"
             class="lg:hidden absolute right-0 top-full mt-2 w-64 rounded-xl border border-emerald-200 bg-white text-slate-700 shadow-lg z-50">
            <div class="px-4 py-3 text-sm font-semibold text-emerald-700 border-b border-emerald-100">Menu Utama</div>
            <ul class="py-2 text-sm">
                <li><a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-emerald-50">Dashboard</a></li>
                @if($user?->isAdmin())
                    <li><a href="{{ route('admin.classrooms.index') }}" class="block px-4 py-2 hover:bg-emerald-50">Master Kelas</a></li>
                    <li><a href="{{ route('admin.subjects.index') }}" class="block px-4 py-2 hover:bg-emerald-50">Master Mapel</a></li>
                    <li><a href="{{ route('admin.students.index') }}" class="block px-4 py-2 hover:bg-emerald-50">Data Siswa</a></li>
                    <li><a href="{{ route('admin.teachers.index') }}" class="block px-4 py-2 hover:bg-emerald-50">Data Guru</a></li>
                    <li x-data="{ open: @json(request()->routeIs('admin.reports.*') || request()->routeIs('admin.teacher-reports.*')) }" class="border-t border-emerald-100/60 pt-2 mt-2">
                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-left font-semibold text-emerald-700 hover:bg-emerald-50">
                            <span>Laporan</span>
                            <svg class="h-4 w-4 text-emerald-600 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" x-transition x-cloak class="mt-1 space-y-1 pl-4 text-sm">
                            <a href="{{ route('admin.reports.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-100 text-emerald-800 font-semibold' : 'hover:bg-emerald-50 text-emerald-700' }}">Laporan Absensi Siswa</a>
                            <a href="{{ route('admin.teacher-reports.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.teacher-reports.*') ? 'bg-emerald-100 text-emerald-800 font-semibold' : 'hover:bg-emerald-50 text-emerald-700' }}">Laporan Jurnal Mengajar</a>
                        </div>
                    </li>
                @endif
                @if($user?->isGuru())
                    <li><a href="{{ route('web.teaching-journals.index') }}" class="block px-4 py-2 hover:bg-emerald-50">Jurnal Mengajar</a></li>
                    <li><a href="{{ route('web.attendances.index') }}" class="block px-4 py-2 hover:bg-emerald-50">Absensi</a></li>
                @endif
            </ul>
        </div>
    </div>
</header>

<div class="px-3 sm:px-4 lg:px-6 py-4 lg:py-6 flex-1 flex">
    <div class="flex flex-col lg:flex-row items-stretch gap-4 lg:gap-0 flex-1">
        <nav :class="sidebarExpanded ? 'lg:w-60 w-full' : 'lg:w-20 w-14'" class="hidden lg:block flex-shrink-0 transition-all duration-300">
            <div class="bg-emerald-950 border border-emerald-800/60 shadow-md lg:shadow-lg rounded-2xl lg:rounded-r-none lg:border-r-0 p-5 md:p-6 flex flex-col text-emerald-100 lg:h-full">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-emerald-200 uppercase tracking-wide" x-show="sidebarExpanded" x-transition x-cloak>Menu Utama</h2>
                <button type="button"
                        @click="toggleSidebar()"
                        :aria-expanded="sidebarExpanded.toString()"
                        class="inline-flex items-center justify-center rounded-lg border border-emerald-600 bg-emerald-800 p-2 text-emerald-100 transition-all duration-300 hover:bg-emerald-700">
                    <svg x-show="sidebarExpanded" x-transition class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5 8.5 13l6.5-6.5"/></svg>
                    <svg x-show="!sidebarExpanded" x-transition class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 19.5 6.5-6.5L9 6.5"/></svg>
                    <span class="sr-only">Toggle sidebar</span>
                </button>
            </div>

            <div x-show="!sidebarExpanded" x-transition x-cloak class="flex justify-center text-emerald-200 mb-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
            </div>

            <ul class="mt-4 space-y-3 text-sm flex-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                       :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75M4.5 10.5V21h6.75v-4.5h1.5V21H21V10.5"/></svg>
                        <span x-show="sidebarExpanded" x-transition.origin.left class="whitespace-nowrap">Dashboard</span>
                        <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Dashboard</span>
                    </a>
                </li>

                @if($user?->isAdmin())
                    <li>
                        <a href="{{ route('admin.classrooms.index') }}"
                           class="flex items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('admin.classrooms.*') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                           :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            <span x-show="sidebarExpanded" x-transition.origin.left class="whitespace-nowrap">Master Kelas</span>
                            <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Master Kelas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.subjects.index') }}"
                           class="flex items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('admin.subjects.*') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                           :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                            <span x-show="sidebarExpanded" x-transition.origin.left class="whitespace-nowrap">Master Mapel</span>
                            <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Master Mapel</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.students.index') }}"
                           class="flex items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('admin.students.*') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                           :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25A2.25 2.25 0 0 1 9.75 12h4.5a2.25 2.25 0 0 1 2.25 2.25v3a2.25 2.25 0 0 1-2.25 2.25h-4.5A2.25 2.25 0 0 1 7.5 17.25v-3ZM15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <span x-show="sidebarExpanded" x-transition.origin.left class="whitespace-nowrap">Data Siswa</span>
                            <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Data Siswa</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.teachers.index') }}"
                           class="flex items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('admin.teachers.*') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                           :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.127a9.06 9.06 0 0 0 2.501.373A9.06 9.06 0 0 0 20 19.127V18a4 4 0 0 0-4-4h-1m0 5.127V18a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v1.127M15 19.127V21m-6 0v-1.873M12 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-6 7.877A9.06 9.06 0 0 1 3.5 19.127V18a4 4 0 0 1 4-4H9"/></svg>
                            <span x-show="sidebarExpanded" x-transition.origin.left class="whitespace-nowrap">Data Guru</span>
                            <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Data Guru</span>
                        </a>
                    </li>
                    <li x-data="{ open: @json(request()->routeIs('admin.reports.*') || request()->routeIs('admin.teacher-reports.*')) }" class="w-full">
                        <button type="button"
                                @click="open = !open"
                                class="flex w-full items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('admin.reports.*') || request()->routeIs('admin.teacher-reports.*') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                                :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9M12 3.75v4.5m5.25 0h.75a2.25 2.25 0 0 1 2.25 2.25v7.5A2.25 2.25 0 0 1 18 20.25H6a2.25 2.25 0 0 1-2.25-2.25v-7.5A2.25 2.25 0 0 1 6 8.25h.75"/></svg>
                            <span x-show="sidebarExpanded" x-transition.origin.left class="flex-1 text-left">Laporan</span>
                            <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Laporan</span>
                            <svg x-show="sidebarExpanded" class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open && sidebarExpanded" x-transition x-cloak class="mt-2 space-y-1 pl-12 text-sm text-emerald-100">
                            <a href="{{ route('admin.reports.index') }}"
                               class="block rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-700 text-white' : 'hover:bg-emerald-800/70 text-emerald-200' }}">
                                Laporan Absensi Siswa
                            </a>
                            <a href="{{ route('admin.teacher-reports.index') }}"
                               class="block rounded-lg px-3 py-2 font-medium transition {{ request()->routeIs('admin.teacher-reports.*') ? 'bg-emerald-700 text-white' : 'hover:bg-emerald-800/70 text-emerald-200' }}">
                                Laporan Jurnal Mengajar
                            </a>
                        </div>
                    </li>
                @endif

                @if($user?->isGuru())
                    <li>
                        <a href="{{ route('web.teaching-journals.index') }}"
                           class="flex items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('web.teaching-journals.*') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                           :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.25 2.25 0 1 1 3.182 3.182L9 18.75 4.5 19.5l.75-4.5 11.612-10.513Z"/></svg>
                            <span x-show="sidebarExpanded" x-transition.origin.left class="whitespace-nowrap">Jurnal Mengajar</span>
                            <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Jurnal Mengajar</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('web.attendances.index') }}"
                           class="flex items-center gap-3 rounded-lg py-2 transition-all duration-300 {{ request()->routeIs('web.attendances.*') ? 'bg-emerald-700/80 text-emerald-100' : 'hover:bg-emerald-800/70 text-emerald-200' }}"
                           :class="sidebarExpanded ? 'px-4 justify-start' : 'px-0 justify-center'">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12 6 6 9-13.5"/></svg>
                            <span x-show="sidebarExpanded" x-transition.origin.left class="whitespace-nowrap">Absensi</span>
                            <span class="sr-only" x-show="!sidebarExpanded" x-transition.origin.left>Absensi</span>
                        </a>
                    </li>
                @endif
            </ul>
            </div>
        </nav>

        <main class="flex-1 max-h-[calc(100vh-4.5rem)] bg-white border border-slate-200 rounded-2xl lg:rounded-l-none lg:border-l lg:border-slate-200 shadow-md lg:shadow-sm p-4 sm:p-6 overflow-y-auto">
            @hasSection('page-title')
                <h2 class="text-lg font-semibold text-slate-900 mb-2">@yield('page-title')</h2>
            @endif
            @hasSection('page-subtitle')
                <p class="text-sm text-slate-500 mb-6">@yield('page-subtitle')</p>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<footer class="px-4 sm:px-6 py-4 bg-white/80 border-t border-slate-200">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm text-slate-500">
        <div>
            &copy; {{ now()->year }} {{ config('app.name') }}. Dikembangkan untuk Pondok Pesantren Addaraen.
        </div>
        <div class="flex items-center gap-3">
            <span>Hubungi Admin:</span>
            <a href="mailto:admin@addaraen.sch.id" class="font-semibold text-emerald-600 hover:text-emerald-500">admin@addaraen.sch.id</a>
            <span class="hidden sm:inline">|</span>
            <span>Versi {{ config('app.version', '1.0.0') }}</span>
        </div>
    </div>
</footer>

</body>
</html>
