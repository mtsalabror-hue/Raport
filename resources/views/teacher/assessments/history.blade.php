@extends('layouts.app')

@section('title', 'Riwayat Perubahan Nilai')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Riwayat Perubahan Nilai</h1></div></section>

@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<h2 class="font-headline text-headline-md text-on-surface mt-6 mb-2">Pengajuan Revisi</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai Lama</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai Baru</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Alasan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Diajukan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($revisions as $r)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->score?->student?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->score?->subject?->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->old_score }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->new_score }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->reason }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->status }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $r->created_at }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
{{ $revisions->links() }}

<h2 class="font-headline text-headline-md text-on-surface mt-6 mb-2">Log Aktivitas Nilai</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Model</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">ID</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Detail</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Waktu</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($logs as $log)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
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

<div class="mt-4 font-body text-body-md"><a href="/guru/assessments" class="text-primary hover:underline">Kembali ke Input Nilai</a></div>
</div>
@endsection
