@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
<section class="bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 text-white rounded-xl shadow-lg p-gutter-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-white">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p class="font-body text-body-md text-amber-100">Pemantauan nilai dan rapor — tahun ajaran aktif: {{ $activeYear?->name ?? '-' }} ({{ $semester?->name ?? '-' }}).</p>
  </div>
</section>

<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Kepala Sekolah</h1>
    <p class="font-body text-body-md text-on-surface-muted">Status pengiriman nilai guru, kelengkapan rapor per kelas, dan penguncian rapor yang sudah diterbitkan.</p>
  </div>
</section>

@include('dashboards._monitoring')
@endsection
