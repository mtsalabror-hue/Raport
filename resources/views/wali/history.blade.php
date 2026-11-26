@extends('layouts.app')

@section('title')
Riwayat Akademik - {{ $student->name }}
@endsection

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Riwayat Akademik</h1><p class="font-body text-body-md text-on-surface-muted">Siswa: <strong>{{ $student->name }}</strong> (NIS {{ $student->nis }}) — hanya-baca, lintas semester.</p></div></section>

@forelse($periods as $period)
<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg mb-6">
<h2 class="font-headline text-headline-md text-on-surface mb-2">{{ $period['academic_year']->name ?? '-' }} &middot; Semester {{ $period['semester']->name ?? '-' }}</h2>

<h3 class="font-headline text-headline-sm text-on-surface mb-1">Nilai Akhir</h3>
@if(!empty($period['finals']))
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-4"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mata Pelajaran</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai Akhir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">KKTP</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($period['finals'] as $row)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['subject']->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['kktp'] }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted mb-4">Belum ada nilai pada periode ini.</p>
@endif

<h3 class="font-headline text-headline-sm text-on-surface mb-1">Presensi</h3>
<p class="font-body text-body-md text-on-surface mb-4">Hadir {{ $period['attendance']['hadir'] }} &middot; Sakit {{ $period['attendance']['sakit'] }} &middot; Izin {{ $period['attendance']['izin'] }} &middot; Tanpa keterangan {{ $period['attendance']['alpa'] }} &middot; Total {{ $period['attendance']['total'] }} catatan &middot; Persentase hadir {{ $period['attendance']['persentase_hadir'] !== null ? $period['attendance']['persentase_hadir'].'%' : '-' }}</p>

<h3 class="font-headline text-headline-sm text-on-surface mb-1">Prestasi</h3>
@if($period['achievements']->isNotEmpty())
<ul class="list-disc pl-5 font-body text-body-md text-on-surface">
@foreach($period['achievements'] as $a)<li><strong>{{ $a->title }}</strong>@if($a->level) ({{ $a->level }})@endif</li>@endforeach
</ul>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada prestasi pada periode ini.</p>
@endif
</div>
@empty
<p class="font-body text-body-md text-on-surface-muted">Belum ada riwayat akademik untuk siswa ini.</p>
@endforelse

<div class="mt-4 font-body text-body-md flex gap-4">
<a href="/wali/preview/{{ $student->id }}" class="text-primary hover:underline">Preview Rapor</a>
<a href="/wali/dashboard" class="text-primary hover:underline">Dashboard</a>
</div>
</div>
@endsection
