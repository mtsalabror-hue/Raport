@extends('layouts.app')

@section('title', 'Komponen Penilaian')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Komponen &amp; Bobot Penilaian</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Total bobot komponen aktif dalam satu cakupan (mapel/kurikulum yang sama, atau global jika mapel dikosongkan) harus tepat 100 sebelum nilai akhir dapat dihitung. KKTP default 75, dapat diubah per komponen.</p></div></section>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif
@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
<form method="POST" action="/guru/components" class="flex flex-wrap gap-2">
@csrf
<input name="name" placeholder="Nama komponen" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30" required>
<select name="type" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="formatif">Formatif</option>
<option value="sumatif">Sumatif</option>
<option value="praktik">Praktik</option>
<option value="proyek">Proyek</option>
</select>
<input name="weight" type="number" min="0" max="100" step="0.01" placeholder="Bobot (%)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-28" required>
<select name="subject_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua mapel (global)</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
</select>
<input name="kktp" type="number" min="0" max="100" step="0.01" placeholder="KKTP (75)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-28">
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
</form>
</div>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nama</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Jenis</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Bobot</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">KKTP</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aktif</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($components as $c)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $c->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $c->type }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $c->weight }}%</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $c->subject?->name ?? 'Semua mapel' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $c->kktp }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($c->is_active)<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Ya</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">Tidak</span>@endif</td>
</tr>
@endforeach
</tbody>
</table></div></div>

<p class="font-body text-body-md text-on-surface-muted mt-4">Total bobot aktif (global): {{ $components->whereNull('subject_id')->where('is_active', true)->sum('weight') }}%</p>
<div class="mt-4 font-body text-body-md"><a href="/guru/assessments" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Input Nilai</a></div>
</div>
@endsection
