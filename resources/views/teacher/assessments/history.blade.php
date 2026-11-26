@extends('layouts.app')

@section('title', 'Riwayat Perubahan Nilai')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Riwayat Perubahan Nilai</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div></div></section>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif
@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mt-6 mb-2">Pengajuan Revisi</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai Lama</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai Baru</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Alasan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Diajukan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($revisions as $r)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->score?->student?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->score?->subject?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->old_score }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->new_score }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->reason }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($r->status === 'diajukan')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">diajukan</span>
@elseif($r->status === 'disetujui')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">disetujui</span>
@elseif($r->status === 'ditolak')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">ditolak</span>
@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">{{ $r->status }}</span>
@endif</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->created_at }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
{{ $revisions->links() }}

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mt-6 mb-2">Log Aktivitas Nilai</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Model</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">ID</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Detail</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Waktu</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($logs as $log)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $log->action }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ class_basename($log->model_type) }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $log->model_id }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ json_encode($log->metadata) }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $log->created_at }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
{{ $logs->links() }}

<div class="mt-4 font-body text-body-md"><a href="/guru/assessments" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Input Nilai</a></div>
</div>
@endsection
