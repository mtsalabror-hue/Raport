@extends('layouts.app')

@section('title', 'Ubah Penempatan PKL')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Ubah Penempatan PKL</h1>
        <p class="font-body text-body-md text-on-surface-muted"><a href="/admin/internships" class="text-primary hover:underline">&larr; Kembali ke daftar penempatan</a> &middot; <a href="/admin/internships/{{ $internship->id }}/assessments" class="text-primary hover:underline">Penilaian per aspek</a></p>
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

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg max-w-2xl">
    <form method="POST" action="/admin/internships/{{ $internship->id }}" class="flex flex-col gap-gutter-md">
        @csrf @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Siswa</label>
            <select name="student_id" required class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
                @foreach($students as $s)<option value="{{ $s->id }}" @selected($internship->student_id == $s->id)>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Perusahaan / mitra industri</label>
            <input type="text" name="company_name" required list="companies" value="{{ $internship->company_name }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <datalist id="companies">@foreach($companies as $c)<option value="{{ $c }}">@endforeach</datalist>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Tanggal mulai</label>
            <input type="date" name="start_date" required value="{{ $internship->start_date->format('Y-m-d') }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Tanggal selesai</label>
            <input type="date" name="end_date" required value="{{ $internship->end_date->format('Y-m-d') }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Pembimbing (nama guru)</label>
            <input type="text" name="supervisor" value="{{ $internship->supervisor }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Nilai akhir / grade (opsional)</label>
            <input type="text" name="grade" value="{{ $internship->grade }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <p class="font-body text-body-sm text-on-surface-muted">Periode baru tidak boleh beririsan dengan penempatan lain siswa yang sama. Status saat ini: {{ ucfirst($internship->status()) }}.</p>
        <div>
            <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
