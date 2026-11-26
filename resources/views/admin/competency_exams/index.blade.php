@extends('layouts.app')

@section('title', 'Jadwal UKK')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Jadwal Ujian Kompetensi (UKK)</h1>
        <p class="font-body text-body-md text-on-surface-muted"><a href="/admin/competency-exams/recap" class="text-primary hover:underline">Rekap Hasil UKK</a></p>
        <p class="font-body text-body-md text-on-surface-muted">Satu jadwal = satu mapel pada satu tanggal ujian. Penguji tidak punya kolom sendiri; catat lewat isian "Penguji" (disimpan sebagai baris "Penguji: ..." di kolom catatan). UKK tidak memakai ambang lulus — rekap hanya menampilkan skor.</p>
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

<form method="GET" action="/admin/competency-exams" class="flex flex-wrap gap-2 mb-6">
    <select name="subject_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        <option value="">Semua Mapel</option>
        @foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <input type="date" name="exam_date" value="{{ $filters['exam_date'] ?? '' }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
    <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/competency-exams">
        @csrf
        <h2 class="font-headline text-headline-md text-on-surface">Tambah Peserta ke Jadwal / Buat Jadwal Baru</h2>
        <div class="flex flex-wrap gap-2">
            <select name="student_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
                <option value="">Pilih Siswa</option>
                @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
            <select name="subject_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
                <option value="">Pilih Mapel</option>
                @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
            <input type="date" name="exam_date" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai (boleh kosong)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <input type="text" name="examiner" placeholder="Penguji (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <input type="text" name="note" placeholder="Catatan (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary flex-1">
        </div>
        <p class="font-body text-body-sm text-on-surface-muted mt-2">Siswa yang sudah terdaftar pada jadwal yang sama tidak diduplikasi — nilainya diperbarui.</p>
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan Peserta</button>
    </form>
</div>

@if($schedules->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mapel</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tanggal Ujian</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Peserta</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Sudah Dinilai</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($schedules as $sch)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['subject']->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['exam_date'] ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['participants'] }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['scored'] }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['average'] === null ? '-' : number_format($sch['average'], 2, ',', '.') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface"><a href="/admin/competency-exams/schedule?subject_id={{ $sch['subject_id'] }}&exam_date={{ $sch['exam_date'] }}" class="text-primary hover:underline">Peserta &amp; Input Hasil</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada jadwal UKK.</p>
@endif
@endsection
