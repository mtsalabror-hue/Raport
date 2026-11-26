@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Profil Saya</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Ubah data diri dan password akun Anda. NIP dan data kepegawaian lain hanya dapat diubah admin.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
<form method="POST" action="{{ $action_url }}">
@csrf
@method('PUT')
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3 mb-4">Data Diri</h2>
<div class="flex flex-col gap-gutter-md max-w-xl">
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">Nama</span>
<input type="text" name="name" required value="{{ old('name', $teacher->name) }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">NIP</span>
<input type="text" value="{{ $teacher->nip }}" disabled class="border border-outline-variant rounded px-3 py-2 font-body text-body-md bg-surface-dim text-on-surface-muted">
</label>
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">Email Akun</span>
<input type="text" value="{{ $user->email }}" disabled class="border border-outline-variant rounded px-3 py-2 font-body text-body-md bg-surface-dim text-on-surface-muted">
</label>
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">Telepon</span>
<input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">Alamat</span>
<textarea name="address" rows="3" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">{{ old('address', $teacher->address) }}</textarea>
</label>
<div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan Profil</button>
</div>
</div>
</form>
</div>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
<form method="POST" action="{{ $password_url }}">
@csrf
@method('PUT')
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-amber-500 pl-3 mb-4">Ganti Password</h2>
<div class="flex flex-col gap-gutter-md max-w-xl">
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">Password Lama</span>
<input type="password" name="current_password" required autocomplete="current-password" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">Password Baru (minimal 8 karakter)</span>
<input type="password" name="password" required autocomplete="new-password" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-md text-on-surface">Konfirmasi Password Baru</span>
<input type="password" name="password_confirmation" required autocomplete="new-password" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<div>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-amber-600 text-white font-label text-label-lg hover:bg-amber-700">Ubah Password</button>
</div>
</div>
</form>
</div>

<div class="mt-4 font-body text-body-md"><a href="{{ $back_url }}" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-body text-body-md hover:bg-surface-dim">Kembali ke Dashboard</a></div>
</div>
@endsection
