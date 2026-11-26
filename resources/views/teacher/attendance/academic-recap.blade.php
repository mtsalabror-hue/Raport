<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Akademik</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Akademik {{ ($readOnly ?? false) ? '(Wali Kelas - hanya baca, nilai terkirim/dikunci)' : '' }}</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="{{ ($readOnly ?? false) ? '/wali/attendance/academic-recap' : '/guru/attendance/academic-recap' }}" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="subject_id" class="border p-2 rounded">
<option value="">Pilih Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }} ({{ $s->code }})</option>@endforeach
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan Rekap</button>
</form>

@if($kktp !== null)
<p class="text-sm text-gray-600 mb-4">KKTP: {{ $kktp }}. Nilai akhir dihitung dari komponen yang ada nilainya saja (AssessmentService).</p>
@endif

@if($stats !== null)
<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
<strong>Ringkasan kelas:</strong>
{{ $stats['total_siswa'] }} siswa &middot;
Tuntas {{ $stats['tuntas'] }} &middot;
Belum tuntas {{ $stats['belum_tuntas'] }} &middot;
Belum dinilai {{ $stats['belum_dinilai'] }}
</div>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2">KKTP</th>
<th class="border px-2 py-2">Ketuntasan</th>
</tr>
</thead>
<tbody>
@foreach($students as $student)
@php $f = $finals[$student->id] ?? null; @endphp
<tr>
<td class="border px-2 py-1">{{ $student->name }}</td>
<td class="border px-2 py-1">{{ $f['final_score'] ?? '-' }}</td>
<td class="border px-2 py-1">{{ $kktp }}</td>
<td class="border px-2 py-1">
@if($f && $f['passed'] === true) Tuntas
@elseif($f && $f['passed'] === false) Belum tuntas
@else -
@endif
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500 mt-4">Pilih kelas, mapel, tahun ajaran, dan semester untuk menampilkan rekap akademik.</p>
@endif

<div class="mt-4 text-sm flex gap-4">
<a href="{{ ($readOnly ?? false) ? '/wali/attendance/recap' : '/guru/attendance/recap' }}" class="text-blue-600 underline">Rekap Presensi</a>
@if(!($readOnly ?? false))
<a href="/guru/attendance" class="text-blue-600 underline">Input Presensi</a>
@endif
</div>
</body>
</html>
