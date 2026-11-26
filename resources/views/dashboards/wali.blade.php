@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')

@section('content')
<section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white rounded-xl shadow-lg p-gutter-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-white">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p class="font-body text-body-md text-blue-100">Pantau kelengkapan rapor kelas yang Anda ampu.</p>
  </div>
  <div class="flex flex-wrap gap-gutter-sm">
    <a href="{{ route('wali.students') }}" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Siswa Saya</a>
    <a href="{{ route('wali.completeness') }}" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Kelengkapan Rapor</a>
  </div>
</section>

<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Wali Kelas</h1>
    <p class="font-body text-body-md text-on-surface-muted">Selamat datang, {{ auth()->user()->name }}. Pantau kelengkapan rapor kelas yang Anda ampu.</p>
  </div>
</section>

<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-gutter-lg">
  <a href="{{ route('wali.students') }}" class="bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-blue-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-blue-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">groups</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-blue-800">Siswa Saya</span>
      <span class="font-body text-body-md text-on-surface-muted">Daftar siswa kelas yang Anda ampu.</span>
    </span>
  </a>
  <a href="{{ route('wali.completeness') }}" class="bg-violet-50 border border-violet-200 border-l-4 border-l-violet-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-violet-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-violet-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">fact_check</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-violet-800">Kelengkapan Rapor</span>
      <span class="font-body text-body-md text-on-surface-muted">Periksa komponen rapor yang masih kurang per siswa.</span>
    </span>
  </a>
  <a href="{{ route('wali.report-cards.index') }}" class="bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-emerald-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-emerald-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">description</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-emerald-800">Rapor</span>
      <span class="font-body text-body-md text-on-surface-muted">Lihat dan unduh rapor siswa kelas Anda.</span>
    </span>
  </a>
  <a href="{{ url('/wali/attendance/recap') }}" class="bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-amber-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-amber-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">event_available</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-amber-800">Presensi</span>
      <span class="font-body text-body-md text-on-surface-muted">Rekap kehadiran siswa kelas Anda.</span>
    </span>
  </a>
  <a href="{{ route('wali.notes') }}" class="bg-rose-50 border border-rose-200 border-l-4 border-l-rose-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-rose-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-rose-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">edit_note</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-rose-800">Catatan</span>
      <span class="font-body text-body-md text-on-surface-muted">Catatan wali kelas untuk rapor siswa.</span>
    </span>
  </a>
  <a href="{{ route('wali.achievements') }}" class="bg-cyan-50 border border-cyan-200 border-l-4 border-l-cyan-500 rounded-xl p-gutter-lg flex flex-col gap-gutter-md hover:-translate-y-0.5 hover:shadow-md hover:bg-cyan-100 transition-all duration-200">
    <span class="inline-flex items-center justify-center w-10 h-10 rounded bg-cyan-600 text-white">
      <span class="material-symbols-outlined text-headline-md" aria-hidden="true">emoji_events</span>
    </span>
    <span class="flex flex-col gap-gutter-xs">
      <span class="font-headline text-headline-md text-cyan-800">Prestasi &amp; Ekstrakurikuler</span>
      <span class="font-body text-body-md text-on-surface-muted">Kelola prestasi dan kegiatan ekstrakurikuler siswa.</span>
    </span>
  </a>
</section>
@endsection
