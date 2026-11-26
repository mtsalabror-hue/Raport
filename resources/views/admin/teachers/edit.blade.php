@extends('layouts.app')

@section('title', 'Edit Guru')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Edit Guru</h1>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/teachers/{{ $teacher->id }}" class="flex flex-col gap-gutter-md">
        @csrf @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label for="nip" class="font-label text-label-md text-on-surface">NIP</label>
            <input id="nip" name="nip" value="{{ $teacher->nip }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="name" class="font-label text-label-md text-on-surface">Nama</label>
            <input id="name" name="name" value="{{ $teacher->name }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="gender" class="font-label text-label-md text-on-surface">Jenis Kelamin</label>
            <select id="gender" name="gender" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="L" {{ $teacher->gender=='L'?'selected':'' }}>L</option><option value="P" {{ $teacher->gender=='P'?'selected':'' }}>P</option></select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="phone" class="font-label text-label-md text-on-surface">Telepon</label>
            <input id="phone" name="phone" value="{{ $teacher->phone }}" placeholder="Telepon" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="address" class="font-label text-label-md text-on-surface">Alamat</label>
            <input id="address" name="address" value="{{ $teacher->address }}" placeholder="Alamat" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
