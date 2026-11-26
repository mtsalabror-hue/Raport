@extends('layouts.app')

@section('title', 'PKL Kelas Wali')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">PKL Siswa Kelas Wali (Hanya Lihat)</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted"><a href="/wali/internships/recap" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-body text-body-md hover:bg-sky-200">Rekap Hasil PKL</a> &middot; <a href="/wali/competency-exams" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-body text-body-md hover:bg-sky-200">UKK kelas wali</a></p></div></section>

<form method="GET" action="/wali/internships" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Kelas Wali Saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

@if($items->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Perusahaan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Periode</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Pembimbing</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Grade</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Rata-rata Aspek</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($items as $p)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->student->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->company_name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->start_date->format('Y-m-d') }} s.d. {{ $p->end_date->format('Y-m-d') }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($p->status() === 'selesai')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Selesai</span>
@elseif($p->status() === 'berjalan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-sky-100 text-sky-800">Berjalan</span>
@elseif($p->status() === 'mendatang')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">Mendatang</span>
@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($p->status()) }}</span>
@endif</td>
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
