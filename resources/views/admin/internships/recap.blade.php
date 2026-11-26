@extends('layouts.app')

@section('title', 'Rekap Hasil PKL')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Rekap Hasil PKL</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
        <p class="font-body text-body-md text-on-surface-muted"><a href="/admin/internships" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">&larr; Daftar penempatan</a> &middot; <a href="/admin/internships/monitoring" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-label text-label-md hover:bg-sky-200">Monitoring</a></p>
        <p class="font-body text-body-md text-on-surface-muted">Nilai PKL tidak otomatis masuk ke rapor (belum ada keputusan desain eksplisit). Rata-rata dihitung dari aspek yang sudah bernilai.</p>
    </div>
</section>

<form method="GET" action="/admin/internships/recap" class="flex flex-wrap gap-2 mb-6">
    <select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        <option value="">Semua Siswa</option>
        @foreach($allStudents as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <select name="company_name" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        <option value="">Semua Perusahaan</option>
        @foreach($companies as $c)<option value="{{ $c }}" @selected(($filters['company_name'] ?? null) == $c)>{{ $c }}</option>@endforeach
    </select>
    <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

@if(count($recaps))
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Penempatan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Selesai</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Berjalan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Rata-rata Gabungan</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($recaps as $r)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $students[$r['student_id']]->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
                        <ul class="list-disc pl-5">
                            @foreach($r['placements'] as $p)
                            <li>{{ $p['internship']->company_name }} ({{ $p['internship']->start_date->format('Y-m-d') }} s.d. {{ $p['internship']->end_date->format('Y-m-d') }}, <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $p['status'] === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($p['status'] === 'berjalan' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($p['status']) }}</span>) &mdash; rata-rata: {{ $p['average'] === null ? '-' : number_format($p['average'], 2, ',', '.') }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $r['finished'] }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $r['active'] }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $r['average'] === null ? '-' : number_format($r['average'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada data PKL untuk rekap.</p>
@endif
@endsection
