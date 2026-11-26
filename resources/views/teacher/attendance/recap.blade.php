@extends('layouts.app')

@section('title', 'Rekap Presensi')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Rekap Presensi {{ ($readOnly ?? false) ? '(Wali Kelas - hanya baca)' : '' }}</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="{{ ($readOnly ?? false) ? '/wali/attendance/recap' : '/guru/attendance/recap' }}" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan Rekap</button>
</form>

@if($classTotal !== null)
<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6 font-body text-body-md text-on-surface">
<strong>Total Kelas:</strong>
Hadir <span class="font-semibold text-emerald-700">{{ $classTotal['hadir'] }}</span> &middot; Sakit <span class="font-semibold text-amber-700">{{ $classTotal['sakit'] }}</span> &middot;
Izin <span class="font-semibold text-sky-700">{{ $classTotal['izin'] }}</span> &middot; Tanpa keterangan <span class="font-semibold text-rose-700">{{ $classTotal['alpa'] }}</span> &middot;
Total {{ $classTotal['total'] }} catatan
&middot; Persentase hadir {{ $classTotal['persentase_hadir'] !== null ? $classTotal['persentase_hadir'].'%' : '-' }}
</div>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Hadir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Sakit</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Izin</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tanpa Ket.</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Total</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">% Hadir</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($rows as $row)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['student']->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md font-semibold text-emerald-700">{{ $row['hadir'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md font-semibold text-amber-700">{{ $row['sakit'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md font-semibold text-sky-700">{{ $row['izin'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md font-semibold text-rose-700">{{ $row['alpa'] }}</td>
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
<a href="{{ ($readOnly ?? false) ? '/wali/attendance/academic-recap' : '/guru/attendance/academic-recap' }}" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-body text-body-md hover:bg-sky-200">Rekap Akademik</a>
@if(!($readOnly ?? false))
<a href="/guru/attendance" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-body text-body-md hover:bg-sky-200">Input Presensi</a>
@endif
</div>
</div>
@endsection
