@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Tambah Pengguna</h1>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="{{ route('admin.users.store') }}" class="flex flex-col gap-gutter-md">
        @csrf
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Nama</label>
            <input name="name" placeholder="Nama" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Email</label>
            <input name="email" type="email" placeholder="Email" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Password</label>
            <input name="password" type="password" placeholder="Password (min 8 karakter)" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Ulangi password</label>
            <input name="password_confirmation" type="password" placeholder="Ulangi password" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Role</label>
            <select name="role_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Pilih Role</option>@foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Guru</label>
            <select name="teacher_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Bukan Guru (tanpa data guru)</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
        </div>
        <label class="font-body text-body-md text-on-surface"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
        <div>
            <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
