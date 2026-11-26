@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Tambah Siswa</h1>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/students" class="flex flex-col gap-gutter-md">
        @csrf
        <div class="flex flex-col gap-gutter-xs">
            <label for="nis" class="font-label text-label-md text-on-surface">NIS</label>
            <input id="nis" name="nis" placeholder="NIS" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="nisn" class="font-label text-label-md text-on-surface">NISN</label>
            <input id="nisn" name="nisn" placeholder="NISN" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="name" class="font-label text-label-md text-on-surface">Nama</label>
            <input id="name" name="name" placeholder="Nama" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="gender" class="font-label text-label-md text-on-surface">Jenis Kelamin</label>
            <select id="gender" name="gender" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="L">L</option><option value="P">P</option></select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="class_id" class="font-label text-label-md text-on-surface">Kelas</label>
            <select id="class_id" name="class_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Tanpa Kelas</option>@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="birth_date" class="font-label text-label-md text-on-surface">Tanggal Lahir</label>
            <input id="birth_date" type="date" name="birth_date" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="address" class="font-label text-label-md text-on-surface">Alamat</label>
            <input id="address" name="address" placeholder="Alamat" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
