@extends('layouts.app')

@section('title', 'Riwayat Rapor - ' . $student->name)

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Riwayat Rapor</h1>
        <p class="font-body text-body-md text-on-surface-muted">{{ $student->name }} (NIS {{ $student->nis }}) &middot; {{ $report_card->schoolClass->name ?? '-' }} &middot; {{ $report_card->academicYear->name ?? '-' }} &middot; {{ $report_card->semester->name ?? '-' }} &middot; Status: {{ $report_card->documentLabel() }} ({{ $report_card->status }})</p>
    </div>
</section>

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

<h2 class="font-headline text-headline-md text-on-surface">Riwayat Status (ActivityLog)</h2>
@if($history->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Waktu</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Oleh</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Keterangan</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($history as $h)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
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
    <a href="/admin/report-cards/{{ $report_card->id }}" class="text-primary hover:underline">Kembali ke Detail Pemeriksaan</a>
    <a href="/admin/report-cards" class="text-primary hover:underline">Daftar Pemeriksaan</a>
</div>
@endsection
