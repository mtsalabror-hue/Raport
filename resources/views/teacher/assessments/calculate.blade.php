@extends('layouts.app')

@section('title', 'Hasil Perhitungan')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Hasil Perhitungan Nilai Akhir</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Nilai akhir = (jumlah skor &times; bobot) dibagi (jumlah bobot komponen yang ada nilainya). Komponen kosong tidak dihitung sebagai 0. Perhitungan hanya berjalan jika total bobot komponen aktif tepat 100.</p></div></section>

@if(!empty($errors))
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors as $k => $e)<li>{{ $k }}: {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa - Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai Akhir</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($results as $k => $v)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $k }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $v }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>

@if(empty($results))
<p class="font-body text-body-md text-on-surface-muted mt-4">Belum ada nilai untuk dihitung.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/guru/assessments" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Input Nilai</a></div>
</div>
@endsection
