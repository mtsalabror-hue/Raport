@extends('layouts.app')

@section('title', 'Monitoring PKL')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Monitoring PKL</h1>
        <p class="font-body text-body-md text-on-surface-muted"><a href="/admin/internships" class="text-primary hover:underline">&larr; Daftar penempatan</a> &middot; <a href="/admin/internships/recap" class="text-primary hover:underline">Rekap Hasil PKL</a></p>
    </div>
</section>

<form method="GET" action="/admin/internships/monitoring" class="flex flex-wrap gap-2 mb-6">
    <select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua Siswa</option>
        @foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <select name="company_name" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua Perusahaan</option>
        @foreach($companies as $c)<option value="{{ $c }}" @selected(($filters['company_name'] ?? null) == $c)>{{ $c }}</option>@endforeach
    </select>
    <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

@foreach(['berjalan' => 'Berjalan / Aktif', 'mendatang' => 'Mendatang', 'selesai' => 'Selesai'] as $key => $label)
<h2 class="font-headline text-headline-md text-on-surface">{{ $label }} ({{ $groups[$key]->count() }})</h2>
@if($groups[$key]->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Perusahaan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Periode</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Pembimbing</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Grade</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata Aspek</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($groups[$key] as $p)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->student->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->company_name }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->start_date->format('Y-m-d') }} s.d. {{ $p->end_date->format('Y-m-d') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->supervisor ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->grade ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface"><a href="/admin/internships/{{ $p->id }}/assessments" class="text-primary hover:underline">Penilaian</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Tidak ada penempatan {{ strtolower($label) }}.</p>
@endif
@endforeach
@endsection
