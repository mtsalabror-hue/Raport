@extends('layouts.app')

@section('title', 'Status Rapor Kelas Saya')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Status Rapor Kelas Saya</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Status rapor siswa di kelas wali Anda. PDF hanya tersedia untuk rapor yang sudah diterbitkan atau dikunci.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="/wali/report-cards" class="flex flex-wrap gap-2 mb-4">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua kelas saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua tahun ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="status" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua status</option>
@foreach(['draft' => 'Belum Lengkap', 'diajukan' => 'Diajukan', 'divalidasi' => 'Disetujui', 'diterbitkan' => 'Diterbitkan', 'dikunci' => 'Terkunci'] as $status => $label)
<option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ $label }}</option>
@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tahun Ajaran</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Semester</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($cards as $card)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->student->name ?? '-' }}<br><span class="font-body text-body-sm text-on-surface-muted">NIS {{ $card->student->nis ?? '-' }}</span></td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->schoolClass->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->academicYear->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->semester->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($card->status === 'draft')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">{{ $card->documentLabel() }}</span>
@elseif($card->status === 'diajukan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">{{ $card->documentLabel() }}</span>
@elseif($card->status === 'divalidasi')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">{{ $card->documentLabel() }}</span>
@elseif($card->status === 'diterbitkan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">{{ $card->documentLabel() }}</span>
@elseif($card->status === 'dikunci')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-violet-100 text-violet-800">{{ $card->documentLabel() }}</span>
@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">{{ $card->documentLabel() }}</span>
@endif</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<a href="/wali/preview/{{ $card->student_id }}?academic_year_id={{ $card->academic_year_id }}&semester_id={{ $card->semester_id }}" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-600 text-white font-body text-body-md hover:bg-sky-700">Preview</a>
| <a href="/wali/report-cards/{{ $card->id }}/cover" class="inline-flex items-center px-2.5 py-1 rounded bg-violet-600 text-white font-body text-body-md hover:bg-violet-700">Sampul</a>
| <a href="/wali/report-cards/{{ $card->id }}/identity" class="inline-flex items-center px-2.5 py-1 rounded bg-cyan-600 text-white font-body text-body-md hover:bg-cyan-700">Identitas</a>
@if($card->isPublished())
| <a href="/wali/report-cards/{{ $card->id }}/pdf" class="inline-flex items-center px-2.5 py-1 rounded bg-emerald-600 text-white font-body text-body-md hover:bg-emerald-700">Unduh PDF</a>
| <a href="/wali/report-cards/{{ $card->id }}/transcript" class="inline-flex items-center px-2.5 py-1 rounded bg-amber-600 text-white font-body text-body-md hover:bg-amber-700">Transkrip</a>
@endif
</td>
</tr>
@empty
<tr><td colspan="6" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada rapor pada filter ini.</td></tr>
@endforelse
</tbody>
</table></div></div>

<div class="mt-4">{{ $cards->links() }}</div>
<div class="mt-4 font-body text-body-md"><a href="/wali/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Dashboard Wali</a></div>
</div>
@endsection
