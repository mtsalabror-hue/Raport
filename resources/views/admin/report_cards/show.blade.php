<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Periksa Rapor - {{ $student->name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Periksa Rapor</h1>
<p class="text-gray-600 text-sm mb-4">Pemeriksaan admin: hasil validasi kelengkapan, nilai, presensi, prestasi, ekstrakurikuler, dan catatan. Persetujuan hanya boleh dilakukan admin.</p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
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

<h2 class="font-bold mb-2">Nilai Akhir Mata Pelajaran (data live)</h2>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Mata Pelajaran</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2">KKTP</th>
<th class="border px-2 py-2">Ketuntasan</th>
<th class="border px-2 py-2 text-left">Deskripsi Capaian</th>
</tr>
</thead>
<tbody>
@forelse($finals as $row)
<tr>
<td class="border px-2 py-1">{{ $row['subject']->name }}</td>
<td class="border px-2 py-1">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim' }}</td>
<td class="border px-2 py-1">{{ $row['kktp'] }}</td>
<td class="border px-2 py-1">{{ $row['passed'] === null ? '-' : ($row['passed'] ? 'Tuntas' : 'Belum tuntas') }}</td>
<td class="border px-2 py-1">{{ $row['description'] ?? 'belum ada deskripsi' }}</td>
</tr>
@empty
<tr><td colspan="5" class="border px-2 py-1 text-gray-500">Belum ada mata pelajaran yang diajarkan di kelas ini pada periode ini.</td></tr>
@endforelse
</tbody>
</table>

@if($snapshot->isNotEmpty())
<h2 class="font-bold mb-2">Snapshot Saat Penerbitan (tidak berubah bila data live berubah)</h2>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Mata Pelajaran</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2 text-left">Deskripsi Capaian</th>
</tr>
</thead>
<tbody>
@foreach($snapshot as $row)
<tr>
<td class="border px-2 py-1">{{ $row->subject->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $row->final_score !== null ? number_format($row->final_score, 2, ',', '.') : '-' }}</td>
<td class="border px-2 py-1">{{ $row->description ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

<h2 class="font-bold mb-2">Presensi</h2>
<p class="text-sm mb-6">Hadir {{ $attendance['hadir'] }} &middot; Sakit {{ $attendance['sakit'] }} &middot; Izin {{ $attendance['izin'] }} &middot; Tanpa keterangan {{ $attendance['alpa'] }} &middot; Total {{ $attendance['total'] }} catatan &middot; Persentase hadir {{ $attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-' }}</p>

<h2 class="font-bold mb-2">Prestasi</h2>
@if($achievements->isNotEmpty())
<ul class="list-disc pl-5 text-sm mb-6">
@foreach($achievements as $a)<li><strong>{{ $a->title }}</strong>@if($a->level) ({{ $a->level }})@endif @if($a->description) - {{ $a->description }}@endif</li>@endforeach
</ul>
@else
<p class="text-sm text-gray-500 mb-6">Belum ada prestasi pada periode ini.</p>
@endif

<h2 class="font-bold mb-2">Ekstrakurikuler</h2>
@if($extracurriculars->isNotEmpty())
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Kegiatan</th>
<th class="border px-2 py-2">Nilai</th>
<th class="border px-2 py-2 text-left">Keterangan</th>
</tr>
</thead>
<tbody>
@foreach($extracurriculars as $e)
<tr>
<td class="border px-2 py-1">{{ $e->extracurricular->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $e->score ?? '-' }}</td>
<td class="border px-2 py-1">{{ $e->description ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-sm text-gray-500 mb-6">Belum ada ekstrakurikuler pada periode ini.</p>
@endif

<h2 class="font-bold mb-2">Catatan Wali Kelas</h2>
<p class="text-sm mb-6">{{ $homeroom_note->content ?? 'Belum ada catatan wali kelas.' }}</p>

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

<h2 class="font-bold mb-2">Riwayat Status</h2>
@if($history->isNotEmpty())
<ul class="list-disc pl-5 text-sm mb-6">
@foreach($history as $h)
<li>{{ $h->created_at->format('d-m-Y H:i') }} — {{ $h->action }} @if($h->user) ({{ $h->user->name }})@endif</li>
@endforeach
</ul>
@else
<p class="text-sm text-gray-500 mb-6">Belum ada riwayat status.</p>
@endif

<div class="flex flex-wrap gap-2 mb-6">
@if($report_card->status === 'diajukan')
<form method="POST" action="/admin/report-cards/{{ $report_card->id }}/approve">
@csrf
<button class="bg-green-600 text-white px-4 py-2 rounded">Setujui Rapor</button>
</form>
<form method="POST" action="/admin/report-cards/{{ $report_card->id }}/reject" class="flex gap-2">
@csrf
<input type="text" name="note" placeholder="Alasan pengembalian (wajib)" required class="border rounded px-2 py-2 w-80">
<button class="bg-red-600 text-white px-4 py-2 rounded">Kembalikan</button>
</form>
@elseif($report_card->status === 'divalidasi')
<form method="POST" action="/admin/report-cards/{{ $report_card->id }}/publish">
@csrf
<button class="bg-green-600 text-white px-4 py-2 rounded">Terbitkan Rapor</button>
</form>
@elseif($report_card->status === 'diterbitkan')
<form method="POST" action="/admin/report-cards/{{ $report_card->id }}/lock">
@csrf
<button class="bg-gray-800 text-white px-4 py-2 rounded">Kunci Rapor</button>
</form>
<a href="/admin/report-cards/{{ $report_card->id }}/pdf" class="bg-blue-600 text-white px-4 py-2 rounded">Unduh PDF</a>
@elseif($report_card->status === 'dikunci')
<a href="/admin/report-cards/{{ $report_card->id }}/pdf" class="bg-blue-600 text-white px-4 py-2 rounded">Unduh PDF</a>
<p class="text-sm text-gray-600 w-full">Rapor ini sudah terkunci dan tidak dapat diubah lagi.</p>
@else
<p class="text-sm text-gray-600">Rapor ini masih berstatus draft ({{ $document_label }}) dan belum diajukan wali kelas.</p>
@endif
</div>

<div class="mt-4 text-sm flex gap-4">
<a href="/admin/report-cards/{{ $report_card->id }}/history" class="text-blue-600 underline">Riwayat Lengkap</a>
<a href="/admin/report-cards" class="text-blue-600 underline">Daftar Pemeriksaan</a>
<a href="/admin/dashboard" class="text-blue-600 underline">Dashboard Admin</a>
</div>
</body>
</html>
