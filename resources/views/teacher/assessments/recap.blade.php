<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Nilai</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Nilai {{ ($readOnly ?? false) ? '(Wali Kelas - hanya baca)' : '' }}</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="{{ ($readOnly ?? false) ? '/wali/assessments/recap' : '/guru/assessments/recap' }}" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="subject_id" class="border p-2 rounded">
<option value="">Semua Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }} ({{ $s->code }})</option>@endforeach
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Semua Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semua Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

@if($kktp !== null)
<p class="text-sm text-gray-600 mb-4">KKTP: {{ $kktp }}. Nilai akhir dihitung dari komponen yang ada nilainya saja, dengan bobot masing-masing.</p>
@endif

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Komponen</th>
<th class="border px-2 py-2">Nilai</th>
<th class="border px-2 py-2">Status</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2">KKTP</th>
</tr>
</thead>
<tbody>
@foreach($scores as $s)
<tr>
<td class="border px-2 py-1">{{ $s->student?->name }}</td>
<td class="border px-2 py-1">{{ $s->component?->name }}</td>
<td class="border px-2 py-1">{{ $s->score ?? '-' }}</td>
<td class="border px-2 py-1">{{ $s->status }}</td>
<td class="border px-2 py-1">{{ $finals[$s->student_id] ?? '-' }}</td>
<td class="border px-2 py-1">
@if(isset($finals[$s->student_id]) && $finals[$s->student_id] !== null && $kktp !== null)
{{ $finals[$s->student_id] >= $kktp ? 'Tercapai' : 'Belum tercapai' }}
@else
-
@endif
</td>
</tr>
@endforeach
</tbody>
</table>

@if($scores->isEmpty())
<p class="text-gray-500 mt-4">Tidak ada nilai untuk filter ini.</p>
@endif

<div class="mt-4 text-sm"><a href="/guru/assessments" class="text-blue-600 underline">Kembali ke Input Nilai</a></div>
</body>
</html>
