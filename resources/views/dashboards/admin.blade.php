@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white rounded-md p-gutter-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-white">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p class="font-body text-body-md text-blue-100">Ringkasan data sekolah dan tahun ajaran aktif: {{ $activeYear?->name ?? '-' }}.</p>
  </div>
  <div class="flex flex-wrap gap-gutter-sm">
    <a href="{{ route('admin.students.index') }}" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Data Siswa</a>
    <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Data Guru</a>
  </div>
</section>

<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Admin</h1>
    <p class="font-body text-body-md text-on-surface-muted">Ringkasan data sekolah dan tahun ajaran aktif.</p>
  </div>
</section>

<section aria-label="Statistik Utama" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-gutter-lg items-stretch">
  <article class="h-full bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Siswa</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-blue-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">groups</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-blue-700 tabular-nums">{{ $students }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Siswa terdaftar</p>
    </div>
  </article>

  <article class="h-full bg-violet-50 border border-violet-200 border-l-4 border-l-violet-500 rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Guru</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-violet-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">badge</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-violet-700 tabular-nums">{{ $teachers }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Guru terdaftar</p>
    </div>
  </article>

  <article class="h-full bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Kelas</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-emerald-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">meeting_room</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-emerald-700 tabular-nums">{{ $classes }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Rombongan belajar</p>
    </div>
  </article>

  <article class="h-full bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Mata Pelajaran</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-amber-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">menu_book</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-amber-700 tabular-nums">{{ $subjects }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Mapel terdaftar</p>
    </div>
  </article>
</section>

<section class="bg-indigo-50 border border-indigo-200 rounded-md p-gutter-lg flex flex-col gap-gutter-sm">
  <h2 class="font-headline text-headline-md text-indigo-900">Tahun Ajaran Aktif</h2>
  <p class="font-body text-body-md text-on-surface-muted">Saat ini sistem berjalan pada tahun ajaran <span class="font-label text-label-lg text-on-surface">{{ $activeYear?->name ?? '-' }}</span>.</p>
</section>
@endsection
