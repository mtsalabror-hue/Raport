@extends('layouts.app')

@section('title', 'Pengaturan Cetak')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Pengaturan Cetak</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
        <p class="font-body text-body-md text-on-surface-muted">Kota &amp; tanggal penerbitan, penanda tangan, dan watermark pada PDF rapor.</p>
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
    <form method="POST" action="{{ route('admin.print-settings.update') }}" class="flex flex-col gap-gutter-md">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-gutter-md">
            <div class="flex flex-col gap-gutter-xs">
                <label for="print_city" class="font-label text-label-md text-on-surface">Kota Penerbitan</label>
                <input id="print_city" name="print_city" value="{{ old('print_city', $settings['print_city']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <p class="font-body text-body-sm text-on-surface-muted">Kosongkan untuk memakai kota sekolah dari Profil Sekolah.</p>
            </div>
            <div class="flex flex-col gap-gutter-xs">
                <label for="print_date" class="font-label text-label-md text-on-surface">Tanggal Penerbitan</label>
                <input id="print_date" type="date" name="print_date" value="{{ old('print_date', $settings['print_date']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <p class="font-body text-body-sm text-on-surface-muted">Kosongkan untuk memakai tanggal hari ini saat PDF dibuat.</p>
            </div>
        </div>

        <h2 class="font-headline text-headline-md text-on-surface pt-gutter-md">Penanda Tangan</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-gutter-md">
            <div class="flex flex-col gap-gutter-xs">
                <label for="principal_name" class="font-label text-label-md text-on-surface">Nama Kepala Sekolah</label>
                <input id="principal_name" name="principal_name" value="{{ old('principal_name', $settings['principal_name']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            </div>
            <div class="flex flex-col gap-gutter-xs">
                <label for="principal_nip" class="font-label text-label-md text-on-surface">NIP Kepala Sekolah</label>
                <input id="principal_nip" name="principal_nip" value="{{ old('principal_nip', $settings['principal_nip']) }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
            </div>
        </div>
        <p class="font-body text-body-sm text-on-surface-muted">Nama dan NIP yang sama dengan Profil Sekolah; mengubah di sini juga mengubah profil.</p>

        <div class="flex items-center gap-gutter-sm">
            <input id="print_watermark" type="checkbox" name="print_watermark" value="1" @checked(old('print_watermark', $settings['print_watermark'])) class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary">
            <label for="print_watermark" class="font-label text-label-md text-on-surface">Tampilkan watermark nama sekolah pada PDF rapor</label>
        </div>

        <div>
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan Pengaturan</button>
        </div>
    </form>
</div>
@endsection
