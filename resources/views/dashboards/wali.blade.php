@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Wali Kelas</h1>
    <p class="font-body text-body-md text-on-surface-muted">Selamat datang, {{ auth()->user()->name }}. Pantau kelengkapan rapor kelas yang Anda ampu.</p>
  </div>
</section>

<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-gutter-lg">
  <a href="{{ route('wali.students') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-primary-container text-on-primary-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">groups</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Siswa Saya</span>
      <span class="font-body text-body-md text-on-surface-muted">Daftar siswa kelas yang Anda ampu.</span>
    </span>
  </a>
  <a href="{{ route('wali.completeness') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-accent-container text-on-accent-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">fact_check</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Kelengkapan Rapor</span>
      <span class="font-body text-body-md text-on-surface-muted">Periksa komponen rapor yang masih kurang per siswa.</span>
    </span>
  </a>
  <a href="{{ route('wali.report-cards.index') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-surface-container text-on-primary-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">description</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Rapor</span>
      <span class="font-body text-body-md text-on-surface-muted">Lihat dan unduh rapor siswa kelas Anda.</span>
    </span>
  </a>
  <a href="{{ url('/wali/attendance/recap') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-surface-container text-on-primary-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">event_available</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Presensi</span>
      <span class="font-body text-body-md text-on-surface-muted">Rekap kehadiran siswa kelas Anda.</span>
    </span>
  </a>
  <a href="{{ route('wali.notes') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-primary-container text-on-primary-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">edit_note</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Catatan</span>
      <span class="font-body text-body-md text-on-surface-muted">Catatan wali kelas untuk rapor siswa.</span>
    </span>
  </a>
  <a href="{{ route('wali.achievements') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-success-container text-on-success-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">emoji_events</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Prestasi &amp; Ekstrakurikuler</span>
      <span class="font-body text-body-md text-on-surface-muted">Kelola prestasi dan kegiatan ekstrakurikuler siswa.</span>
    </span>
  </a>
</section>
@endsection
