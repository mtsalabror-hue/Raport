@extends('layouts.app')

@section('title', 'Kenaikan Kelas')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Kenaikan Kelas</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Tetapkan naik atau tidak naik per siswa untuk satu kelas. Keputusan kenaikan kelas hanya ditetapkan pada semester Genap; pada semester Ganjil form tidak aktif.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="/wali/promotions" class="flex flex-wrap gap-2 mb-6">
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
@if(!$isEven)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md mb-6"><span class="material-symbols-outlined" aria-hidden="true">info</span><span>Periode yang dipilih adalah semester Ganjil. Keputusan kenaikan kelas hanya dapat ditetapkan pada semester Genap — form di bawah tidak aktif.</span></div>
@endif
<form method="POST" action="/wali/promotions">
@csrf
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Keputusan</span></th>
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
<select name="records[{{ $i }}][status]" @disabled(!$isEven) class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full disabled:bg-surface-dim disabled:text-on-surface-muted">
<option value="">— Belum ditetapkan —</option>
@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected(($row->status ?? null) === $value)>{{ $label }}</option>@endforeach
</select>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<button @disabled(!$isEven) class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover disabled:bg-surface-container-high disabled:text-on-surface-muted">Simpan Keputusan</button>
</form>
@elseif(!empty($filters['class_id']))
<p class="font-body text-body-md text-on-surface-muted">Tidak ada siswa di kelas ini.</p>
@endif
</div>
@endsection
