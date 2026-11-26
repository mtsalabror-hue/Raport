<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Dashboard') — e-Rapor SMK IT AL-ABROR</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">/** @type {import('tailwindcss').Config} */
globalThis.tailwind = globalThis.tailwind || {};
globalThis.tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'primary': '#1d4ed8',
        'on-primary': '#ffffff',
        'primary-hover': '#0039b5',
        'primary-container': '#dce1ff',
        'on-primary-container': '#001551',
        'sidebar': '#0b1530',
        'on-sidebar': '#ffffff',
        'on-sidebar-muted': '#a1aacd',
        'sidebar-hover': '#111a35',
        'sidebar-active': '#262f4c',
        'accent': '#f59e0b',
        'on-accent': '#2a1700',
        'accent-container': '#ffeede',
        'on-accent-container': '#653e00',
        'surface': '#ffffff',
        'surface-dim': '#f8fafc',
        'surface-container': '#efefff',
        'surface-container-high': '#e0e1f4',
        'on-surface': '#0b1c30',
        'on-surface-muted': '#505f76',
        'outline': '#8292aa',
        'outline-variant': '#e0e1f4',
        'success': '#006e2d',
        'on-success': '#ffffff',
        'success-container': '#c5ffc8',
        'on-success-container': '#002109',
        'error': '#bf0715',
        'on-error': '#ffffff',
        'error-container': '#ffedea',
        'on-error-container': '#410002',
        'brand-violet': '#7c3aed',
        'brand-emerald': '#059669',
        'brand-rose': '#e11d48',
        'brand-cyan': '#0891b2',
        'brand-amber': '#d97706',
        'violet-tint': '#ede9fe',
        'emerald-tint': '#d1fae5',
        'rose-tint': '#ffe4e6',
        'sky-tint': '#e0f2fe',
        'amber-tint': '#fef3c7',
        'blue-tint': '#dbeafe',
      },
      borderRadius: {
        'sm': '4px',
        'DEFAULT': '8px',
        'md': '12px',
        'lg': '16px',
        'full': '9999px',
      },
      spacing: {
        'gutter-xs': '4px',
        'gutter-sm': '8px',
        'gutter-md': '16px',
        'gutter-lg': '24px',
        'gutter-xl': '32px',
        'section': '32px',
        'page-margin': '24px',
      },
      fontFamily: {
        'display': ['"Red Hat Text"', 'sans-serif'],
        'headline': ['"Red Hat Text"', 'sans-serif'],
        'body': ['"Instrument Sans"', 'sans-serif'],
        'label': ['"Instrument Sans"', 'sans-serif'],
        'mono': ['"Spline Sans Mono"', 'monospace'],
      },
      fontSize: {
        'display-md': ['36px', { lineHeight: '44px', letterSpacing: '-0.5px', fontWeight: '700' }],
        'display-md-mobile': ['28px', { lineHeight: '36px', letterSpacing: '-0.25px', fontWeight: '700' }],
        'headline-lg': ['24px', { lineHeight: '32px', letterSpacing: '-0.25px', fontWeight: '700' }],
        'headline-md': ['20px', { lineHeight: '28px', letterSpacing: '0px', fontWeight: '600' }],
        'headline-sm': ['16px', { lineHeight: '24px', letterSpacing: '0px', fontWeight: '600' }],
        'body-lg': ['16px', { lineHeight: '24px', letterSpacing: '0px', fontWeight: '400' }],
        'body-md': ['14px', { lineHeight: '20px', letterSpacing: '0px', fontWeight: '400' }],
        'body-sm': ['12px', { lineHeight: '16px', letterSpacing: '0.1px', fontWeight: '400' }],
        'label-lg': ['14px', { lineHeight: '20px', letterSpacing: '0.1px', fontWeight: '600' }],
        'label-md': ['12px', { lineHeight: '16px', letterSpacing: '0.2px', fontWeight: '600' }],
        'label-sm': ['11px', { lineHeight: '14px', letterSpacing: '0.3px', fontWeight: '600' }],
        'mono-sm': ['12px', { lineHeight: '16px', letterSpacing: '0px', fontWeight: '500' }],
      },
      transitionDuration: {
        'state-change': '120ms',
        'surface-enter': '220ms',
        'surface-exit': '150ms',
      },
      transitionDelay: {
        'state-change': '0ms',
        'surface-enter': '0ms',
        'surface-exit': '0ms',
      },
      transitionTimingFunction: {
        'state-change': 'ease-out',
        'surface-enter': 'cubic-bezier(0.2, 0, 1, 1)',
        'surface-exit': 'cubic-bezier(0.3, 0, 1, 1)',
      },
    },
  },
};
if (typeof module !== 'undefined') module.exports = globalThis.tailwind.config;
</script>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&amp;family=Red+Hat+Text:wght@500;600;700&amp;family=Spline+Sans+Mono:wght@500&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet">
<style>.material-symbols-outlined, .material-symbols-rounded, .material-symbols-sharp, .material-icons { display: inline-block; width: 1em; height: 1em; line-height: 1 !important; box-sizing: content-box; overflow: hidden; white-space: nowrap; direction: ltr; vertical-align: middle; } [x-cloak] { display: none !important; }</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-surface text-on-surface font-body antialiased">

