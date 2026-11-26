@extends('layouts.app')

@section('title', 'Ekstrakurikuler')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Ekstrakurikuler Siswa</h1></div></section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/extracurriculars" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6">
<form method="POST" action="/wali/extracurriculars">
@csrf
<h2 class="font-headline text-headline-md text-on-surface mb-2">Catat Ekstrakurikuler Siswa</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="extracurricular_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Ekstrakurikuler</option>
@foreach($masters as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
</select>
<input type="number" step="0.01" min="0" max="100" name="score" placeholder="Nilai (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-32">
<select name="academic_year_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
</div>
<input type="text" name="description" placeholder="Keterangan (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-full mt-2">
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan</button>
</form>
</div>

@if($records->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Ekstrakurikuler</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Keterangan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($records as $record)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $record->student->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $record->extracurricular->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $record->score ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $record->description ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
<form method="POST" action="/wali/extracurriculars/{{ $record->id }}" class="inline">
@csrf
@method('PUT')
<input type="number" step="0.01" min="0" max="100" name="score" value="{{ $record->score }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-20">
<button class="text-primary hover:underline font-body text-body-md">Ubah</button>
</form>
<form method="POST" action="/wali/extracurriculars/{{ $record->id }}" class="inline ml-2" onsubmit="return confirm('Hapus catatan ini?')">
@csrf
@method('DELETE')
<button class="text-error hover:underline font-body text-body-md">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<div class="mt-4 mb-6">{{ $records->links() }}</div>
@else
<p class="font-body text-body-md text-on-surface-muted mb-6">Belum ada catatan ekstrakurikuler siswa pada filter ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface mb-2">Data Master Ekstrakurikuler</h2>
<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-4">
<form method="POST" action="/wali/extracurriculars/master" class="flex flex-wrap gap-2">
@csrf
<input type="text" name="name" required placeholder="Nama ekstrakurikuler baru" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary flex-1">
<input type="text" name="description" placeholder="Keterangan (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary flex-1">
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tambah Master</button>
</form>
</div>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nama</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Keterangan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($masters as $m)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $m->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $m->description ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
<form method="POST" action="/wali/extracurriculars/master/{{ $m->id }}" class="inline">
@csrf
@method('PUT')
<input type="text" name="name" value="{{ $m->name }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-40">
<button class="text-primary hover:underline font-body text-body-md">Ubah</button>
</form>
<form method="POST" action="/wali/extracurriculars/master/{{ $m->id }}" class="inline ml-2" onsubmit="return confirm('Hapus data master ini?')">
@csrf
@method('DELETE')
<button class="text-error hover:underline font-body text-body-md">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>

<div class="mt-4 font-body text-body-md"><a href="/wali/dashboard" class="text-primary hover:underline">Kembali ke Dashboard</a></div>
</div>
@endsection
