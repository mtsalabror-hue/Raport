@extends('layouts.app')

@section('title', 'Catatan Wali Kelas')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Catatan Wali Kelas</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="/wali/notes" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
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

@if($student)
<p class="font-body text-body-md text-on-surface mb-4">Siswa: <strong>{{ $student->name }}</strong> &middot; Status rapor: @if(($reportCard->status ?? null) === null)<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">belum ada rapor (akan dibuat sebagai draft saat catatan disimpan)</span>@elseif($reportCard->status === 'draft')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">draft</span>@elseif($reportCard->status === 'diajukan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">diajukan</span>@elseif($reportCard->status === 'divalidasi')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">divalidasi</span>@elseif($reportCard->status === 'diterbitkan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">diterbitkan</span>@elseif($reportCard->status === 'dikunci')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-violet-100 text-violet-800">dikunci</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">{{ $reportCard->status }}</span>@endif</p>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6">
<form method="POST" action="/wali/notes">
@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<div class="flex flex-col gap-gutter-xs">
<label class="font-label text-label-md text-on-surface" for="content">Catatan baru</label>
<textarea id="content" name="content" rows="4" required class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">{{ old('content') }}</textarea>
</div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan Catatan</button>
</form>
</div>

@if($notes->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Isi Catatan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Terakhir diubah</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($notes as $note)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $note->content }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $note->updated_at }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
<form method="POST" action="/wali/notes/{{ $note->id }}" class="inline">
@csrf
@method('PUT')
<input type="text" name="content" value="{{ $note->content }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-64">
<button class="inline-flex items-center px-2.5 py-1 rounded bg-amber-100 text-amber-800 font-label text-label-md hover:bg-amber-200">Ubah</button>
</form>
<form method="POST" action="/wali/notes/{{ $note->id }}" class="inline ml-2" onsubmit="return confirm('Hapus catatan ini?')">
@csrf
@method('DELETE')
<button class="inline-flex items-center px-2.5 py-1 rounded bg-error text-on-error font-label text-label-md hover:opacity-90">Hapus</button>
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

<div class="mt-4 font-body text-body-md"><a href="/wali/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Dashboard</a></div>
</div>
@endsection
