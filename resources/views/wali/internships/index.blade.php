@extends('layouts.app')

@section('title', 'PKL Kelas Wali')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">PKL Siswa Kelas Wali (Hanya Lihat)</h1><p class="font-body text-body-md text-on-surface-muted"><a href="/wali/internships/recap" class="text-primary hover:underline">Rekap Hasil PKL</a> &middot; <a href="/wali/competency-exams" class="text-primary hover:underline">UKK kelas wali</a></p></div></section>

<form method="GET" action="/wali/internships" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Kelas Wali Saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

@if($items->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Perusahaan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Periode</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Pembimbing</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Grade</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata Aspek</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($items as $p)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->student->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->company_name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->start_date->format('Y-m-d') }} s.d. {{ $p->end_date->format('Y-m-d') }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ ucfirst($p->status()) }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->supervisor ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->grade ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada penempatan PKL untuk siswa kelas wali Anda.</p>
@endif
</div>
@endsection
