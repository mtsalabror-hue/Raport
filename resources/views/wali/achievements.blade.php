@extends('layouts.app')

@section('title', 'Prestasi Siswa')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Prestasi Siswa</h1></div></section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/achievements" class="flex flex-wrap gap-2 mb-6">
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
<form method="POST" action="/wali/achievements">
@csrf
<h2 class="font-headline text-headline-md text-on-surface mb-2">Tambah Prestasi</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<input type="text" name="title" required placeholder="Judul prestasi" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary flex-1">
<input type="text" name="level" placeholder="Tingkat (mis. Sekolah/Kabupaten)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<select name="academic_year_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
</div>
<textarea name="description" rows="2" placeholder="Keterangan (opsional)" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary mt-2"></textarea>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan Prestasi</button>
</form>
</div>

@if($items->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Prestasi</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tingkat</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Keterangan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($items as $item)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->student->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->title }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->level ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->description ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
<form method="POST" action="/wali/achievements/{{ $item->id }}" class="inline">
@csrf
@method('PUT')
<input type="text" name="title" value="{{ $item->title }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-40">
<input type="text" name="level" value="{{ $item->level }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-24">
<button class="text-primary hover:underline font-body text-body-md">Ubah</button>
</form>
<form method="POST" action="/wali/achievements/{{ $item->id }}" class="inline ml-2" onsubmit="return confirm('Hapus prestasi ini?')">
@csrf
@method('DELETE')
<button class="text-error hover:underline font-body text-body-md">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada prestasi pada filter ini.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/wali/dashboard" class="text-primary hover:underline">Kembali ke Dashboard</a></div>
</div>
@endsection
