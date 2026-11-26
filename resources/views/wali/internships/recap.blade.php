@extends('layouts.app')

@section('title', 'Rekap Hasil PKL Kelas Wali')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Rekap Hasil PKL Kelas Wali (Hanya Lihat)</h1><p class="font-body text-body-md text-on-surface-muted"><a href="/wali/internships" class="text-primary hover:underline">&larr; Daftar PKL</a></p></div></section>

<form method="GET" action="/wali/internships/recap" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Kelas Wali Saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Siswa</option>
@foreach($allStudents as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

@if(count($recaps))
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Penempatan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Selesai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Berjalan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata Gabungan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($recaps as $r)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $students[$r['student_id']]->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<ul class="list-disc pl-5">
@foreach($r['placements'] as $p)
<li>{{ $p['internship']->company_name }} ({{ $p['internship']->start_date->format('Y-m-d') }} s.d. {{ $p['internship']->end_date->format('Y-m-d') }}, {{ ucfirst($p['status']) }}) &mdash; rata-rata: {{ $p['average'] === null ? '-' : number_format($p['average'], 2, ',', '.') }}</li>
@endforeach
</ul>
</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $r['finished'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $r['active'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $r['average'] === null ? '-' : number_format($r['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada data PKL untuk rekap.</p>
@endif
</div>
@endsection
