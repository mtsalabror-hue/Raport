@extends('layouts.app')

@section('title', 'Ubah Penempatan PKL')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Ubah Penempatan PKL</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
        <p class="font-body text-body-md text-on-surface-muted"><a href="/admin/internships" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">&larr; Kembali ke daftar penempatan</a> &middot; <a href="/admin/internships/{{ $internship->id }}/assessments" class="inline-flex items-center px-2.5 py-1 rounded bg-sky-100 text-sky-800 font-label text-label-md hover:bg-sky-200">Penilaian per aspek</a></p>
    </div>
</section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 mb-4 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">error</span>
    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 mb-4 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>{{ session('success') }}</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg max-w-2xl">
    <form method="POST" action="/admin/internships/{{ $internship->id }}" class="flex flex-col gap-gutter-md">
        @csrf @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Siswa</label>
            <select name="student_id" required class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                @foreach($students as $s)<option value="{{ $s->id }}" @selected($internship->student_id == $s->id)>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Perusahaan / mitra industri</label>
            <input type="text" name="company_name" required list="companies" value="{{ $internship->company_name }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            <datalist id="companies">@foreach($companies as $c)<option value="{{ $c }}">@endforeach</datalist>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Tanggal mulai</label>
            <input type="date" name="start_date" required value="{{ $internship->start_date->format('Y-m-d') }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Tanggal selesai</label>
            <input type="date" name="end_date" required value="{{ $internship->end_date->format('Y-m-d') }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Pembimbing (nama guru)</label>
            <input type="text" name="supervisor" value="{{ $internship->supervisor }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Nilai akhir / grade (opsional)</label>
            <input type="text" name="grade" value="{{ $internship->grade }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <p class="font-body text-body-sm text-on-surface-muted">Periode baru tidak boleh beririsan dengan penempatan lain siswa yang sama. Status saat ini: <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $internship->status() === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($internship->status() === 'berjalan' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($internship->status()) }}</span>.</p>
        <div>
            <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
