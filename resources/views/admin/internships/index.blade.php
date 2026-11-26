@extends('layouts.app')

@section('title', 'Penempatan PKL')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Penempatan PKL</h1>
        <p class="font-body text-body-md text-on-surface-muted">Periode PKL ditentukan dari tanggal mulai s.d. tanggal selesai (tidak ada kolom tahun ajaran/semester pada tabel internships). <a href="/admin/internships/monitoring" class="text-primary hover:underline">Monitoring</a> &middot; <a href="/admin/internships/recap" class="text-primary hover:underline">Rekap Hasil PKL</a></p>
    </div>
</section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<h2 class="font-headline text-headline-md text-on-surface">Mitra Industri ({{ count($companies) }})</h2>
@if(count($companies))
<p class="font-body text-body-md text-on-surface">{{ implode(', ', $companies) }}</p>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada mitra industri. Mitra baru tercatat otomatis saat penempatan ditambahkan.</p>
@endif

<form method="GET" action="/admin/internships" class="flex flex-wrap gap-2 mb-6">
    <select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua Siswa</option>
        @foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <select name="company_name" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua Perusahaan</option>
        @foreach($companies as $c)<option value="{{ $c }}" @selected(($filters['company_name'] ?? null) == $c)>{{ $c }}</option>@endforeach
    </select>
    <select name="status" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua Status</option>
        @foreach(['mendatang' => 'Mendatang', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $v => $l)<option value="{{ $v }}" @selected(($filters['status'] ?? null) == $v)>{{ $l }}</option>@endforeach
    </select>
    <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/internships">
        @csrf
        <h2 class="font-headline text-headline-md text-on-surface">Tambah Penempatan PKL</h2>
        <div class="flex flex-wrap gap-2">
            <select name="student_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
                <option value="">Pilih Siswa</option>
                @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
            <input type="text" name="company_name" required list="companies" placeholder="Nama perusahaan / mitra industri" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary flex-1">
            <datalist id="companies">@foreach($companies as $c)<option value="{{ $c }}">@endforeach</datalist>
            <input type="date" name="start_date" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <input type="date" name="end_date" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <input type="text" name="supervisor" placeholder="Pembimbing (nama guru)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <input type="text" name="grade" placeholder="Nilai akhir / grade (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <p class="font-body text-body-sm text-on-surface-muted mt-2">Penempatan yang tanggalnya beririsan dengan penempatan lain siswa yang sama akan ditolak.</p>
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan Penempatan</button>
    </form>
</div>

@if($items->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Perusahaan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Periode</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Pembimbing</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Grade</span></th>
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
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->supervisor ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->grade ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface whitespace-nowrap">
                        <a href="/admin/internships/{{ $p->id }}/assessments" class="text-primary hover:underline">Penilaian</a>
                        <a href="/admin/internships/{{ $p->id }}/edit" class="text-primary hover:underline ml-2">Ubah</a>
                        <form method="POST" action="/admin/internships/{{ $p->id }}" class="inline ml-2" onsubmit="return confirm('Hapus penempatan ini beserta penilaian aspeknya?')">
                            @csrf @method('DELETE')
                            <button class="text-error hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada penempatan PKL.</p>
@endif
@endsection
