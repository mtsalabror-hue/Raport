@extends('layouts.app')

@section('title', 'PKL Bimbingan Saya')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">PKL Bimbingan Saya</h1><p class="font-body text-body-md text-on-surface-muted">Hanya penempatan yang kolom pembimbingnya berisi nama Anda ({{ $teacher->name }}) yang tampil di sini. <a href="/guru/competency-exams" class="text-primary hover:underline">Hasil UKK mapel yang saya ampu</a></p></div></section>

@if($items->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Perusahaan</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Periode</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata Aspek</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($items as $p)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->student->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->company_name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->start_date->format('Y-m-d') }} s.d. {{ $p->end_date->format('Y-m-d') }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ ucfirst($p->status()) }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.') }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface"><a href="/guru/internships/{{ $p->id }}" class="text-primary hover:underline">Lihat &amp; Nilai</a></td>
</tr>
@endforeach
</tbody>
</table></div></div>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada penempatan PKL dengan Anda sebagai pembimbing.</p>
@endif
</div>
@endsection
