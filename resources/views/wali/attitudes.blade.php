@extends('layouts.app')

@section('title', 'Sikap Sosial & Spiritual')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Sikap Sosial &amp; Spiritual</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Isi predikat dan deskripsi sikap per siswa untuk satu kelas dan periode. Sikap bukan komponen kelengkapan rapor — rapor tetap bisa diajukan walau sikap belum diisi.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="/wali/attitudes" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan</button>
</form>

@if($students->isNotEmpty())
<form method="POST" action="/wali/attitudes">
@csrf
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
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
@foreach($students as $i => $s)
@php $row = $existing[$s->id] ?? collect(); @endphp
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<input type="hidden" name="records[{{ $i }}][student_id]" value="{{ $s->id }}">
{{ $s->name }}<br><span class="text-on-surface-muted text-body-sm">NIS {{ $s->nis }}</span>
</td>
<td class="px-gutter-md py-gutter-md">
<select name="records[{{ $i }}][sosial][predikat]" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full">
<option value="">—</option>
@foreach($predikatOptions as $p)<option value="{{ $p }}" @selected(($row['sosial']->predikat ?? null) === $p)>{{ $p }}</option>@endforeach
</select>
</td>
<td class="px-gutter-md py-gutter-md">
<textarea name="records[{{ $i }}][sosial][deskripsi]" rows="2" placeholder="Deskripsi sikap sosial (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full">{{ $row['sosial']->deskripsi ?? '' }}</textarea>
</td>
<td class="px-gutter-md py-gutter-md">
<select name="records[{{ $i }}][spiritual][predikat]" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full">
<option value="">—</option>
@foreach($predikatOptions as $p)<option value="{{ $p }}" @selected(($row['spiritual']->predikat ?? null) === $p)>{{ $p }}</option>@endforeach
</select>
</td>
<td class="px-gutter-md py-gutter-md">
<textarea name="records[{{ $i }}][spiritual][deskripsi]" rows="2" placeholder="Deskripsi sikap spiritual (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full">{{ $row['spiritual']->deskripsi ?? '' }}</textarea>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan Sikap</button>
</form>
@elseif(!empty($filters['class_id']))
<p class="font-body text-body-md text-on-surface-muted">Belum ada siswa di kelas ini pada periode ini.</p>
@else
<p class="font-body text-body-md text-on-surface-muted">Pilih kelas, tahun ajaran, dan semester untuk mengisi sikap.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/wali/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Dashboard</a></div>
</div>
@endsection
