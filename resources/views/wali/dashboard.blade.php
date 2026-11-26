<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Wali Kelas</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Dashboard Wali Kelas</h1>
<p class="text-gray-600 text-sm mb-4">Wali kelas: {{ $teacher->name }} — hanya kelas pada penugasan wali Anda yang tampil.</p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/dashboard" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
</form>

@if($stats !== null)
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6 text-sm">
<div class="bg-gray-50 border rounded p-3"><div class="text-gray-500">Total Siswa</div><div class="text-xl font-bold">{{ $stats['total_siswa'] }}</div></div>
<div class="bg-green-50 border rounded p-3"><div class="text-gray-500">Rapor Lengkap</div><div class="text-xl font-bold">{{ $stats['lengkap'] }}</div></div>
<div class="bg-yellow-50 border rounded p-3"><div class="text-gray-500">Belum Lengkap</div><div class="text-xl font-bold">{{ $stats['belum_lengkap'] }}</div></div>
<div class="bg-blue-50 border rounded p-3"><div class="text-gray-500">Sudah Diajukan</div><div class="text-xl font-bold">{{ $stats['diajukan'] }}</div></div>
<div class="bg-red-50 border rounded p-3"><div class="text-gray-500">Nilai Belum Terkirim</div><div class="text-xl font-bold">{{ $stats['nilai_belum_terkirim'] }}</div></div>
</div>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Kelengkapan</th>
<th class="border px-2 py-2 text-left">Kekurangan</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($checks as $check)
<tr>
<td class="border px-2 py-1">{{ $check['student']->name }}</td>
<td class="border px-2 py-1">{{ $check['complete'] ? 'Lengkap' : 'Belum lengkap ('.count($check['missing']).' kekurangan)' }}</td>
<td class="border px-2 py-1">
@if($check['complete']) <span class="text-green-700">-</span>
@else
<ul class="list-disc pl-4">
@foreach($check['missing'] as $m)<li><a class="text-blue-600 underline" href="{{ $m['url'] }}">{{ $m['message'] }}</a></li>@endforeach
</ul>
@endif
</td>
<td class="border px-2 py-1 whitespace-nowrap">
<a href="/wali/preview/{{ $check['student']->id }}?academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="text-blue-600 underline">Preview</a>
&middot;
<a href="/wali/history/{{ $check['student']->id }}" class="text-blue-600 underline">Riwayat</a>
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500 mt-4">Pilih kelas, tahun ajaran, dan semester untuk melihat ringkasan kelengkapan rapor kelas Anda.</p>
@endif

<div class="mt-4 text-sm flex flex-wrap gap-4">
<a href="/wali/students" class="text-blue-600 underline">Data Siswa</a>
<a href="/wali/completeness" class="text-blue-600 underline">Monitoring Kelengkapan</a>
<a href="/wali/notes" class="text-blue-600 underline">Catatan Wali Kelas</a>
<a href="/wali/achievements" class="text-blue-600 underline">Prestasi</a>
<a href="/wali/extracurriculars" class="text-blue-600 underline">Ekstrakurikuler</a>
<a href="/wali/assessments/recap" class="text-blue-600 underline">Rekap Nilai (baca saja)</a>
<a href="/wali/attendance/recap" class="text-blue-600 underline">Rekap Presensi (baca saja)</a>
</div>
</body>
</html>
