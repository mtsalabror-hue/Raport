@extends('layouts.app')

@section('title', 'Catatan Wali Kelas')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Catatan Wali Kelas</h1></div></section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/notes" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Siswa</option>
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
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan</button>
</form>

@if($student)
<p class="font-body text-body-md text-on-surface mb-4">Siswa: <strong>{{ $student->name }}</strong> &middot; Status rapor: <strong>{{ $reportCard->status ?? 'belum ada rapor (akan dibuat sebagai draft saat catatan disimpan)' }}</strong></p>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6">
<form method="POST" action="/wali/notes">
@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<div class="flex flex-col gap-gutter-xs">
<label class="font-label text-label-md text-on-surface" for="content">Catatan baru</label>
<textarea id="content" name="content" rows="4" required class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">{{ old('content') }}</textarea>
</div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan Catatan</button>
</form>
</div>

@if($notes->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Isi Catatan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Terakhir diubah</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($notes as $note)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $note->content }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $note->updated_at }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
<form method="POST" action="/wali/notes/{{ $note->id }}" class="inline">
@csrf
@method('PUT')
<input type="text" name="content" value="{{ $note->content }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-64">
<button class="text-primary hover:underline font-body text-body-md">Ubah</button>
</form>
<form method="POST" action="/wali/notes/{{ $note->id }}" class="inline ml-2" onsubmit="return confirm('Hapus catatan ini?')">
@csrf
@method('DELETE')
<button class="text-error hover:underline font-body text-body-md">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada catatan wali kelas untuk siswa ini pada periode ini.</p>
@endif
@else
<p class="font-body text-body-md text-on-surface-muted mt-4">Pilih siswa, tahun ajaran, dan semester untuk mengelola catatan wali kelas.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/wali/dashboard" class="text-primary hover:underline">Kembali ke Dashboard</a></div>
</div>
@endsection
