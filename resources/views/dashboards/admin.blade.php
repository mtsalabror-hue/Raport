@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Admin</h1>
    <p class="font-body text-body-md text-on-surface-muted">Ringkasan data sekolah dan tahun ajaran aktif.</p>
  </div>
</section>

<section aria-label="Statistik Utama" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-gutter-lg items-stretch">
  <article class="h-full bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Siswa</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-primary-container text-on-primary-container shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">groups</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-on-surface tabular-nums">{{ $students }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Siswa terdaftar</p>
    </div>
  </article>

  <article class="h-full bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Guru</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-surface-container text-on-primary-container shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">badge</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-on-surface tabular-nums">{{ $teachers }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Guru terdaftar</p>
    </div>
  </article>

  <article class="h-full bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Kelas</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-surface-container text-on-primary-container shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">meeting_room</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-on-surface tabular-nums">{{ $classes }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Rombongan belajar</p>
    </div>
  </article>

  <article class="h-full bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col justify-between gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Mata Pelajaran</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-accent-container text-on-accent-container shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">menu_book</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-on-surface tabular-nums">{{ $subjects }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Mapel terdaftar</p>
    </div>
  </article>
</section>

<section class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-sm">
  <h2 class="font-headline text-headline-md text-on-surface">Tahun Ajaran Aktif</h2>
  <p class="font-body text-body-md text-on-surface-muted">Saat ini sistem berjalan pada tahun ajaran <span class="font-label text-label-lg text-on-surface">{{ $activeYear?->name ?? '-' }}</span>.</p>
</section>
@endsection
