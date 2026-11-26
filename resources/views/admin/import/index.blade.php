@extends('layouts.app')

@section('title', 'Impor Data')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Impor Data</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Impor siswa dan guru dari file CSV (bukan Excel). Unduh dulu template-nya, isi, lalu unggah. Baris yang valid akan dibuat; baris yang gagal dilaporkan dengan nomor baris dan alasannya.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

@php $result = session('import_result'); @endphp
@if($result)
<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
<h2 class="font-headline text-headline-md text-on-surface">Hasil Impor {{ ucfirst($result['type']) }}</h2>
<p class="font-body text-body-md text-on-surface"><span class="font-label font-bold text-emerald-700">{{ $result['created'] }} baris berhasil</span> dibuat, <span class="font-label font-bold text-rose-700">{{ $result['failed'] }} baris gagal</span>.</p>
@if(!empty($result['errors']))
<div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar"><tr>
<th class="px-gutter-md py-gutter-sm"><span class="font-label text-label-md text-white uppercase">Baris</span></th>
<th class="px-gutter-md py-gutter-sm"><span class="font-label text-label-md text-white uppercase">Alasan Gagal</span></th>
</tr></thead>
<tbody class="divide-y divide-outline-variant">
@foreach($result['errors'] as $err)
<tr class="hover:bg-surface-dim even:bg-blue-50/50"><td class="px-gutter-md py-gutter-sm font-body text-body-md">{{ $err['row'] }}</td><td class="px-gutter-md py-gutter-sm font-body text-body-md">{{ $err['message'] }}</td></tr>
@endforeach
</tbody>
</table></div>
@endif
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter-lg">
<section class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Impor Siswa</h2>
<p class="font-body text-body-md text-on-surface-muted">Kolom: nis, nisn, nama, gender, kelas, tanggal_lahir, alamat. NIS dan NISN wajib dan harus unik. Gender diisi L atau P. Kolom kelas diisi nama kelas yang sudah ada (mis. X TKJ 1); bila kelas tidak ditemukan, baris gagal. Tanggal lahir format YYYY-MM-DD.</p>
<a href="{{ route('admin.import.template-students') }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-surface border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim self-start"><span class="material-symbols-outlined" aria-hidden="true">download</span>Unduh Template Siswa (.csv)</a>
<form method="POST" action="{{ route('admin.import.students') }}" enctype="multipart/form-data" class="flex flex-col gap-gutter-md">
@csrf
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface">File CSV siswa</span>
<input type="file" name="file" accept=".csv,text/csv" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<button type="submit" class="inline-flex items-center justify-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover self-start"><span class="material-symbols-outlined" aria-hidden="true">upload_file</span>Impor Siswa</button>
</form>
</section>

<section class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-brand-emerald pl-3">Impor Guru</h2>
<p class="font-body text-body-md text-on-surface-muted">Kolom: nip, nama, gender, telepon, alamat. NIP wajib dan harus unik, nama wajib, gender diisi L atau P, telepon dan alamat boleh kosong. Impor ini hanya membuat data guru — tanpa akun login. Akun login guru dibuat dari menu Pengguna.</p>
<a href="{{ route('admin.import.template-teachers') }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-surface border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim self-start"><span class="material-symbols-outlined" aria-hidden="true">download</span>Unduh Template Guru (.csv)</a>
<form method="POST" action="{{ route('admin.import.teachers') }}" enctype="multipart/form-data" class="flex flex-col gap-gutter-md">
@csrf
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface">File CSV guru</span>
<input type="file" name="file" accept=".csv,text/csv" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<button type="submit" class="inline-flex items-center justify-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-brand-emerald text-on-primary font-label text-label-lg hover:bg-emerald-700 self-start"><span class="material-symbols-outlined" aria-hidden="true">upload_file</span>Impor Guru</button>
</form>
</section>
</div>
</div>
@endsection
