@extends('layouts.app')

@section('title', 'Periksa Rapor - ' . $student->name)

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Periksa Rapor</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
        <p class="font-body text-body-md text-on-surface-muted">Pemeriksaan admin: hasil validasi kelengkapan, nilai, presensi, prestasi, ekstrakurikuler, dan catatan. Persetujuan hanya boleh dilakukan admin.</p>
    </div>
</section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 mb-4 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">error</span>
    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 mb-4 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>{{ session('success') }}</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg font-body text-body-md text-on-surface">
    <p><strong>Nama:</strong> {{ $student->name }}</p>
    <p><strong>NIS:</strong> {{ $student->nis }} &middot; <strong>NISN:</strong> {{ $student->nisn }}</p>
    <p><strong>Kelas:</strong> {{ $report_card->schoolClass->name ?? '-' }} &middot; <strong>Tahun Ajaran:</strong> {{ $report_card->academicYear->name ?? '-' }} &middot; <strong>Semester:</strong> {{ $report_card->semester->name ?? '-' }}</p>
    <p><strong>Status rapor:</strong> <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $report_card->status === 'diterbitkan' || $report_card->status === 'divalidasi' ? 'bg-emerald-100 text-emerald-800' : ($report_card->status === 'diajukan' ? 'bg-amber-100 text-amber-800' : ($report_card->status === 'dikunci' ? 'bg-violet-100 text-violet-800' : 'bg-rose-100 text-rose-800')) }}">{{ $document_label }}</span> ({{ $report_card->status }})</p>
    <p><strong>Kelengkapan:</strong> @if($check['complete'])<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Lengkap</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">Belum lengkap ({{ count($check['missing']) }} kekurangan)</span>@endif</p>
    @if(!$check['complete'])
    <ul class="list-disc pl-5 mt-2">
        @foreach($check['missing'] as $m)<li>{{ $m['message'] }}</li>@endforeach
    </ul>
    @endif
    @if($report_card->issued_at)<p><strong>Diterbitkan:</strong> {{ $report_card->issued_at->format('d-m-Y H:i') }} oleh {{ $report_card->issuer->name ?? '-' }}</p>@endif
    @if($report_card->locked_at)<p><strong>Dikunci:</strong> {{ $report_card->locked_at->format('d-m-Y H:i') }}</p>@endif
</div>

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Nilai Akhir Mata Pelajaran (data live)</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mata Pelajaran</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai Akhir</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">KKTP</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Ketuntasan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi Capaian</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($finals as $row)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['subject']->name }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['kktp'] }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($row['passed'] === null)-@elseif($row['passed'])<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Tuntas</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">Belum tuntas</span>@endif</td>
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
<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Snapshot Saat Penerbitan (tidak berubah bila data live berubah)</h2>
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mata Pelajaran</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai Akhir</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Deskripsi Capaian</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($snapshot as $row)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
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

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Presensi</h2>
<p class="font-body text-body-md text-on-surface">Hadir {{ $attendance['hadir'] }} &middot; Sakit {{ $attendance['sakit'] }} &middot; Izin {{ $attendance['izin'] }} &middot; Tanpa keterangan {{ $attendance['alpa'] }} &middot; Total {{ $attendance['total'] }} catatan &middot; Persentase hadir {{ $attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-' }}</p>

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Prestasi</h2>
@if($achievements->isNotEmpty())
<ul class="list-disc pl-5 font-body text-body-md text-on-surface">
    @foreach($achievements as $a)<li><strong>{{ $a->title }}</strong>@if($a->level) ({{ $a->level }})@endif @if($a->description) - {{ $a->description }}@endif</li>@endforeach
</ul>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada prestasi pada periode ini.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Ekstrakurikuler</h2>
@if($extracurriculars->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kegiatan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Nilai</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Keterangan</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($extracurriculars as $e)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
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

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Catatan Wali Kelas</h2>
<p class="font-body text-body-md text-on-surface">{{ $homeroom_note->content ?? 'Belum ada catatan wali kelas.' }}</p>

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Riwayat Persetujuan</h2>
@if($approvals->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Pemeriksa</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Keputusan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Alasan / Catatan</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Waktu</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($approvals as $ap)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $ap->approver->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">@if($ap->status === 'approved')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">Disetujui</span>@elseif($ap->status === 'rejected')<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-100 text-rose-800">Dikembalikan (Perlu Perbaikan)</span>@else<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800">Menunggu</span>@endif</td>
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

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Riwayat Status</h2>
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
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-emerald-600 text-white font-label text-label-lg hover:bg-emerald-700">Setujui Rapor</button>
    </form>
    <form method="POST" action="/admin/report-cards/{{ $report_card->id }}/reject" class="flex gap-2">
        @csrf
        <input type="text" name="note" placeholder="Alasan pengembalian (wajib)" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-80">
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-error text-on-error font-label text-label-lg">Kembalikan</button>
    </form>
    @elseif($report_card->status === 'divalidasi')
    <form method="POST" action="/admin/report-cards/{{ $report_card->id }}/publish">
        @csrf
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-emerald-600 text-white font-label text-label-lg hover:bg-emerald-700">Terbitkan Rapor</button>
    </form>
    @elseif($report_card->status === 'diterbitkan')
    <form method="POST" action="/admin/report-cards/{{ $report_card->id }}/lock">
        @csrf
        <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Kunci Rapor</button>
    </form>
    <a href="/admin/report-cards/{{ $report_card->id }}/pdf" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-emerald-600 text-white font-label text-label-lg hover:bg-emerald-700">Unduh PDF</a>
    @elseif($report_card->status === 'dikunci')
    <a href="/admin/report-cards/{{ $report_card->id }}/pdf" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-emerald-600 text-white font-label text-label-lg hover:bg-emerald-700">Unduh PDF</a>
    <p class="font-body text-body-md text-on-surface-muted w-full">Rapor ini sudah terkunci dan tidak dapat diubah lagi.</p>
    @else
    <p class="font-body text-body-md text-on-surface-muted">Rapor ini masih berstatus draft (<span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">{{ $document_label }}</span>) dan belum diajukan wali kelas.</p>
    @endif
</div>

<div class="mt-4 flex gap-gutter-md">
    <a href="/admin/report-cards/{{ $report_card->id }}/history" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">Riwayat Lengkap</a>
    <a href="/admin/report-cards" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">Daftar Pemeriksaan</a>
    <a href="/admin/dashboard" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">Dashboard Admin</a>
</div>
@endsection
