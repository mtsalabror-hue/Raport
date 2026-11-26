@extends('layouts.app')

@section('title', 'P5 Saya')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">P5 Saya</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Proyek P5 yang Anda koordinatori. Buka proyek untuk mengisi capaian tiap siswa.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tema</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Dimensi</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tahun Ajaran</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Semester</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($projects as $p)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->tema }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->dimensi }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->schoolClass->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->academicYear->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->semester->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<a href="{{ route('guru.p5.show', $p) }}" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-body text-body-md hover:bg-sky-200">Isi Capaian</a>
</td>
</tr>
@empty
<tr><td colspan="6" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada proyek P5 yang Anda koordinatori.</td></tr>
@endforelse
</tbody>
</table></div></div>

<div class="mt-4">{{ $projects->links() }}</div>

<div class="mt-4 font-body text-body-md"><a href="/guru/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Dashboard</a></div>
</div>
@endsection
