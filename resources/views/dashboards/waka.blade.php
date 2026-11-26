@extends('layouts.app')

@section('title', 'Dashboard Waka Kurikulum')

@section('content')
<section class="bg-gradient-to-r from-cyan-600 via-sky-600 to-blue-600 text-white rounded-xl shadow-lg p-gutter-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-white">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p class="font-body text-body-md text-cyan-100">Pemantauan nilai dan rapor (hanya-baca) — tahun ajaran aktif: {{ $activeYear?->name ?? '-' }} ({{ $semester?->name ?? '-' }}).</p>
  </div>
</section>

<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Waka Kurikulum</h1>
    <p class="font-body text-body-md text-on-surface-muted">Status pengiriman nilai guru dan kelengkapan rapor per kelas. Dashboard ini hanya-baca; penguncian rapor hanya oleh Kepala Sekolah.</p>
  </div>
</section>

@include('dashboards._monitoring')
@endsection
