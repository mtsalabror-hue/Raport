@extends('layouts.app')

@section('title', 'Profil Sekolah')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Profil Sekolah</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
        <p class="font-body text-body-md text-on-surface-muted">Data ini tampil di sidebar, kop rapor, dan tanda tangan Kepala Sekolah pada PDF rapor.</p>
    </div>
</section>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container rounded-md px-4 py-3 font-body text-body-md">
    <ul class="list-disc pl-5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="{{ route('admin.school-profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-gutter-md">
        @csrf
        @method('PUT')

        <h2 class="font-headline text-headline-md text-on-surface">Identitas Sekolah</h2>

        <div class="flex flex-col gap-gutter-xs">
            <label for="school_name" class="font-label text-label-md text-on-surface">Nama Sekolah</label>
            <input id="school_name" name="school_name" value="{{ old('school_name', $profile['school_name']) }}" required class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-gutter-md">
            <div class="flex flex-col gap-gutter-xs">
                <label for="school_npsn" class="font-label text-label-md text-on-surface">NPSN</label>
                <input id="school_npsn" name="school_npsn" value="{{ old('school_npsn', $profile['school_npsn']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="flex flex-col gap-gutter-xs">
                <label for="school_phone" class="font-label text-label-md text-on-surface">Telepon</label>
                <input id="school_phone" name="school_phone" value="{{ old('school_phone', $profile['school_phone']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            </div>
        </div>

        <div class="flex flex-col gap-gutter-xs">
            <label for="school_address" class="font-label text-label-md text-on-surface">Alamat</label>
            <textarea id="school_address" name="school_address" rows="2" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">{{ old('school_address', $profile['school_address']) }}</textarea>
        </div>

        <div class="flex flex-col gap-gutter-xs">
            <label for="school_city" class="font-label text-label-md text-on-surface">Kota / Kabupaten</label>
            <input id="school_city" name="school_city" value="{{ old('school_city', $profile['school_city']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            <p class="font-body text-body-sm text-on-surface-muted">Dipakai sebagai kota penerbitan rapor bila kota cetak belum diisi.</p>
        </div>

        <div class="flex flex-col gap-gutter-xs">
            <label for="logo" class="font-label text-label-md text-on-surface">Logo Sekolah</label>
            @if($logoUrl)
            <div class="flex items-center gap-gutter-md">
                <img src="{{ $logoUrl }}" alt="Logo sekolah saat ini" class="h-16 w-16 object-contain bg-surface-dim border border-outline-variant rounded p-1">
                <span class="font-body text-body-sm text-on-surface-muted">Logo saat ini. Unggah file baru untuk menggantinya.</span>
            </div>
            @endif
            <input id="logo" type="file" name="logo" accept="image/*" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            <p class="font-body text-body-sm text-on-surface-muted">Gambar (JPG, PNG, dan sejenisnya), maksimal 2 MB. Logo tampil di sidebar dan kop PDF rapor.</p>
        </div>

        <h2 class="font-headline text-headline-md text-on-surface pt-gutter-md">Kepala Sekolah</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-gutter-md">
            <div class="flex flex-col gap-gutter-xs">
                <label for="principal_name" class="font-label text-label-md text-on-surface">Nama Kepala Sekolah</label>
                <input id="principal_name" name="principal_name" value="{{ old('principal_name', $profile['principal_name']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="flex flex-col gap-gutter-xs">
                <label for="principal_nip" class="font-label text-label-md text-on-surface">NIP Kepala Sekolah</label>
                <input id="principal_nip" name="principal_nip" value="{{ old('principal_nip', $profile['principal_nip']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            </div>
        </div>

        <div>
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan Profil</button>
        </div>
    </form>
</div>
@endsection
