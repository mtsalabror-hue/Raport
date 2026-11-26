@extends('layouts.app')

@section('title', 'Rekap Presensi')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Rekap Presensi {{ ($readOnly ?? false) ? '(Wali Kelas - hanya baca)' : '' }}</h1></div></section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="{{ ($readOnly ?? false) ? '/wali/attendance/recap' : '/guru/attendance/recap' }}" class="flex flex-wrap gap-2 mb-6">
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
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan Rekap</button>
</form>

@if($classTotal !== null)
<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6 font-body text-body-md text-on-surface">
<strong>Total Kelas:</strong>
Hadir {{ $classTotal['hadir'] }} &middot; Sakit {{ $classTotal['sakit'] }} &middot;
Izin {{ $classTotal['izin'] }} &middot; Tanpa keterangan {{ $classTotal['alpa'] }} &middot;
Total {{ $classTotal['total'] }} catatan
&middot; Persentase hadir {{ $classTotal['persentase_hadir'] !== null ? $classTotal['persentase_hadir'].'%' : '-' }}
</div>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Hadir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Sakit</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Izin</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tanpa Ket.</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Total</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">% Hadir</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($rows as $row)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['student']->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['hadir'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['sakit'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['izin'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['alpa'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['total'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['persentase_hadir'] !== null ? $row['persentase_hadir'].'%' : '-' }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted mt-4">Pilih kelas, tahun ajaran, dan semester untuk menampilkan rekap. Rekap memakai satu periode eksplisit dan tidak mencampur semester lain.</p>
@endif

<div class="mt-4 font-body text-body-md flex gap-4">
<a href="{{ ($readOnly ?? false) ? '/wali/attendance/academic-recap' : '/guru/attendance/academic-recap' }}" class="text-primary hover:underline">Rekap Akademik</a>
@if(!($readOnly ?? false))
<a href="/guru/attendance" class="text-primary hover:underline">Input Presensi</a>
@endif
</div>
</div>
@endsection
