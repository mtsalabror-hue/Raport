@extends('layouts.app')

@section('title', 'Penilaian PKL')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Penilaian PKL per Aspek</h1>
        <p class="font-body text-body-md text-on-surface-muted"><a href="/admin/internships" class="text-primary hover:underline">&larr; Daftar penempatan</a></p>
    </div>
</section>

<p class="font-body text-body-md text-on-surface"><strong>{{ $internship->student->name ?? '-' }}</strong> &middot; {{ $internship->company_name }}</p>
<p class="font-body text-body-md text-on-surface-muted">{{ $internship->start_date->format('Y-m-d') }} s.d. {{ $internship->end_date->format('Y-m-d') }} &middot; Status: {{ ucfirst($recap['status']) }} &middot; Pembimbing: {{ $internship->supervisor ?? '-' }} &middot; Grade: {{ $internship->grade ?? '-' }}</p>
<p class="font-body text-body-md text-on-surface">Rata-rata aspek: <strong>{{ $recap['average'] === null ? 'Belum ada nilai' : number_format($recap['average'], 2, ',', '.') }}</strong></p>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/internships/{{ $internship->id }}/assessments">
        @csrf
        <h2 class="font-headline text-headline-md text-on-surface">Tambah / Perbarui Penilaian Aspek</h2>
        <div class="flex flex-wrap gap-2">
            <input type="text" name="aspect" required list="aspects" placeholder="Aspek (mis. kedisiplinan, keterampilan, sikap)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary flex-1">
            <datalist id="aspects"><option value="kedisiplinan"><option value="keterampilan"><option value="sikap"><option value="kerjasama"><option value="tanggung jawab"></datalist>
            <input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai 0-100" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
            <input type="text" name="note" placeholder="Catatan (opsional)" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary flex-1">
        </div>
        <p class="font-body text-body-sm text-on-surface-muted mt-2">Aspek yang sama pada penempatan ini hanya satu baris; mengirim aspek yang sama akan memperbarui nilainya.</p>
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover mt-2">Simpan Penilaian</button>
    </form>
</div>

@if($internship->assessments->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aspek</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Catatan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($internship->assessments as $a)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $a->aspect }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $a->score === null ? '-' : number_format((float) $a->score, 2, ',', '.') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $a->note ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
                        <form method="POST" action="/admin/internships/assessments/{{ $a->id }}" class="inline-flex flex-wrap gap-1 items-center">
                            @csrf @method('PUT')
                            <input type="hidden" name="aspect" value="{{ $a->aspect }}">
                            <input type="number" name="score" step="0.01" min="0" max="100" value="{{ $a->score }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-24">
                            <input type="text" name="note" value="{{ $a->note }}" placeholder="Catatan" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
                            <button class="inline-flex items-center gap-gutter-sm px-gutter-sm py-gutter-xs rounded bg-primary text-on-primary font-label text-label-md hover:bg-primary-hover">Ubah</button>
                        </form>
                        <form method="POST" action="/admin/internships/assessments/{{ $a->id }}" class="inline ml-2" onsubmit="return confirm('Hapus penilaian aspek ini?')">
                            @csrf @method('DELETE')
                            <button class="text-error hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada penilaian aspek untuk penempatan ini.</p>
@endif
@endsection
