@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Edit Pengguna</h1>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="{{ route('admin.users.update',$user) }}" class="flex flex-col gap-gutter-md">
        @csrf @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Nama</label>
            <input name="name" value="{{ $user->name }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Email</label>
            <input name="email" type="email" value="{{ $user->email }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Password baru</label>
            <input name="password" type="password" placeholder="Password baru (kosongkan bila tidak diubah)" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Ulangi password baru</label>
            <input name="password_confirmation" type="password" placeholder="Ulangi password baru" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Role</label>
            <select name="role_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">@foreach($roles as $r)<option value="{{ $r->id }}" {{ $user->role_id==$r->id?'selected':'' }}>{{ $r->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Guru</label>
            <select name="teacher_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Bukan Guru (tanpa data guru)</option>@foreach($teachers as $t)<option value="{{ $t->id }}" {{ $user->teacher_id==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select>
        </div>
        <label class="font-body text-body-md text-on-surface"><input type="checkbox" name="is_active" value="1" {{ $user->is_active?'checked':'' }}> Aktif</label>
        <div>
            <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