@php
  $__roleSlug = auth()->user()?->role?->slug;
  $__activeYear = null;
  try { $__activeYear = \App\Models\AcademicYear::where('is_active', true)->first(); } catch (\Throwable $__e) { $__activeYear = null; }
  $__roleChip = match ($__roleSlug) {
    'admin' => 'bg-blue-100 text-blue-800',
    'guru' => 'bg-emerald-100 text-emerald-800',
    'wali_kelas' => 'bg-violet-100 text-violet-800',
    'kepala_sekolah' => 'bg-amber-100 text-amber-800',
    'waka_kurikulum' => 'bg-cyan-100 text-cyan-800',
    default => 'bg-slate-100 text-slate-700',
  };
  // Profil sekolah (Fase A): nama + logo untuk brand sidebar.
  $__schoolName = 'SMK IT AL-ABROR';
  $__logoUrl = null;
  try {
    $__profileService = app(\App\Services\SchoolProfileService::class);
    $__schoolName = $__profileService->profile()['school_name'] ?? $__schoolName;
    $__logoUrl = $__profileService->logoUrl();
  } catch (\Throwable $__e) { $__schoolName = 'SMK IT AL-ABROR'; $__logoUrl = null; }
  // Dropdown sidebar admin: terbuka otomatis bila salah satu anaknya aktif.
  $__masterActive = request()->routeIs('admin.academic_years.*', 'admin.semesters.*', 'admin.departments.*', 'admin.classes.*', 'admin.subjects.*', 'admin.curricula.*', 'admin.teachers.*', 'admin.students.*');
  $__penugasanActive = request()->routeIs('admin.teaching-assignments.*', 'admin.teaching-copy.*', 'admin.homeroom-assignments.*');
@endphp

