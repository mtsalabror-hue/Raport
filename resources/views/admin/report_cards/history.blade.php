@extends('layouts.app')

@section('title', 'Riwayat Rapor - ' . $student->name)

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Riwayat Rapor</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
        <p class="font-body text-body-md text-on-surface-muted">{{ $student->name }} (NIS {{ $student->nis }}) &middot; {{ $report_card->schoolClass->name ?? '-' }} &middot; {{ $report_card->academicYear->name ?? '-' }} &middot; {{ $report_card->semester->name ?? '-' }} &middot; Status: <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $report_card->status === 'diterbitkan' || $report_card->status === 'divalidasi' ? 'bg-emerald-100 text-emerald-800' : ($report_card->status === 'diajukan' ? 'bg-amber-100 text-amber-800' : ($report_card->status === 'dikunci' ? 'bg-violet-100 text-violet-800' : 'bg-rose-100 text-rose-800')) }}">{{ $report_card->documentLabel() }}</span> ({{ $report_card->status }})</p>
    </div>
</section>

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

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Riwayat Status (ActivityLog)</h2>
@if($history->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Waktu</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Oleh</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Keterangan</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($history as $h)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $h->created_at->format('d-m-Y H:i') }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $h->action }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $h->user->name ?? '-' }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $h->metadata['reason'] ?? ($h->metadata['note'] ?? '-') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada riwayat status.</p>
@endif

<div class="mt-4 flex gap-gutter-md">
    <a href="/admin/report-cards/{{ $report_card->id }}" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">Kembali ke Detail Pemeriksaan</a>
    <a href="/admin/report-cards" class="inline-flex items-center px-2.5 py-1 rounded bg-white border border-outline-variant text-on-surface font-label text-label-md hover:bg-surface-dim">Daftar Pemeriksaan</a>
</div>
@endsection
