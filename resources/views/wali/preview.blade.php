@extends('layouts.app')

@section('title')
Preview Rapor - {{ $student->name }}
@endsection

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Preview Rapor</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Pratinjau hanya-baca untuk wali kelas. Nilai tidak dapat diubah dari halaman ini.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6 font-body text-body-md text-on-surface">
<p><strong>Nama:</strong> {{ $student->name }}</p>
<p><strong>NIS:</strong> {{ $student->nis }} &middot; <strong>NISN:</strong> {{ $student->nisn }}</p>
<p><strong>Kelas:</strong> {{ $student->class_id }} &middot; <strong>Status rapor:</strong> @if(($reportCard->status ?? null) === null)<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">draft (belum ada rapor)</span>@elseif($reportCard->status === 'draft')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">draft</span>@elseif($reportCard->status === 'diajukan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">diajukan</span>@elseif($reportCard->status === 'divalidasi')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">divalidasi</span>@elseif($reportCard->status === 'diterbitkan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">diterbitkan</span>@elseif($reportCard->status === 'dikunci')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-violet-100 text-violet-800">dikunci</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">{{ $reportCard->status }}</span>@endif</p>
<p><strong>Kelengkapan:</strong> @if($check['complete'])<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Lengkap</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">Belum lengkap ({{ count($check['missing']) }} kekurangan)</span>@endif</p>
<p><strong>Keputusan Kenaikan Kelas:</strong> @if($promotionDecision)<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $promotionDecision->status === 'naik' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $promotionDecision->label() }}</span>@elseif($promotionSemesterIsEven)Belum ditetapkan@else-@endif</p>
@if(!$check['complete'])
<ul class="list-disc pl-5 mt-2">
@foreach($check['missing'] as $m)<li><a class="text-primary hover:underline" href="{{ $m['url'] }}">{{ $m['message'] }}</a></li>@endforeach
</ul>
@endif
</div>

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-2">Nilai Akhir Mata Pelajaran</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mata Pelajaran</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai Akhir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">KKTP</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Ketuntasan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi Capaian</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($finals as $row)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['subject']->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($row['passed'] === true)<span class="font-semibold text-emerald-700">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim' }}</span>@elseif($row['passed'] === false)<span class="font-semibold text-rose-700">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim' }}</span>@else{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim' }}@endif</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['kktp'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($row['passed'] === null)-@elseif($row['passed'])<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Tuntas</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">Belum tuntas</span>@endif</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['description'] ?? 'belum ada deskripsi' }}</td>
</tr>
@empty
<tr><td colspan="5" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada mata pelajaran yang diajarkan di kelas ini pada periode ini.</td></tr>
@endforelse
</tbody>
</table></div></div>

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-2">Presensi</h2>
<p class="font-body text-body-md text-on-surface mb-6">Hadir {{ $attendance['hadir'] }} &middot; Sakit {{ $attendance['sakit'] }} &middot; Izin {{ $attendance['izin'] }} &middot; Tanpa keterangan {{ $attendance['alpa'] }} &middot; Total {{ $attendance['total'] }} catatan &middot; Persentase hadir {{ $attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-' }}</p>

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-2">Prestasi</h2>
@if($achievements->isNotEmpty())
<ul class="list-disc pl-5 font-body text-body-md text-on-surface mb-6">
@foreach($achievements as $a)<li><strong>{{ $a->title }}</strong>@if($a->level) ({{ $a->level }})@endif @if($a->description) - {{ $a->description }}@endif</li>@endforeach
</ul>
@else
<p class="font-body text-body-md text-on-surface-muted mb-6">Belum ada prestasi pada periode ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-2">Ekstrakurikuler</h2>
@if($extracurriculars->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kegiatan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Keterangan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($extracurriculars as $e)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
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

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-2">Sikap</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Sikap</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Predikat</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">Sikap Sosial</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $attitudes['sosial']->predikat ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $attitudes['sosial']->deskripsi ?? '-' }}</td>
</tr>
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">Sikap Spiritual</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $attitudes['spiritual']->predikat ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $attitudes['spiritual']->deskripsi ?? '-' }}</td>
</tr>
</tbody>
</table></div></div>

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-2">P5 (Projek Penguatan Profil Pelajar Pancasila)</h2>
@if($p5Scores->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Proyek (Tema)</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Dimensi</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Capaian</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($p5Scores as $p)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->project->tema ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->project->dimensi ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->capaian ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->deskripsi ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted mb-6">Belum ada nilai P5 pada periode ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-2">Catatan Wali Kelas</h2>
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
<p class="font-body text-body-md text-amber-800">Rapor ini sudah diajukan dan menunggu pemeriksaan.</p>
@endif

<div class="mt-4 font-body text-body-md flex gap-4">
<a href="/wali/completeness?class_id={{ $student->class_id }}&academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-body text-body-md hover:bg-sky-200">Monitoring Kelengkapan</a>
<a href="/wali/history/{{ $student->id }}" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-body text-body-md hover:bg-sky-200">Riwayat Akademik</a>
<a href="/wali/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Dashboard</a>
</div>
</div>
@endsection
