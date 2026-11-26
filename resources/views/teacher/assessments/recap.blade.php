@extends('layouts.app')

@section('title', 'Rekap Nilai')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Rekap Nilai {{ ($readOnly ?? false) ? '(Wali Kelas - hanya baca)' : '' }}</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="{{ ($readOnly ?? false) ? '/wali/assessments/recap' : '/guru/assessments/recap' }}" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="subject_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }} ({{ $s->code }})</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

@if($kktp !== null)
<p class="font-body text-body-md text-on-surface-muted mb-4">KKTP: {{ $kktp }}. Nilai akhir dihitung dari komponen yang ada nilainya saja, dengan bobot masing-masing.</p>
@endif

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Komponen</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai Akhir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">KKTP</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($scores as $s)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $s->student?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $s->component?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $s->score ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($s->status === 'draft')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">draft</span>
@elseif($s->status === 'terkirim')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">terkirim</span>
@elseif($s->status === 'dikunci')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-violet-100 text-violet-800">dikunci</span>
@elseif($s->status === 'perlu_revisi')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">perlu_revisi</span>
@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">{{ $s->status }}</span>
@endif</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $finals[$s->student_id] ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
@if(isset($finals[$s->student_id]) && $finals[$s->student_id] !== null && $kktp !== null)
@if($finals[$s->student_id] >= $kktp)<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Tercapai</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">Belum tercapai</span>@endif
@else
-
@endif
</td>
</tr>
@endforeach
</tbody>
</table></div></div>

@if($scores->isEmpty())
<p class="font-body text-body-md text-on-surface-muted mt-4">Tidak ada nilai untuk filter ini.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/guru/assessments" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Input Nilai</a></div>
</div>
@endsection
