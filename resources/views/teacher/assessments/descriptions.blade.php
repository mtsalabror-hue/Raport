@extends('layouts.app')

@section('title', 'Deskripsi Capaian')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Deskripsi Capaian Pembelajaran</h1><p class="font-body text-body-md text-on-surface-muted">Kosongkan kolom deskripsi untuk membuat deskripsi otomatis dari nilai akhir dan KKTP. Isi sendiri untuk deskripsi yang lebih spesifik per capaian pembelajaran.</p></div></section>

@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
<form method="POST" action="/guru/descriptions">
@csrf
<div class="flex flex-wrap gap-2 mb-2">
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary" required>
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->nis }})</option>@endforeach
</select>
<select name="subject_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary" required>
<option value="">Pilih Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary" required>
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary" required>
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
</div>
<div class="flex flex-col gap-gutter-xs">
<textarea name="description" rows="4" placeholder="Deskripsi capaian (kosongkan untuk otomatis)" class="w-full max-w-2xl border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
</div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan Deskripsi</button>
</form>
</div>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Deskripsi</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Diperbarui</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($items as $item)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->student?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->subject?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->description }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $item->updated_at }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
{{ $items->links() }}

<div class="mt-4 font-body text-body-md"><a href="/guru/assessments" class="text-primary hover:underline">Kembali ke Input Nilai</a></div>
</div>
@endsection
