@extends('layouts.app')

@section('title', 'Isi Capaian P5 - ' . $project->tema)

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Isi Capaian P5</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Tulis capaian dan deskripsi P5 per siswa. P5 bukan komponen kelengkapan rapor — rapor tetap bisa diajukan walau P5 belum diisi.</p></div></section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6 font-body text-body-md text-on-surface">
<p><strong>Tema:</strong> {{ $project->tema }}</p>
<p><strong>Dimensi:</strong> {{ $project->dimensi }}</p>
<p><strong>Kelas:</strong> {{ $project->schoolClass->name ?? '-' }} &middot; <strong>Tahun Ajaran:</strong> {{ $project->academicYear->name ?? '-' }} &middot; <strong>Semester:</strong> {{ $project->semester->name ?? '-' }}</p>
<p><strong>Koordinator:</strong> {{ $project->coordinator->name ?? '-' }}</p>
</div>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

@if($students->isNotEmpty())
<form method="POST" action="{{ route('guru.p5.scores.store', $project) }}">
@csrf
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Capaian</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($students as $i => $s)
@php $row = $existing[$s->id] ?? null; @endphp
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<input type="hidden" name="records[{{ $i }}][student_id]" value="{{ $s->id }}">
{{ $s->name }}<br><span class="text-on-surface-muted text-body-sm">NIS {{ $s->nis }}</span>
</td>
<td class="px-gutter-md py-gutter-md">
<textarea name="records[{{ $i }}][capaian]" rows="2" placeholder="Capaian P5 siswa (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full">{{ $row->capaian ?? '' }}</textarea>
</td>
<td class="px-gutter-md py-gutter-md">
<textarea name="records[{{ $i }}][deskripsi]" rows="2" placeholder="Deskripsi capaian (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full">{{ $row->deskripsi ?? '' }}</textarea>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan Capaian P5</button>
</form>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada siswa di kelas proyek ini.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="{{ route('guru.p5.index') }}" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke P5 Saya</a></div>
</div>
@endsection
