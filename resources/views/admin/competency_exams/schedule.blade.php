@extends('layouts.app')

@section('title', 'Jadwal UKK')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Jadwal UKK: {{ $subject->name }} &mdash; {{ $exam_date }}</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
        <p class="font-body text-body-md text-on-surface-muted"><a href="/admin/competency-exams" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">&larr; Daftar jadwal</a> &middot; <a href="/admin/competency-exams/recap" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-label text-label-md hover:bg-sky-200">Rekap Hasil UKK</a></p>
    </div>
</section>

<p class="font-body text-body-md text-on-surface">Peserta: <strong>{{ $participants->count() }}</strong> &middot; Rata-rata yang sudah dinilai: <strong>{{ $average === null ? '-' : number_format($average, 2, ',', '.') }}</strong></p>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 mb-4 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">error</span>
    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 mb-4 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>{{ session('success') }}</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/competency-exams">
        @csrf
        <h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Tambah Peserta ke Jadwal Ini</h2>
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="exam_date" value="{{ $exam_date }}">
        <div class="flex flex-wrap gap-2">
            <select name="student_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <option value="">Pilih Siswa</option>
                @foreach($candidates as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
            <input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai (boleh kosong)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            <input type="text" name="examiner" placeholder="Penguji (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            <input type="text" name="note" placeholder="Catatan (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 flex-1">
        </div>
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Tambah Peserta</button>
    </form>
</div>

@if($participants->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Penguji</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Catatan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($participants as $p)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->student->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->score === null ? '-' : number_format((float) $p->score, 2, ',', '.') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->examiner() ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->noteWithoutExaminer() ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
                        <form method="POST" action="/admin/competency-exams/{{ $p->id }}" class="inline-flex flex-wrap gap-1 items-center">
                            @csrf @method('PUT')
                            <input type="number" name="score" step="0.01" min="0" max="100" value="{{ $p->score }}" placeholder="Nilai" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-24">
                            <input type="text" name="examiner" value="{{ $p->examiner() }}" placeholder="Penguji" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                            <input type="text" name="note" value="{{ $p->noteWithoutExaminer() }}" placeholder="Catatan" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                            <button class="inline-flex items-center gap-gutter-sm px-gutter-sm py-gutter-xs rounded bg-primary text-on-primary font-label text-label-md hover:bg-primary-hover">Simpan Hasil</button>
                        </form>
                        <form method="POST" action="/admin/competency-exams/{{ $p->id }}" class="inline ml-2" onsubmit="return confirm('Hapus peserta ini dari jadwal?')">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center px-2.5 py-1 rounded bg-error text-on-error font-label text-label-md">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada peserta pada jadwal ini.</p>
@endif
@endsection
