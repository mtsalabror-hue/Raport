@extends('layouts.app')

@section('title', 'Rekap Sikap')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Rekap Sikap Sosial &amp; Spiritual</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Hanya-baca: sikap yang diisi wali kelas per siswa untuk satu kelas dan periode.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/admin/attitudes/recap" class="flex flex-wrap gap-2 mb-6">
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
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan</button>
</form>

@if($students->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Predikat Sosial</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi Sosial</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Predikat Spiritual</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi Spiritual</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($students as $s)
@php $row = $attitudes[$s->id] ?? collect(); @endphp
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $s->name }}<br><span class="text-on-surface-muted text-body-sm">NIS {{ $s->nis }}</span></td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['sosial']->predikat ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['sosial']->deskripsi ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['spiritual']->predikat ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['spiritual']->deskripsi ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@elseif(!empty($filters['class_id']))
<p class="font-body text-body-md text-on-surface-muted">Belum ada siswa di kelas ini pada periode ini.</p>
@else
<p class="font-body text-body-md text-on-surface-muted">Pilih kelas, tahun ajaran, dan semester untuk melihat rekap sikap.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/admin/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Dashboard</a></div>
</div>
@endsection
