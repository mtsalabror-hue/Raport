@extends('layouts.app')

@section('title', 'Nilai Binaan - ' . $extracurricular->name)

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Nilai Binaan: {{ $extracurricular->name }}</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Pembina: {{ $teacher->name }}. Nilai dan deskripsi di sini memakai catatan yang sama dengan pengisian wali kelas — yang terakhir menyimpan menang, dan setiap perubahan tercatat di log aktivitas.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="{{ route('guru.supervised-extracurriculars.show', $extracurricular) }}" class="flex flex-wrap gap-2">
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter Periode</button>
</form>

@if($filters['academic_year_id'] && $filters['semester_id'])
<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
<form method="POST" action="{{ route('guru.supervised-extracurriculars.students.store', $extracurricular) }}" class="flex flex-wrap gap-2 items-center">
@csrf
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Tambah Siswa Binaan</h2>
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<select name="student_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 flex-1">
<option value="">Pilih Siswa</option>
@foreach($availableStudents as $s)<option value="{{ $s->id }}">{{ $s->name }} (NIS {{ $s->nis }})</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tambah</button>
</form>
</div>

@if($records->isNotEmpty())
<form method="POST" action="{{ route('guru.supervised-extracurriculars.scores.store', $extracurricular) }}">
@csrf
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($records as $i => $record)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $record->student->name ?? '-' }}<br><span class="font-body text-body-sm text-on-surface-muted">NIS {{ $record->student->nis ?? '-' }}</span></td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<input type="hidden" name="records[{{ $i }}][student_id]" value="{{ $record->student_id }}">
<input type="number" step="0.01" min="0" max="100" name="records[{{ $i }}][score]" value="{{ $record->score }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-24">
</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<input type="text" name="records[{{ $i }}][description]" value="{{ $record->description }}" placeholder="Keterangan (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full">
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-4">Simpan Nilai Binaan</button>
</form>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada siswa binaan pada periode ini. Tambahkan siswa lewat form di atas.</p>
@endif
@else
<p class="font-body text-body-md text-on-surface-muted">Pilih tahun ajaran dan semester untuk menampilkan siswa binaan.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="{{ route('guru.supervised-extracurriculars.index') }}" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Ekstra Binaan</a></div>
</div>
@endsection
