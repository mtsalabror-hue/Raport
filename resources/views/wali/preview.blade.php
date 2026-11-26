<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Preview Rapor - {{ $student->name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Preview Rapor</h1>
<p class="text-gray-600 text-sm mb-4">Pratinjau hanya-baca untuk wali kelas. Nilai tidak dapat diubah dari halaman ini.</p>

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
<p><strong>Kelas:</strong> {{ $student->class_id }} &middot; <strong>Status rapor:</strong> {{ $reportCard->status ?? 'draft (belum ada rapor)' }}</p>
<p><strong>Kelengkapan:</strong> {{ $check['complete'] ? 'Lengkap' : 'Belum lengkap ('.count($check['missing']).' kekurangan)' }}</p>
@if(!$check['complete'])
<ul class="list-disc pl-5 mt-2">
@foreach($check['missing'] as $m)<li><a class="text-blue-600 underline" href="{{ $m['url'] }}">{{ $m['message'] }}</a></li>@endforeach
</ul>
@endif
</div>

<h2 class="font-bold mb-2">Nilai Akhir Mata Pelajaran</h2>
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
<p class="text-sm mb-6">{{ $homeroomNote->content ?? 'Belum ada catatan wali kelas.' }}</p>

@if($check['complete'] && ($reportCard->status ?? 'draft') === 'draft')
<form method="POST" action="/wali/submit">
@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<input type="hidden" name="class_id" value="{{ $student->class_id }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="bg-green-600 text-white px-4 py-2 rounded">Ajukan Rapor</button>
</form>
@elseif(($reportCard->status ?? null) === 'diajukan')
<p class="text-sm text-green-700">Rapor ini sudah diajukan dan menunggu pemeriksaan.</p>
@endif

<div class="mt-4 text-sm flex gap-4">
<a href="/wali/completeness?class_id={{ $student->class_id }}&academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="text-blue-600 underline">Monitoring Kelengkapan</a>
<a href="/wali/history/{{ $student->id }}" class="text-blue-600 underline">Riwayat Akademik</a>
<a href="/wali/dashboard" class="text-blue-600 underline">Dashboard</a>
</div>
</body>
</html>
