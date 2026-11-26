@extends('layouts.app')

@section('title', 'Riwayat Presensi')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Riwayat Presensi</h1></div></section>

@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/guru/attendance/history" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary" title="Dari tanggal">
<input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary" title="Sampai tanggal">
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="status" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Status</option>
@foreach(['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'] as $code => $label)
<option value="{{ $code }}" @selected(($filters['status'] ?? null) === $code)>{{ $label }}</option>
@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tanggal</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Kelas</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Catatan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Koreksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($records as $r)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->date->toDateString() }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->student?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->schoolClass?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ \App\Models\StudentAttendance::STATUSES[$r->status] ?? $r->status }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->note ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<form method="POST" action="/guru/attendance/{{ $r->id }}/correct" class="flex gap-1">
@csrf
<select name="status" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
@foreach(['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'] as $code => $label)
<option value="{{ $code }}" @selected($r->status === $code)>{{ $label }}</option>
@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-sm py-gutter-xs rounded bg-surface border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">Koreksi</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>

@if($records->isEmpty())
<p class="font-body text-body-md text-on-surface-muted mt-4">Tidak ada catatan presensi untuk filter ini.</p>
@endif

<div class="mt-4">{{ $records->links() }}</div>

<div class="mt-4 font-body text-body-md"><a href="/guru/attendance" class="text-primary hover:underline">Kembali ke Input Presensi</a></div>
</div>
@endsection
