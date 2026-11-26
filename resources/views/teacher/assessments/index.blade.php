@extends('layouts.app')

@section('title', 'Input Nilai')

@section('content')
<div x-data="{ dirty: false }" class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Input Nilai</h1></div></section>

@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/guru/assessments" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="subject_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }} ({{ $s->code }})</option>@endforeach
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

@if($students->isEmpty())
<p class="font-body text-body-md text-on-surface-muted">Pilih kelas dan mapel untuk menampilkan daftar siswa dan kolom komponen penilaian.</p>
@else
<p class="font-body text-body-md text-on-surface-muted mb-2">Nilai kosong dihitung sebagai belum dinilai, bukan 0. Nilai yang sudah terkirim atau terkunci tidak dapat diubah langsung, ajukan revisi.</p>

<form method="POST" action="/guru/assessments/bulk" @submit="dirty = false">
@csrf
<input type="hidden" name="subject_id" value="{{ $filters['subject_id'] }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<input type="hidden" name="loaded_at" value="{{ $loadedAt }}">

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">NIS</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nama Siswa</span></th>
@foreach($components as $comp)
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">{{ $comp->name }}</span><br><span class="font-body text-body-sm text-on-surface-muted">{{ $comp->type }} &middot; {{ rtrim(rtrim(number_format($comp->weight, 2), '0'), '.') }}%</span></th>
@endforeach
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@php $i = 0; @endphp
@foreach($students as $student)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $student->nis }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $student->name }}</td>
@foreach($components as $comp)
@php $existing = $scores[$student->id.'-'.$comp->id] ?? null; @endphp
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<input type="hidden" name="scores[{{ $i }}][student_id]" value="{{ $student->id }}">
<input type="hidden" name="scores[{{ $i }}][component_id]" value="{{ $comp->id }}">
<input type="number" name="scores[{{ $i }}][score]" min="0" max="100" step="0.01"
value="{{ $existing?->score !== null ? $existing->score : '' }}"
@change="dirty = true"
{{ $existing && $existing->isLocked() ? 'disabled' : '' }}
class="w-20 border border-outline-variant rounded px-2 py-1 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary {{ $existing && $existing->isLocked() ? 'bg-surface-dim' : '' }}"
placeholder="-">
</td>
@php $i++; @endphp
@endforeach
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
@php $any = $scores->filter(fn($s) => $s->student_id === $student->id)->first(); @endphp
{{ $any ? $any->status : 'belum ada' }}
</td>
</tr>
@endforeach
</tbody>
</table></div></div>

<div class="flex gap-2 mt-4">
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover" :disabled="!dirty" :class="!dirty && 'opacity-50'">Simpan Draft</button>
<span x-show="dirty" class="font-body text-body-md text-on-accent-container self-center">Ada perubahan yang belum disimpan.</span>
</div>
</form>

<div class="flex flex-wrap gap-2 mt-4">
<form method="POST" action="/guru/assessments/submit" class="inline-block">
@csrf
<input type="hidden" name="subject_id" value="{{ $filters['subject_id'] }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover" onclick="return confirm('Kirim semua nilai draft pada filter ini? Nilai yang terkirim tidak dapat diubah langsung.')">Kirim Nilai</button>
</form>

<form method="POST" action="/guru/assessments/lock" class="inline-block">
@csrf
<input type="hidden" name="subject_id" value="{{ $filters['subject_id'] }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-surface border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim" onclick="return confirm('Kunci semua nilai yang sudah terkirim pada filter ini?')">Kunci Nilai Terkirim</button>
</form>
</div>
@endif

<div class="mt-6 font-body text-body-md">
<a href="/guru/assessments/recap" class="text-primary hover:underline">Rekap Nilai</a> &middot;
<a href="/guru/assessments/calculate" class="text-primary hover:underline">Hitung Nilai Akhir</a> &middot;
<a href="/guru/assessments/history" class="text-primary hover:underline">Riwayat Perubahan</a> &middot;
<a href="/guru/components" class="text-primary hover:underline">Komponen &amp; Bobot</a> &middot;
<a href="/guru/descriptions" class="text-primary hover:underline">Deskripsi Capaian</a>
</div>
</div>
@endsection
