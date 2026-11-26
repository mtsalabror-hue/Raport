@extends('layouts.app')

@section('title', 'Periksa Rapor - ' . $student->name)

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Periksa Rapor</h1>
        <p class="font-body text-body-md text-on-surface-muted">Pemeriksaan admin: hasil validasi kelengkapan, nilai, presensi, prestasi, ekstrakurikuler, dan catatan. Persetujuan hanya boleh dilakukan admin.</p>
    </div>
</section>

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container px-4 py-2 rounded mb-4 font-body text-body-md">
    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-success-container border border-success/30 text-on-success-container px-4 py-2 rounded mb-4 font-body text-body-md">{{ session('success') }}</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg font-body text-body-md text-on-surface">
    <p><strong>Nama:</strong> {{ $student->name }}</p>
    <p><strong>NIS:</strong> {{ $student->nis }} &middot; <strong>NISN:</strong> {{ $student->nisn }}</p>
    <p><strong>Kelas:</strong> {{ $report_card->schoolClass->name ?? '-' }} &middot; <strong>Tahun Ajaran:</strong> {{ $report_card->academicYear->name ?? '-' }} &middot; <strong>Semester:</strong> {{ $report_card->semester->name ?? '-' }}</p>
    <p><strong>Status rapor:</strong> {{ $document_label }} ({{ $report_card->status }})</p>
    <p><strong>Kelengkapan:</strong> {{ $check['complete'] ? 'Lengkap' : 'Belum lengkap ('.count($check['missing']).' kekurangan)' }}</p>
    @if(!$check['complete'])
    <ul class="list-disc pl-5 mt-2">
        @foreach($check['missing'] as $m)<li>{{ $m['message'] }}</li>@endforeach
    </ul>
    @endif
    @if($report_card->issued_at)<p><strong>Diterbitkan:</strong> {{ $report_card->issued_at->format('d-m-Y H:i') }} oleh {{ $report_card->issuer->name ?? '-' }}</p>@endif
    @if($report_card->locked_at)<p><strong>Dikunci:</strong> {{ $report_card->locked_at->format('d-m-Y H:i') }}</p>@endif
</div>

<h2 class="font-headline text-headline-md text-on-surface">Nilai Akhir Mata Pelajaran (data live)</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mata Pelajaran</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai Akhir</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">KKTP</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Ketuntasan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Deskripsi Capaian</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($finals as $row)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['subject']->name }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['kktp'] }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['passed'] === null ? '-' : ($row['passed'] ? 'Tuntas' : 'Belum tuntas') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['description'] ?? 'belum ada deskripsi' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada mata pelajaran yang diajarkan di kelas ini pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($snapshot->isNotEmpty())
<h2 class="font-headline text-headline-md text-on-surface">Snapshot Saat Penerbitan (tidak berubah bila data live berubah)</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mata Pelajaran</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai Akhir</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Deskripsi Capaian</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($snapshot as $row)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row->subject->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row->final_score !== null ? number_format($row->final_score, 2, ',', '.') : '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row->description ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<h2 class="font-headline text-headline-md text-on-surface">Presensi</h2>
<p class="font-body text-body-md text-on-surface">Hadir {{ $attendance['hadir'] }} &middot; Sakit {{ $attendance['sakit'] }} &middot; Izin {{ $attendance['izin'] }} &middot; Tanpa keterangan {{ $attendance['alpa'] }} &middot; Total {{ $attendance['total'] }} catatan &middot; Persentase hadir {{ $attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-' }}</p>

<h2 class="font-headline text-headline-md text-on-surface">Prestasi</h2>
@if($achievements->isNotEmpty())
<ul class="list-disc pl-5 font-body text-body-md text-on-surface">
    @foreach($achievements as $a)<li><strong>{{ $a->title }}</strong>@if($a->level) ({{ $a->level }})@endif @if($a->description) - {{ $a->description }}@endif</li>@endforeach
</ul>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada prestasi pada periode ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface">Ekstrakurikuler</h2>
@if($extracurriculars->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Kegiatan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nilai</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Keterangan</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($extracurriculars as $e)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $e->extracurricular->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $e->score ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $e->description ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada ekstrakurikuler pada periode ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface">Catatan Wali Kelas</h2>
<p class="font-body text-body-md text-on-surface">{{ $homeroom_note->content ?? 'Belum ada catatan wali kelas.' }}</p>

<h2 class="font-headline text-headline-md text-on-surface">Riwayat Persetujuan</h2>
@if($approvals->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Pemeriksa</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Keputusan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Alasan / Catatan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Waktu</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($approvals as $ap)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ap->approver->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ap->status === 'approved' ? 'Disetujui' : ($ap->status === 'rejected' ? 'Dikembalikan (Perlu Perbaikan)' : 'Menunggu') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ap->note ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ap->created_at->format('d-m-Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada keputusan persetujuan.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface">Riwayat Status</h2>
@if($history->isNotEmpty())
<ul class="list-disc pl-5 font-body text-body-md text-on-surface">
    @foreach($history as $h)
    <li>{{ $h->created_at->format('d-m-Y H:i') }} — {{ $h->action }} @if($h->user) ({{ $h->user->name }})@endif</li>
    @endforeach
</ul>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada riwayat status.</p>
@endif

<div class="flex flex-wrap gap-2">
    @if($report_card->status === 'diajukan')
    <form method="POST" action="/admin/report-cards/{{ $report_card->id }}/approve">
        @csrf
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-success text-on-success font-label text-label-lg">Setujui Rapor</button>
    </form>
    <form method="POST" action="/admin/report-cards/{{ $report_card->id }}/reject" class="flex gap-2">
        @csrf
        <input type="text" name="note" placeholder="Alasan pengembalian (wajib)" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary w-80">
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-error text-on-error font-label text-label-lg">Kembalikan</button>
    </form>
    @elseif($report_card->status === 'divalidasi')
    <form method="POST" action="/admin/report-cards/{{ $report_card->id }}/publish">
        @csrf
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-success text-on-success font-label text-label-lg">Terbitkan Rapor</button>
    </form>
    @elseif($report_card->status === 'diterbitkan')
    <form method="POST" action="/admin/report-cards/{{ $report_card->id }}/lock">
        @csrf
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Kunci Rapor</button>
    </form>
    <a href="/admin/report-cards/{{ $report_card->id }}/pdf" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Unduh PDF</a>
    @elseif($report_card->status === 'dikunci')
    <a href="/admin/report-cards/{{ $report_card->id }}/pdf" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Unduh PDF</a>
    <p class="font-body text-body-md text-on-surface-muted w-full">Rapor ini sudah terkunci dan tidak dapat diubah lagi.</p>
    @else
    <p class="font-body text-body-md text-on-surface-muted">Rapor ini masih berstatus draft ({{ $document_label }}) dan belum diajukan wali kelas.</p>
    @endif
</div>

<div class="mt-4 flex gap-gutter-md">
    <a href="/admin/report-cards/{{ $report_card->id }}/history" class="text-primary hover:underline">Riwayat Lengkap</a>
    <a href="/admin/report-cards" class="text-primary hover:underline">Daftar Pemeriksaan</a>
    <a href="/admin/dashboard" class="text-primary hover:underline">Dashboard Admin</a>
</div>
@endsection
