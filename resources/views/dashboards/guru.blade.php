@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white rounded-xl shadow-lg p-gutter-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-white">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p class="font-body text-body-md text-blue-100">Kelola nilai, presensi, PKL, dan UKK dari sini.</p>
  </div>
  <div class="flex flex-wrap gap-gutter-sm">
    <a href="{{ url('/guru/assessments') }}" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Input Nilai</a>
    <a href="{{ url('/guru/attendance') }}" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Presensi</a>
  </div>
</section>

<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Guru</h1>
    <p class="font-body text-body-md text-on-surface-muted">Selamat datang, {{ auth()->user()->name }}. Kelola nilai, presensi, PKL, dan UKK dari sini.</p>
  </div>
</section>

<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-gutter-lg">
  <a href="{{ url('/guru/assessments') }}" class="bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-blue-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-blue-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">edit_note</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-blue-800">Input Nilai</span>
      <span class="font-body text-body-md text-on-surface-muted">Masukkan nilai formatif, sumatif, praktik, dan proyek siswa.</span>
    </span>
  </a>
  <a href="{{ url('/guru/assessments/recap') }}" class="bg-violet-50 border border-violet-200 border-l-4 border-l-violet-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-violet-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-violet-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">table_chart</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-violet-800">Rekap Nilai</span>
      <span class="font-body text-body-md text-on-surface-muted">Lihat rekap nilai akhir dan riwayat pengiriman nilai.</span>
    </span>
  </a>
  <a href="{{ url('/guru/attendance') }}" class="bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-emerald-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-emerald-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">event_available</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-emerald-800">Presensi</span>
      <span class="font-body text-body-md text-on-surface-muted">Catat kehadiran siswa dan lihat rekap presensi.</span>
    </span>
  </a>
  <a href="{{ route('guru.internships.index') }}" class="bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-amber-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-amber-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">work</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-amber-800">PKL</span>
      <span class="font-body text-body-md text-on-surface-muted">Bimbingan dan penilaian Praktik Kerja Lapangan.</span>
    </span>
  </a>
  <a href="{{ route('guru.competency-exams.index') }}" class="bg-rose-50 border border-rose-200 border-l-4 border-l-rose-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-rose-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-rose-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">verified</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-rose-800">UKK</span>
      <span class="font-body text-body-md text-on-surface-muted">Lihat jadwal dan hasil Uji Kompetensi Keahlian.</span>
    </span>
  </a>
</section>
@endsection
