<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Siswa Kelas</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Data Siswa Kelas (Wali Kelas)</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/wali/students" class="flex flex-wrap gap-2 mb-6">
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

@if($students->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">NIS</th>
<th class="border px-2 py-2 text-left">Nama</th>
<th class="border px-2 py-2">Status Rapor</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($students as $student)
<tr>
<td class="border px-2 py-1">{{ $student->nis }}</td>
<td class="border px-2 py-1">{{ $student->name }}</td>
<td class="border px-2 py-1">{{ $reportCards[$student->id]->status ?? 'belum ada rapor' }}</td>
<td class="border px-2 py-1 whitespace-nowrap">
<a href="/wali/preview/{{ $student->id }}?academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="text-blue-600 underline">Preview</a>
&middot;
<a href="/wali/history/{{ $student->id }}" class="text-blue-600 underline">Riwayat</a>
&middot;
<a href="/wali/notes?student_id={{ $student->id }}&academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="text-blue-600 underline">Catatan</a>
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500 mt-4">Pilih kelas untuk menampilkan siswa. Hanya kelas pada penugasan wali Anda yang dapat dilihat.</p>
@endif

<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Kembali ke Dashboard</a></div>
</body>
</html>
