@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Backup &amp; Restore</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Unduh cadangan seluruh data aplikasi sebagai satu file JSON, atau pulihkan data dari file cadangan. Cadangan berisi {{ count($tables) }} tabel aplikasi (tidak termasuk tabel cache, jobs, sessions, dan password_reset_tokens). Password pengguna ikut tercadangkan sebagai hash dan tidak pernah ditampilkan di halaman ini.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter-lg">
<section class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Unduh Cadangan</h2>
<p class="font-body text-body-md text-on-surface-muted">Satu file JSON berisi seluruh data: pengguna, guru, siswa, kelas, mapel, nilai, presensi, rapor, PKL, UKK, sikap, P5, kenaikan kelas, dan pengaturan. Simpan file ini di tempat yang aman; siapa pun yang memiliki file ini dapat memulihkan seluruh data termasuk akun pengguna.</p>
<a href="{{ route('admin.backup.download') }}" class="inline-flex items-center justify-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover self-start"><span class="material-symbols-outlined" aria-hidden="true">download</span>Unduh Backup (JSON)</a>
<p class="font-body text-body-sm text-on-surface-muted">Nama file: backup-rapor-YYYY-MM-DD-HHmm.json</p>
</section>

<section class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-error pl-3">Pulihkan dari Cadangan</h2>
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 font-body text-body-md"><span class="font-label font-bold">Peringatan:</span> restore mengganti <span class="font-label font-bold">seluruh data saat ini</span> dengan isi file cadangan — termasuk akun admin. Pastikan cadangan berisi akun admin yang masih bisa login. Bila cadangan tidak berisi akun admin yang aktif, restore ditolak dan data tidak berubah. File yang formatnya tidak valid juga ditolak tanpa mengubah data.</div>
<form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" class="flex flex-col gap-gutter-md">
@csrf
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface">File cadangan (.json)</span>
<input type="file" name="backup" accept=".json,application/json" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
</label>
<button type="submit" onclick="return confirm('Restore akan MENGGANTI seluruh data saat ini dengan isi file cadangan. Lanjutkan?')" class="inline-flex items-center justify-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-error text-on-error font-label text-label-lg hover:bg-rose-700 self-start"><span class="material-symbols-outlined" aria-hidden="true">restore</span>Pulihkan Data</button>
</form>
</section>
</div>

<section class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
<h2 class="font-headline text-headline-md text-on-surface">Tabel yang dicadangkan</h2>
<p class="font-body text-body-md text-on-surface-muted">{{ implode(', ', $tables) }}</p>
</section>
</div>
@endsection
