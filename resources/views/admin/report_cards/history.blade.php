<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Rapor - {{ $student->name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Riwayat Rapor</h1>
<p class="text-gray-600 text-sm mb-4">{{ $student->name }} (NIS {{ $student->nis }}) &middot; {{ $report_card->schoolClass->name ?? '-' }} &middot; {{ $report_card->academicYear->name ?? '-' }} &middot; {{ $report_card->semester->name ?? '-' }} &middot; Status: {{ $report_card->documentLabel() }} ({{ $report_card->status }})</p>

<h2 class="font-bold mb-2">Riwayat Persetujuan</h2>
@if($approvals->isNotEmpty())
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Pemeriksa</th>
<th class="border px-2 py-2 text-left">Keputusan</th>
<th class="border px-2 py-2 text-left">Alasan / Catatan</th>
<th class="border px-2 py-2 text-left">Waktu</th>
</tr>
</thead>
<tbody>
@foreach($approvals as $ap)
<tr>
<td class="border px-2 py-1">{{ $ap->approver->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $ap->status === 'approved' ? 'Disetujui' : ($ap->status === 'rejected' ? 'Dikembalikan (Perlu Perbaikan)' : 'Menunggu') }}</td>
<td class="border px-2 py-1">{{ $ap->note ?? '-' }}</td>
<td class="border px-2 py-1">{{ $ap->created_at->format('d-m-Y H:i') }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-sm text-gray-500 mb-6">Belum ada keputusan persetujuan.</p>
@endif

<h2 class="font-bold mb-2">Riwayat Status (ActivityLog)</h2>
@if($history->isNotEmpty())
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Waktu</th>
<th class="border px-2 py-2 text-left">Aksi</th>
<th class="border px-2 py-2 text-left">Oleh</th>
<th class="border px-2 py-2 text-left">Keterangan</th>
</tr>
</thead>
<tbody>
@foreach($history as $h)
<tr>
<td class="border px-2 py-1">{{ $h->created_at->format('d-m-Y H:i') }}</td>
<td class="border px-2 py-1">{{ $h->action }}</td>
<td class="border px-2 py-1">{{ $h->user->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $h->metadata['reason'] ?? ($h->metadata['note'] ?? '-') }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-sm text-gray-500 mb-6">Belum ada riwayat status.</p>
@endif

<div class="mt-4 text-sm flex gap-4">
<a href="/admin/report-cards/{{ $report_card->id }}" class="text-blue-600 underline">Kembali ke Detail Pemeriksaan</a>
<a href="/admin/report-cards" class="text-blue-600 underline">Daftar Pemeriksaan</a>
</div>
</body>
</html>
