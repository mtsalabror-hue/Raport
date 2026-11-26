@extends('layouts.app')

@section('title')
Preview Rapor - {{ $student->name }}
@endsection

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Preview Rapor</h1><p class="font-body text-body-md text-on-surface-muted">Pratinjau hanya-baca untuk wali kelas. Nilai tidak dapat diubah dari halaman ini.</p></div></section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6 font-body text-body-md text-on-surface">
<p><strong>Nama:</strong> {{ $student->name }}</p>
<p><strong>NIS:</strong> {{ $student->nis }} &middot; <strong>NISN:</strong> {{ $student->nisn }}</p>
<p><strong>Kelas:</strong> {{ $student->class_id }} &middot; <strong>Status rapor:</strong> {{ $reportCard->status ?? 'draft (belum ada rapor)' }}</p>
<p><strong>Kelengkapan:</strong> {{ $check['complete'] ? 'Lengkap' : 'Belum lengkap ('.count($check['missing']).' kekurangan)' }}</p>
@if(!$check['complete'])
<ul class="list-disc pl-5 mt-2">
@foreach($check['missing'] as $m)<li><a class="text-primary hover:underline" href="{{ $m['url'] }}">{{ $m['message'] }}</a></li>@endforeach
</ul>
@endif
</div>

<h2 class="font-headline text-headline-md text-on-surface mb-2">Nilai Akhir Mata Pelajaran</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mata Pelajaran</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai Akhir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">KKTP</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Ketuntasan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Deskripsi Capaian</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($finals as $row)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['subject']->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['kktp'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['passed'] === null ? '-' : ($row['passed'] ? 'Tuntas' : 'Belum tuntas') }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['description'] ?? 'belum ada deskripsi' }}</td>
</tr>
@empty
<tr><td colspan="5" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada mata pelajaran yang diajarkan di kelas ini pada periode ini.</td></tr>
@endforelse
</tbody>
</table></div></div>

<h2 class="font-headline text-headline-md text-on-surface mb-2">Presensi</h2>
<p class="font-body text-body-md text-on-surface mb-6">Hadir {{ $attendance['hadir'] }} &middot; Sakit {{ $attendance['sakit'] }} &middot; Izin {{ $attendance['izin'] }} &middot; Tanpa keterangan {{ $attendance['alpa'] }} &middot; Total {{ $attendance['total'] }} catatan &middot; Persentase hadir {{ $attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-' }}</p>

<h2 class="font-headline text-headline-md text-on-surface mb-2">Prestasi</h2>
@if($achievements->isNotEmpty())
<ul class="list-disc pl-5 font-body text-body-md text-on-surface mb-6">
@foreach($achievements as $a)<li><strong>{{ $a->title }}</strong>@if($a->level) ({{ $a->level }})@endif @if($a->description) - {{ $a->description }}@endif</li>@endforeach
</ul>
@else
<p class="font-body text-body-md text-on-surface-muted mb-6">Belum ada prestasi pada periode ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface mb-2">Ekstrakurikuler</h2>
@if($extracurriculars->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Kegiatan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Keterangan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($extracurriculars as $e)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $e->extracurricular->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $e->score ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $e->description ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted mb-6">Belum ada ekstrakurikuler pada periode ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface mb-2">Catatan Wali Kelas</h2>
<p class="font-body text-body-md text-on-surface mb-6">{{ $homeroomNote->content ?? 'Belum ada catatan wali kelas.' }}</p>

@if($check['complete'] && ($reportCard->status ?? 'draft') === 'draft')
<form method="POST" action="/wali/submit">
@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<input type="hidden" name="class_id" value="{{ $student->class_id }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Ajukan Rapor</button>
</form>
@elseif(($reportCard->status ?? null) === 'diajukan')
<p class="font-body text-body-md text-success">Rapor ini sudah diajukan dan menunggu pemeriksaan.</p>
@endif

<div class="mt-4 font-body text-body-md flex gap-4">
<a href="/wali/completeness?class_id={{ $student->class_id }}&academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="text-primary hover:underline">Monitoring Kelengkapan</a>
<a href="/wali/history/{{ $student->id }}" class="text-primary hover:underline">Riwayat Akademik</a>
<a href="/wali/dashboard" class="text-primary hover:underline">Dashboard</a>
</div>
</div>
@endsection
