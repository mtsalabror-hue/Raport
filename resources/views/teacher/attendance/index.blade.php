@extends('layouts.app')

@section('title', 'Input Presensi')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Input Presensi</h1></div></section>

@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/guru/attendance" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan Siswa</button>
</form>

@if($students->isNotEmpty())
<form method="POST" action="/guru/attendance">
@csrf
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="date" value="{{ $filters['date'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] ?? '' }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] ?? '' }}">

<p class="font-body text-body-md text-on-surface-muted mb-4">Tanggal: <strong>{{ $filters['date'] }}</strong>. Satu baris per siswa; siswa yang sudah tercatat pada tanggal ini tidak dapat diinput ulang (gunakan koreksi di riwayat).</p>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Hadir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Sakit</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Izin</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tanpa Keterangan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Catatan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($students as $i => $student)
@php $done = $existing->get($student->id); @endphp
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
{{ $student->name }}
@if($done)
<span class="font-body text-body-sm text-on-surface-muted">(sudah tercatat: {{ $done->status }})</span>
@endif
</td>
@if($done)
<td colspan="4" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted text-center">Sudah tercatat</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $done->note ?? '-' }}</td>
@else
<input type="hidden" name="records[{{ $i }}][student_id]" value="{{ $student->id }}">
@foreach(['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'] as $code => $label)
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface text-center">
<input type="radio" name="records[{{ $i }}][status]" value="{{ $code }}" @checked($code === 'H') title="{{ $label }}">
</td>
@endforeach
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface"><input type="text" name="records[{{ $i }}][note]" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-full" placeholder="Opsional"></td>
@endif
</tr>
@endforeach
</tbody>
</table></div></div>

<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-4">Simpan Presensi</button>
</form>
@elseif(!empty($filters['class_id']) && !empty($filters['date']))
<p class="font-body text-body-md text-on-surface-muted mt-4">Tidak ada siswa di kelas ini.</p>
@else
<p class="font-body text-body-md text-on-surface-muted mt-4">Pilih kelas dan tanggal untuk menampilkan daftar siswa.</p>
@endif

<div class="mt-4 font-body text-body-md flex gap-4">
<a href="/guru/attendance/history" class="text-primary hover:underline">Riwayat Presensi</a>
<a href="/guru/attendance/recap" class="text-primary hover:underline">Rekap Presensi</a>
<a href="/guru/attendance/academic-recap" class="text-primary hover:underline">Rekap Akademik</a>
</div>
</div>
@endsection
