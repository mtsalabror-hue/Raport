@extends('layouts.app')

@section('title', 'Hasil Perhitungan')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Hasil Perhitungan Nilai Akhir</h1><p class="font-body text-body-md text-on-surface-muted">Nilai akhir = (jumlah skor &times; bobot) dibagi (jumlah bobot komponen yang ada nilainya). Komponen kosong tidak dihitung sebagai 0. Perhitungan hanya berjalan jika total bobot komponen aktif tepat 100.</p></div></section>

@if(!empty($errors))
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
<ul class="list-disc pl-5">@foreach($errors as $k => $e)<li>{{ $k }}: {{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa - Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai Akhir</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($results as $k => $v)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $k }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $v }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>

@if(empty($results))
<p class="font-body text-body-md text-on-surface-muted mt-4">Belum ada nilai untuk dihitung.</p>
@endif

<div class="mt-4 font-body text-body-md"><a href="/guru/assessments" class="text-primary hover:underline">Kembali ke Input Nilai</a></div>
</div>
@endsection
