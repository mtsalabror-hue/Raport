@extends('layouts.app')

@section('title', 'Hasil UKK Mapel Saya')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Hasil UKK (Mapel yang Saya Ampu)</h1><p class="font-body text-body-md text-on-surface-muted">Hanya-baca. Hanya jadwal untuk mapel yang Anda ampu (teaching_assignments) yang tampil. Tidak ada label lulus/tidak lulus — ambang kelulusan belum ditetapkan sekolah.</p></div></section>

<h2 class="font-headline text-headline-md text-on-surface mb-2">Jadwal Ujian</h2>
@if($schedules->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tanggal Ujian</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Peserta</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Sudah Dinilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($schedules as $sch)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['subject']->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['exam_date'] ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['participants'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['scored'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['average'] === null ? '-' : number_format($sch['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted mb-6">Belum ada jadwal UKK untuk mapel yang Anda ampu.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface mb-2">Rekap per Mapel</h2>
@if(count($perSubject))
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Peserta</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Sudah Dinilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($perSubject as $ps)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ps['subject']->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ps['participants'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ps['scored'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ps['average'] === null ? '-' : number_format($ps['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada data UKK untuk rekap.</p>
@endif
</div>
@endsection