<div class="min-h-screen flex bg-surface-dim">

  <!-- Mobile Sidebar Backdrop -->
  <div id="sidebar-backdrop" class="fixed inset-0 bg-sidebar/50 z-30 hidden lg:hidden" onclick="toggleSidebar(false)"></div>

  <!-- Left Navigation Sidebar (#0b1530) -->
  <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 w-[260px] min-h-screen bg-sidebar text-on-sidebar flex flex-col justify-between transform -translate-x-full lg:translate-x-0 lg:static transition-transform duration-surface-enter ease-surface-enter shrink-0">
    <div class="flex flex-col gap-gutter-lg p-gutter-md">
      <!-- Brand Header -->
      <div class="flex items-center justify-between gap-gutter-sm px-gutter-sm py-gutter-xs">
        <div class="flex items-center gap-gutter-sm">
          @if($__logoUrl)
            <img src="{{ $__logoUrl }}" alt="Logo sekolah" class="w-9 h-9 rounded object-contain bg-white shrink-0">
          @else
          <div class="w-9 h-9 rounded bg-gradient-to-br from-blue-500 via-indigo-500 to-violet-600 flex items-center justify-center shrink-0 shadow">
            <span class="material-symbols-outlined text-on-primary text-headline-md" aria-hidden="true">school</span>
          </div>
          @endif
          <div class="flex flex-col">
            <span class="font-headline text-headline-sm text-on-sidebar leading-tight">e-Rapor SMK</span>
            <span class="font-label text-label-sm text-on-sidebar-muted">{{ $__schoolName }}</span>
          </div>
        </div>
        <button type="button" aria-label="Tutup menu" onclick="toggleSidebar(false)" class="lg:hidden inline-flex items-center justify-center w-8 h-8 rounded text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover">
          <span class="material-symbols-outlined text-headline-md" aria-hidden="true">close</span>
        </button>
      </div>

      <!-- Primary Navigation -->
      <nav aria-label="Navigasi Utama" class="flex flex-col gap-gutter-xs">
        @if($__roleSlug === 'admin')
          <a href="{{ url('/admin/dashboard') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('admin/dashboard') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change" @if(request()->is('admin/dashboard')) aria-current="page" @endif>
            <span class="material-symbols-outlined text-headline-md {{ request()->is('admin/dashboard') ? 'text-accent' : '' }}" @if(request()->is('admin/dashboard')) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">dashboard</span>
            <span class="font-label text-label-lg">Dashboard</span>
          </a>
          <div x-data="{ open: {{ $__masterActive ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="w-full flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ $__masterActive ? 'text-on-sidebar bg-sidebar-hover' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change" :aria-expanded="open.toString()">
              <span class="material-symbols-outlined text-headline-md" aria-hidden="true">database</span>
              <span class="font-label text-label-lg flex-1 text-left">Data Master</span>
              <span class="material-symbols-outlined text-headline-md transition-transform duration-state-change" :class="open ? 'rotate-180' : ''" aria-hidden="true">expand_more</span>
            </button>
            <div x-show="open" x-cloak class="flex flex-col gap-gutter-xs pl-6 mt-1">
          <a href="{{ route('admin.academic_years.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.academic_years.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">calendar_month</span>
            <span class="font-body text-body-md">Tahun Ajaran</span>
          </a>
          <a href="{{ route('admin.semesters.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.semesters.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">date_range</span>
            <span class="font-body text-body-md">Semester</span>
          </a>
          <a href="{{ route('admin.departments.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.departments.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">domain</span>
            <span class="font-body text-body-md">Jurusan</span>
          </a>
          <a href="{{ route('admin.classes.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.classes.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">meeting_room</span>
            <span class="font-body text-body-md">Kelas</span>
          </a>
          <a href="{{ route('admin.subjects.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.subjects.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">menu_book</span>
            <span class="font-body text-body-md">Mata Pelajaran</span>
          </a>
          <a href="{{ route('admin.curricula.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.curricula.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">book</span>
            <span class="font-body text-body-md">Kurikulum</span>
          </a>
          <a href="{{ route('admin.teachers.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.teachers.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">badge</span>
            <span class="font-body text-body-md">Guru</span>
          </a>
          <a href="{{ route('admin.students.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.students.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">groups</span>
            <span class="font-body text-body-md">Siswa</span>
          </a>
            </div>
          </div>
          <div x-data="{ open: {{ $__penugasanActive ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="w-full flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ $__penugasanActive ? 'text-on-sidebar bg-sidebar-hover' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change" :aria-expanded="open.toString()">
              <span class="material-symbols-outlined text-headline-md" aria-hidden="true">assignment_ind</span>
              <span class="font-label text-label-lg flex-1 text-left">Penugasan</span>
              <span class="material-symbols-outlined text-headline-md transition-transform duration-state-change" :class="open ? 'rotate-180' : ''" aria-hidden="true">expand_more</span>
            </button>
            <div x-show="open" x-cloak class="flex flex-col gap-gutter-xs pl-6 mt-1">
          <a href="{{ route('admin.teaching-assignments.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.teaching-assignments.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">assignment</span>
            <span class="font-body text-body-md">Mengajar</span>
          </a>
          <a href="{{ route('admin.teaching-copy.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.teaching-copy.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">content_copy</span>
            <span class="font-body text-body-md">Salin Mengajar</span>
          </a>
          <a href="{{ route('admin.homeroom-assignments.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.homeroom-assignments.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">supervisor_account</span>
            <span class="font-body text-body-md">Wali Kelas</span>
          </a>
            </div>
          </div>
          <a href="{{ route('admin.users.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">manage_accounts</span>
            <span class="font-body text-body-md">Pengguna</span>
          </a>
          <a href="{{ url('/admin/attendance/monitoring') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('admin/attendance/monitoring') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">event_available</span>
            <span class="font-body text-body-md">Monitoring Presensi</span>
          </a>
          <a href="{{ route('admin.report-cards.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.report-cards.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">description</span>
            <span class="font-body text-body-md">Rapor</span>
          </a>
          <a href="{{ route('admin.ledger.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.ledger.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">table_chart</span>
            <span class="font-body text-body-md">Leger</span>
          </a>
          <a href="{{ route('admin.extracurriculars.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.extracurriculars.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">star_half</span>
            <span class="font-body text-body-md">Ekstrakurikuler</span>
          </a>
          <a href="{{ route('admin.attitudes.recap') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.attitudes.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">self_improvement</span>
            <span class="font-body text-body-md">Rekap Sikap</span>
          </a>
          <a href="{{ route('admin.p5-projects.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.p5-projects.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">diversity_3</span>
            <span class="font-body text-body-md">P5</span>
          </a>
          <a href="{{ route('admin.internships.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.internships.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">work</span>
            <span class="font-body text-body-md">PKL</span>
          </a>
          <a href="{{ route('admin.competency-exams.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.competency-exams.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">verified</span>
            <span class="font-body text-body-md">UKK</span>
          </a>
          <a href="{{ route('admin.promotions.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.promotions.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">trending_up</span>
            <span class="font-body text-body-md">Naik Kelas</span>
          </a>
          <a href="{{ route('admin.alumni.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.alumni.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">school</span>
            <span class="font-body text-body-md">Alumni</span>
          </a>
          <span class="px-gutter-md pt-gutter-md pb-gutter-xs font-label text-label-sm text-amber-300 uppercase">Pengaturan</span>
          <a href="{{ route('admin.school-profile.edit') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.school-profile.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">apartment</span>
            <span class="font-body text-body-md">Profil Sekolah</span>
          </a>
          <a href="{{ route('admin.print-settings.edit') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.print-settings.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">print</span>
            <span class="font-body text-body-md">Pengaturan Cetak</span>
          </a>
          <a href="{{ route('admin.backup.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.backup.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">backup</span>
            <span class="font-body text-body-md">Backup &amp; Restore</span>
          </a>
          <a href="{{ route('admin.import.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('admin.import.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">upload_file</span>
            <span class="font-body text-body-md">Impor Data</span>
          </a>
        @elseif($__roleSlug === 'guru')
          <a href="{{ url('/guru/dashboard') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('guru/dashboard') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change" @if(request()->is('guru/dashboard')) aria-current="page" @endif>
            <span class="material-symbols-outlined text-headline-md {{ request()->is('guru/dashboard') ? 'text-accent' : '' }}" @if(request()->is('guru/dashboard')) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">dashboard</span>
            <span class="font-label text-label-lg">Dashboard</span>
          </a>
          <a href="{{ url('/guru/assessments') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('guru/assessments') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">edit_note</span>
            <span class="font-body text-body-md">Input Nilai</span>
          </a>
          <a href="{{ url('/guru/assessments/recap') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('guru/assessments/recap', 'guru/assessments/history', 'guru/assessments/calculate') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">table_chart</span>
            <span class="font-body text-body-md">Rekap Nilai</span>
          </a>
          <a href="{{ url('/guru/components') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('guru/components') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">tune</span>
            <span class="font-body text-body-md">Komponen Nilai</span>
          </a>
          <a href="{{ url('/guru/descriptions') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('guru/descriptions') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">description</span>
            <span class="font-body text-body-md">Deskripsi Nilai</span>
          </a>
          <a href="{{ url('/guru/attendance') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('guru/attendance*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">event_available</span>
            <span class="font-body text-body-md">Presensi</span>
          </a>
          <a href="{{ route('guru.internships.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('guru.internships.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">work</span>
            <span class="font-body text-body-md">PKL</span>
          </a>
          <a href="{{ route('guru.competency-exams.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('guru.competency-exams.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">verified</span>
            <span class="font-body text-body-md">UKK</span>
          </a>
          <a href="{{ route('guru.p5.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('guru.p5.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">diversity_3</span>
            <span class="font-body text-body-md">P5 Saya</span>
          </a>
          <a href="{{ route('guru.supervised-extracurriculars.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('guru.supervised-extracurriculars.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">star_half</span>
            <span class="font-body text-body-md">Ekstra Binaan</span>
          </a>
          <a href="{{ route('guru.profile.edit') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('guru.profile.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">account_circle</span>
            <span class="font-body text-body-md">Profil Saya</span>
          </a>
        @elseif($__roleSlug === 'wali_kelas')
          <a href="{{ route('wali.dashboard') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.dashboard') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change" @if(request()->routeIs('wali.dashboard')) aria-current="page" @endif>
            <span class="material-symbols-outlined text-headline-md {{ request()->routeIs('wali.dashboard') ? 'text-accent' : '' }}" @if(request()->routeIs('wali.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">dashboard</span>
            <span class="font-label text-label-lg">Dashboard</span>
          </a>
          <a href="{{ route('wali.students') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.students') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">groups</span>
            <span class="font-body text-body-md">Siswa Saya</span>
          </a>
          <a href="{{ route('wali.completeness') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.completeness') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">fact_check</span>
            <span class="font-body text-body-md">Kelengkapan Rapor</span>
          </a>
          <a href="{{ route('wali.report-cards.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.report-cards.*', 'wali.preview', 'wali.history') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">description</span>
            <span class="font-body text-body-md">Rapor</span>
          </a>
          <a href="{{ url('/wali/attendance/recap') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->is('wali/attendance/*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">event_available</span>
            <span class="font-body text-body-md">Presensi</span>
          </a>
          <a href="{{ route('wali.extracurriculars') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.extracurriculars.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">star_half</span>
            <span class="font-body text-body-md">Ekstrakurikuler</span>
          </a>
          <a href="{{ route('wali.achievements') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.achievements.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">emoji_events</span>
            <span class="font-body text-body-md">Prestasi</span>
          </a>
          <a href="{{ route('wali.notes') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.notes.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">edit_note</span>
            <span class="font-body text-body-md">Catatan</span>
          </a>
          <a href="{{ route('wali.attitudes') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.attitudes.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">self_improvement</span>
            <span class="font-body text-body-md">Sikap</span>
          </a>
          <a href="{{ route('wali.promotions') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.promotions.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">trending_up</span>
            <span class="font-body text-body-md">Kenaikan Kelas</span>
          </a>
          <a href="{{ route('wali.internships.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.internships.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">work</span>
            <span class="font-body text-body-md">PKL</span>
          </a>
          <a href="{{ route('wali.competency-exams.index') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.competency-exams.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">verified</span>
            <span class="font-body text-body-md">UKK</span>
          </a>
          <a href="{{ route('wali.profile.edit') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('wali.profile.*') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-md" aria-hidden="true">account_circle</span>
            <span class="font-body text-body-md">Profil Saya</span>
          </a>
        @elseif($__roleSlug === 'kepala_sekolah')
          <a href="{{ route('kepala.dashboard') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('kepala.dashboard') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change" @if(request()->routeIs('kepala.dashboard')) aria-current="page" @endif>
            <span class="material-symbols-outlined text-headline-md {{ request()->routeIs('kepala.dashboard') ? 'text-accent' : '' }}" @if(request()->routeIs('kepala.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">dashboard</span>
            <span class="font-label text-label-lg">Dashboard</span>
          </a>
          <p class="px-gutter-md pt-gutter-md font-body text-body-sm text-on-sidebar-muted">Pemantauan nilai &amp; rapor. Kunci rapor yang sudah diterbitkan dari tabel di dashboard.</p>
        @elseif($__roleSlug === 'waka_kurikulum')
          <a href="{{ route('waka.dashboard') }}" class="flex items-center gap-gutter-md px-gutter-md py-gutter-sm rounded {{ request()->routeIs('waka.dashboard') ? 'bg-gradient-to-r from-primary to-violet-600 text-white shadow' : 'text-on-sidebar-muted hover:text-on-sidebar hover:bg-sidebar-hover' }} transition-colors duration-state-change" @if(request()->routeIs('waka.dashboard')) aria-current="page" @endif>
            <span class="material-symbols-outlined text-headline-md {{ request()->routeIs('waka.dashboard') ? 'text-accent' : '' }}" @if(request()->routeIs('waka.dashboard')) style="font-variation-settings: 'FILL' 1;" @endif aria-hidden="true">dashboard</span>
            <span class="font-label text-label-lg">Dashboard</span>
          </a>
          <p class="px-gutter-md pt-gutter-md font-body text-body-sm text-on-sidebar-muted">Pemantauan nilai &amp; rapor (hanya-baca).</p>
        @endif
      </nav>
    </div>

    <!-- Sidebar Footer: Academic Year Info -->
    <div class="p-gutter-md border-t border-sidebar-active">
      <div class="bg-sidebar-hover border border-white/10 rounded p-gutter-md flex flex-col gap-gutter-xs">
        <span class="font-label text-label-sm text-amber-300 uppercase">Tahun Pelajaran</span>
        @if($__activeYear)
          <span class="font-label text-label-lg text-on-sidebar tabular-nums">{{ $__activeYear->name }}</span>
        @endif
        <span class="font-body text-body-sm text-on-sidebar-muted">Kurikulum Merdeka SMK</span>
      </div>
    </div>
  </aside>

  <!-- Main Workspace Column -->
  <div class="flex-1 flex flex-col min-w-0">

    <!-- Sticky Top Bar -->
    <header class="sticky top-0 z-20 h-16 bg-surface border-b border-outline-variant px-page-margin flex items-center justify-between gap-gutter-md">
      <div class="flex items-center gap-gutter-md min-w-0">
        <button type="button" aria-label="Buka menu navigasi" onclick="toggleSidebar(true)" class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded border border-outline-variant text-on-surface hover:bg-surface-dim">
          <span class="material-symbols-outlined text-headline-md" aria-hidden="true">menu</span>
        </button>
        <div class="flex items-center gap-gutter-sm min-w-0">
          <span class="font-headline text-headline-sm text-on-surface truncate">{{ $__schoolName }}</span>
        </div>
      </div>

      <div class="flex items-center gap-gutter-md">
        <div class="hidden md:flex items-center gap-gutter-sm px-gutter-sm py-gutter-xs">
          <span class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-600 text-white flex items-center justify-center shrink-0">
            <span class="font-label text-label-md">{{ strtoupper(implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(explode(' ', trim(auth()->user()->name ?? '?')), 0, 2)))) }}</span>
          </span>
          <div class="flex flex-col items-start text-left">
            <span class="font-label text-label-md text-on-surface leading-tight">{{ auth()->user()->name }}</span>
            <span class="font-label text-label-sm leading-tight px-2 py-0.5 rounded-full {{ $__roleChip }}">{{ auth()->user()->role->name ?? '' }}</span>
          </div>
        </div>
        <form method="POST" action="/logout">
          @csrf
          <button type="submit" class="inline-flex items-center gap-gutter-xs px-gutter-md py-gutter-sm rounded bg-surface border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim transition-colors duration-state-change">
            <span class="material-symbols-outlined text-headline-sm" aria-hidden="true">logout</span>
            <span class="hidden sm:inline">Keluar</span>
          </button>
        </form>
      </div>
      <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-gradient-to-r from-blue-600 via-violet-500 to-amber-500"></div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 p-page-margin max-w-[1280px] w-full mx-auto flex flex-col gap-section">
      @yield('content')
    </main>
  </div>
</div>

<script>
function toggleSidebar(open) {
  var sidebar = document.getElementById('app-sidebar');
  var backdrop = document.getElementById('sidebar-backdrop');
  if (open) {
    sidebar.classList.remove('-translate-x-full');
    backdrop.classList.remove('hidden');
  } else {
    sidebar.classList.add('-translate-x-full');
    backdrop.classList.add('hidden');
  }
}
</script>
@stack('scripts')
</body>
</html>
