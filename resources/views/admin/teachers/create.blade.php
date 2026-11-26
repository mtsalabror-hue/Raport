@extends('layouts.app')

@section('title', 'Tambah Guru')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Tambah Guru</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/teachers" class="flex flex-col gap-gutter-md">
        @csrf
        <div class="flex flex-col gap-gutter-xs">
            <label for="nip" class="font-label text-label-md text-on-surface">NIP</label>
            <input id="nip" name="nip" placeholder="NIP" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="name" class="font-label text-label-md text-on-surface">Nama</label>
            <input id="name" name="name" placeholder="Nama" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="gender" class="font-label text-label-md text-on-surface">Jenis Kelamin</label>
            <select id="gender" name="gender" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30"><option value="L">L</option><option value="P">P</option></select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="phone" class="font-label text-label-md text-on-surface">Telepon</label>
            <input id="phone" name="phone" placeholder="Telepon" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="address" class="font-label text-label-md text-on-surface">Alamat</label>
            <input id="address" name="address" placeholder="Alamat" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
