@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')

@section('content')
<section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white rounded-md p-gutter-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-white">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p class="font-body text-body-md text-blue-100">Wali kelas: {{ $teacher->name }} — hanya kelas pada penugasan wali Anda yang tampil.</p>
  </div>
  <div class="flex flex-wrap gap-gutter-sm">
    <a href="/wali/students" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Data Siswa</a>
    <a href="/wali/completeness" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white text-blue-700 font-label text-label-lg hover:bg-blue-50">Monitoring Kelengkapan</a>
  </div>
</section>

<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Wali Kelas</h1>
    <p class="font-body text-body-md text-on-surface-muted">Wali kelas: {{ $teacher->name }} — hanya kelas pada penugasan wali Anda yang tampil.</p>
  </div>
</section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/dashboard" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan</button>
</form>

@if($stats !== null)
<section aria-label="Statistik Utama" class="grid grid-cols-2 md:grid-cols-5 gap-gutter-lg items-stretch mb-6">
<div class="bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 rounded-md p-gutter-lg flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface-muted">Total Siswa</span>
<span class="font-display text-display-md text-blue-700 tabular-nums">{{ $stats['total_siswa'] }}</span>
</div>
<div class="bg-violet-50 border border-violet-200 border-l-4 border-l-violet-500 rounded-md p-gutter-lg flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface-muted">Rapor Lengkap</span>
<span class="font-display text-display-md text-violet-700 tabular-nums">{{ $stats['lengkap'] }}</span>
</div>
<div class="bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 rounded-md p-gutter-lg flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface-muted">Belum Lengkap</span>
<span class="font-display text-display-md text-emerald-700 tabular-nums">{{ $stats['belum_lengkap'] }}</span>
</div>
<div class="bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-md p-gutter-lg flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface-muted">Sudah Diajukan</span>
<span class="font-display text-display-md text-amber-700 tabular-nums">{{ $stats['diajukan'] }}</span>
</div>
<div class="bg-rose-50 border border-rose-200 border-l-4 border-l-rose-500 rounded-md p-gutter-lg flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface-muted">Nilai Belum Terkirim</span>
<span class="font-display text-display-md text-rose-700 tabular-nums">{{ $stats['nilai_belum_terkirim'] }}</span>
</div>
</section>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead class="bg-sidebar border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelengkapan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kekurangan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($checks as $check)
<tr class="even:bg-blue-50/50 hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $check['student']->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($check['complete'])<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Lengkap</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">Belum lengkap ({{ count($check['missing']) }} kekurangan)</span>@endif</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
@if($check['complete']) <span class="text-success">-</span>
@else
<ul class="list-disc pl-4">
@foreach($check['missing'] as $m)<li><a class="text-primary hover:underline" href="{{ $m['url'] }}">{{ $m['message'] }}</a></li>@endforeach
</ul>
@endif
</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md whitespace-nowrap">
<a href="/wali/preview/{{ $check['student']->id }}?academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-600 text-white text-xs font-semibold hover:bg-sky-700">Preview</a>
<a href="/wali/history/{{ $check['student']->id }}" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-600 text-white text-xs font-semibold hover:bg-sky-700">Riwayat</a>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted mt-4">Pilih kelas, tahun ajaran, dan semester untuk melihat ringkasan kelengkapan rapor kelas Anda.</p>
@endif

<div class="mt-4 font-body text-body-md flex flex-wrap gap-4">
<a href="/wali/students" class="text-primary hover:underline">Data Siswa</a>
<a href="/wali/completeness" class="text-primary hover:underline">Monitoring Kelengkapan</a>
<a href="/wali/notes" class="text-primary hover:underline">Catatan Wali Kelas</a>
<a href="/wali/achievements" class="text-primary hover:underline">Prestasi</a>
<a href="/wali/extracurriculars" class="text-primary hover:underline">Ekstrakurikuler</a>
<a href="/wali/assessments/recap" class="text-primary hover:underline">Rekap Nilai (baca saja)</a>
<a href="/wali/attendance/recap" class="text-primary hover:underline">Rekap Presensi (baca saja)</a>
</div>
@endsection
