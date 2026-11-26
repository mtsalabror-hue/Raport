@extends('layouts.app')

@section('title', 'Pembina Ekstrakurikuler')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Pembina Ekstrakurikuler</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Tetapkan guru pembina untuk tiap jenis ekstrakurikuler. Guru yang ditunjuk dapat menilai siswa binaannya lewat menu "Ekstra Binaan".</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Ekstrakurikuler</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Keterangan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Pembina</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($masters as $m)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $m->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $m->description ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
<form method="POST" action="{{ route('admin.extracurriculars.supervisor.update', $m) }}" class="flex flex-wrap gap-2 items-center">
@csrf
@method('PUT')
<select name="supervisor_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">— Belum ada pembina —</option>
@foreach($teachers as $t)<option value="{{ $t->id }}" @selected($m->supervisor_id == $t->id)>{{ $t->name }}</option>@endforeach
</select>
<button class="inline-flex items-center px-2.5 py-1 rounded bg-primary text-on-primary font-label text-label-md hover:bg-primary-hover">Simpan Pembina</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="3" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada data master ekstrakurikuler. Master ditambahkan lewat halaman Ekstrakurikuler wali kelas.</td></tr>
@endforelse
</tbody>
</table></div></div>

<div class="mt-4 font-body text-body-md"><a href="/admin/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Dashboard Admin</a></div>
</div>
@endsection
