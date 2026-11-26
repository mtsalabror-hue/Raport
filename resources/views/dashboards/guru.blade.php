@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Guru</h1>
    <p class="font-body text-body-md text-on-surface-muted">Selamat datang, {{ auth()->user()->name }}. Kelola nilai, presensi, PKL, dan UKK dari sini.</p>
  </div>
</section>

<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-gutter-lg">
  <a href="{{ url('/guru/assessments') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-primary-container text-on-primary-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">edit_note</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Input Nilai</span>
      <span class="font-body text-body-md text-on-surface-muted">Masukkan nilai formatif, sumatif, praktik, dan proyek siswa.</span>
    </span>
  </a>
  <a href="{{ url('/guru/assessments/recap') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-surface-container text-on-primary-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">table_chart</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Rekap Nilai</span>
      <span class="font-body text-body-md text-on-surface-muted">Lihat rekap nilai akhir dan riwayat pengiriman nilai.</span>
    </span>
  </a>
  <a href="{{ url('/guru/attendance') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-accent-container text-on-accent-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">event_available</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">Presensi</span>
      <span class="font-body text-body-md text-on-surface-muted">Catat kehadiran siswa dan lihat rekap presensi.</span>
    </span>
  </a>
  <a href="{{ route('guru.internships.index') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-surface-container text-on-primary-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">work</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">PKL</span>
      <span class="font-body text-body-md text-on-surface-muted">Bimbingan dan penilaian Praktik Kerja Lapangan.</span>
    </span>
  </a>
  <a href="{{ route('guru.competency-exams.index') }}" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md hover:bg-surface-dim transition-colors duration-state-change">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-success-container text-on-success-container">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">verified</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-on-surface">UKK</span>
      <span class="font-body text-body-md text-on-surface-muted">Lihat jadwal dan hasil Uji Kompetensi Keahlian.</span>
    </span>
  </a>
</section>
@endsection
