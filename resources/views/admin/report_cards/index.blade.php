@extends('layouts.app')

@section('title', 'Pemeriksaan Rapor')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Pemeriksaan Rapor</h1>
        <p class="font-body text-body-md text-on-surface-muted">Rapor yang diajukan wali kelas diperiksa di sini. Hanya admin yang dapat menyetujui, mengembalikan, menerbitkan, dan mengunci rapor.</p>
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

<div class="flex flex-wrap gap-gutter-sm">
    @foreach(['draft' => 'Belum Lengkap', 'diajukan' => 'Diajukan', 'divalidasi' => 'Disetujui', 'diterbitkan' => 'Diterbitkan', 'dikunci' => 'Terkunci'] as $status => $label)
    <span class="inline-flex items-center px-gutter-sm py-gutter-xs rounded-full bg-primary-container text-on-primary-container font-label text-label-md">{{ $label }}: <strong>&nbsp;{{ $counts[$status] ?? 0 }}</strong></span>
    @endforeach
</div>

<form method="GET" action="/admin/report-cards" class="flex flex-wrap gap-2 mb-6">
    <select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua kelas</option>
        @foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua tahun ajaran</option>
        @foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
    </select>
    <select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua semester</option>
        @foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <select name="status" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua status</option>
        @foreach(['draft' => 'Belum Lengkap', 'diajukan' => 'Diajukan', 'divalidasi' => 'Disetujui', 'diterbitkan' => 'Diterbitkan', 'dikunci' => 'Terkunci'] as $status => $label)
        <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Kelas</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tahun Ajaran</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Semester</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Status</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Diterbitkan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($cards as $card)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->student->name ?? '-' }}<br><span class="font-body text-body-sm text-on-surface-muted">NIS {{ $card->student->nis ?? '-' }}</span></td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->schoolClass->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->academicYear->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->semester->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->documentLabel() }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->issued_at ? $card->issued_at->format('d-m-Y H:i') : '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
                        <a href="/admin/report-cards/{{ $card->id }}" class="text-primary hover:underline">Periksa</a>
                        @if($card->isPublished())
                        | <a href="/admin/report-cards/{{ $card->id }}/pdf" class="text-primary hover:underline">PDF</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada rapor pada filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $cards->links() }}</div>
<div class="mt-4"><a href="/admin/dashboard" class="text-primary hover:underline">Dashboard Admin</a></div>
@endsection
