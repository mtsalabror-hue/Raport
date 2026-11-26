@extends('layouts.app')

@section('title', 'Leger Kelas')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Leger Kelas</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Rekap nilai akhir per siswa per mata pelajaran untuk satu kelas dan semester.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="{{ route('admin.ledger.index') }}" class="flex flex-wrap gap-2">
<select name="class_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan Leger</button>
</form>

@if($ledger)
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
<div class="flex flex-col gap-gutter-xs">
<h2 class="font-headline text-headline-md text-on-surface">Leger {{ $class->name }}</h2>
<p class="font-body text-body-md text-on-surface-muted">{{ $ledger['student_count'] }} siswa &middot; {{ $ledger['subjects']->count() }} mata pelajaran</p>
<p class="font-body text-body-md text-on-surface-muted">Sumber nilai: <span class="font-label text-label-md text-on-surface">{{ $ledger['source_label'] }}</span></p>
</div>
<div class="flex flex-wrap gap-gutter-sm">
<button type="button" onclick="window.print()" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-white border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim">Cetak</button>
<a href="{{ route('admin.ledger.pdf', ['class_id' => $filters['class_id'], 'academic_year_id' => $filters['academic_year_id'], 'semester_id' => $filters['semester_id']]) }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-emerald-600 text-white font-label text-label-lg hover:bg-emerald-700">Unduh PDF</a>
</div>
</section>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">No</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">NIS</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nama Siswa</span></th>
@foreach($ledger['subjects'] as $subject)
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">{{ $subject->name }}</span></th>
@endforeach
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Rata-rata</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($ledger['rows'] as $i => $row)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $i + 1 }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['student']->nis }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['student']->name }}</td>
@foreach($ledger['subjects'] as $subject)
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['scores'][$subject->id] !== null ? number_format($row['scores'][$subject->id], 2, ',', '.') : '-' }}</td>
@endforeach
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums font-semibold">{{ $row['average'] !== null ? number_format($row['average'], 2, ',', '.') : '-' }}</td>
</tr>
@empty
<tr><td colspan="{{ 4 + $ledger['subjects']->count() }}" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada siswa di kelas ini.</td></tr>
@endforelse
</tbody>
</table></div></div>
@elseif(!empty($filters['class_id']))
<p class="font-body text-body-md text-on-surface-muted">Pilih tahun ajaran dan semester untuk menampilkan leger.</p>
@else
<p class="font-body text-body-md text-on-surface-muted">Pilih kelas, tahun ajaran, dan semester untuk menampilkan leger.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/admin/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Dashboard Admin</a></div>
</div>
@endsection
