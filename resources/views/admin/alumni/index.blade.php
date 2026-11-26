@extends('layouts.app')

@section('title', 'Alumni')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Alumni</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Daftar siswa yang sudah lulus (hanya-baca). Data alumni tetap tersimpan dan tidak dihapus.</p></div></section>

<form method="GET" action="{{ route('admin.alumni.index') }}" class="flex flex-wrap gap-2 mb-6">
<select name="alumni_year" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Tahun Lulus</option>
@foreach($years as $y)<option value="{{ $y }}" @selected(($filters['alumni_year'] ?? null) === $y)>{{ $y }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nama</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">NIS</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas Terakhir</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tahun Lulus</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($students as $s)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $s->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $s->nis }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $lastClasses[$s->id] ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $s->alumni_year ?? '-' }}</td>
</tr>
@empty
<tr><td colspan="4" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada alumni.</td></tr>
@endforelse
</tbody>
</table></div></div>

{{ $students->links() }}
</div>
@endsection
