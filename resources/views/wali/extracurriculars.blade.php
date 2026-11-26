<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ekstrakurikuler</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Ekstrakurikuler Siswa</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/extracurriculars" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<form method="POST" action="/wali/extracurriculars" class="bg-gray-50 border rounded p-4 mb-6">
@csrf
<h2 class="font-bold mb-2">Catat Ekstrakurikuler Siswa</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="extracurricular_id" required class="border p-2 rounded">
<option value="">Pilih Ekstrakurikuler</option>
@foreach($masters as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
</select>
<input type="number" step="0.01" min="0" max="100" name="score" placeholder="Nilai (opsional)" class="border p-2 rounded w-32">
<select name="academic_year_id" required class="border p-2 rounded">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" required class="border p-2 rounded">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
</div>
<input type="text" name="description" placeholder="Keterangan (opsional)" class="border p-2 rounded w-full mt-2">
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan</button>
</form>

@if($records->isNotEmpty())
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Ekstrakurikuler</th>
<th class="border px-2 py-2">Nilai</th>
<th class="border px-2 py-2 text-left">Keterangan</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($records as $record)
<tr>
<td class="border px-2 py-1">{{ $record->student->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $record->extracurricular->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $record->score ?? '-' }}</td>
<td class="border px-2 py-1">{{ $record->description ?? '-' }}</td>
<td class="border px-2 py-1 whitespace-nowrap">
<form method="POST" action="/wali/extracurriculars/{{ $record->id }}" class="inline">
@csrf
@method('PUT')
<input type="number" step="0.01" min="0" max="100" name="score" value="{{ $record->score }}" class="border p-1 rounded w-20">
<button class="text-blue-600 underline">Ubah</button>
</form>
<form method="POST" action="/wali/extracurriculars/{{ $record->id }}" class="inline ml-2" onsubmit="return confirm('Hapus catatan ini?')">
@csrf
@method('DELETE')
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-4 mb-6">{{ $records->links() }}</div>
@else
<p class="text-gray-500 mb-6">Belum ada catatan ekstrakurikuler siswa pada filter ini.</p>
@endif

<h2 class="font-bold mb-2">Data Master Ekstrakurikuler</h2>
<form method="POST" action="/wali/extracurriculars/master" class="flex flex-wrap gap-2 mb-4">
@csrf
<input type="text" name="name" required placeholder="Nama ekstrakurikuler baru" class="border p-2 rounded flex-1">
<input type="text" name="description" placeholder="Keterangan (opsional)" class="border p-2 rounded flex-1">
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tambah Master</button>
</form>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Nama</th>
<th class="border px-2 py-2 text-left">Keterangan</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($masters as $m)
<tr>
<td class="border px-2 py-1">{{ $m->name }}</td>
<td class="border px-2 py-1">{{ $m->description ?? '-' }}</td>
<td class="border px-2 py-1 whitespace-nowrap">
<form method="POST" action="/wali/extracurriculars/master/{{ $m->id }}" class="inline">
@csrf
@method('PUT')
<input type="text" name="name" value="{{ $m->name }}" class="border p-1 rounded w-40">
<button class="text-blue-600 underline">Ubah</button>
</form>
<form method="POST" action="/wali/extracurriculars/master/{{ $m->id }}" class="inline ml-2" onsubmit="return confirm('Hapus data master ini?')">
@csrf
@method('DELETE')
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>

<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Kembali ke Dashboard</a></div>
</body>
</html>
