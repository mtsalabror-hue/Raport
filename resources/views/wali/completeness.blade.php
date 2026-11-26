@extends('layouts.app')

@section('title', 'Monitoring Kelengkapan Rapor')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Monitoring Kelengkapan Rapor</h1></div></section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/completeness" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
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

@if($stats !== null)
<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6 font-body text-body-md text-on-surface">
<strong>Total {{ $stats['total'] }} siswa</strong> &middot; Lengkap: {{ $stats['lengkap'] }} &middot; Belum lengkap: {{ $stats['belum_lengkap'] }}
</div>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Kekurangan (klik untuk ke sumber data)</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($checks as $check)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $check['student']->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($check['complete'])<span class="inline-flex items-center px-gutter-sm py-gutter-xs rounded-full bg-success-container text-on-success-container font-label text-label-md">Lengkap</span>@else<span class="inline-flex items-center px-gutter-sm py-gutter-xs rounded-full bg-accent-container text-on-accent-container font-label text-label-md">Belum lengkap</span>@endif</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
@if($check['complete']) <span class="text-success">Tidak ada kekurangan.</span>
@else
<ul class="list-disc pl-4">
@foreach($check['missing'] as $m)<li><a class="text-primary hover:underline" href="{{ $m['url'] }}">{{ $m['message'] }}</a></li>@endforeach
</ul>
@endif
</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
<a href="/wali/preview/{{ $check['student']->id }}?academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="text-primary hover:underline">Preview</a>
@if($check['complete'] && ($check['report_card']->status ?? 'draft') === 'draft')
<form method="POST" action="/wali/submit" class="inline ml-2">
@csrf
<input type="hidden" name="student_id" value="{{ $check['student']->id }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="text-success hover:underline font-body text-body-md">Ajukan Rapor</button>
</form>
@elseif(($check['report_card']->status ?? null) === 'diajukan')
<span class="text-on-surface-muted ml-2">Sudah diajukan</span>
@endif
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted mt-4">Pilih kelas, tahun ajaran, dan semester untuk memantau kelengkapan rapor.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/wali/dashboard" class="text-primary hover:underline">Kembali ke Dashboard</a></div>
</div>
@endsection
