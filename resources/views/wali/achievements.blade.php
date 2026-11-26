<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Prestasi Siswa</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Prestasi Siswa</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/achievements" class="flex flex-wrap gap-2 mb-6">
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

<form method="POST" action="/wali/achievements" class="bg-gray-50 border rounded p-4 mb-6">
@csrf
<h2 class="font-bold mb-2">Tambah Prestasi</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<input type="text" name="title" required placeholder="Judul prestasi" class="border p-2 rounded flex-1">
<input type="text" name="level" placeholder="Tingkat (mis. Sekolah/Kabupaten)" class="border p-2 rounded">
<select name="academic_year_id" required class="border p-2 rounded">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" required class="border p-2 rounded">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
</div>
<textarea name="description" rows="2" placeholder="Keterangan (opsional)" class="border p-2 rounded w-full mt-2"></textarea>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Prestasi</button>
</form>

@if($items->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Prestasi</th>
<th class="border px-2 py-2">Tingkat</th>
<th class="border px-2 py-2 text-left">Keterangan</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($items as $item)
<tr>
<td class="border px-2 py-1">{{ $item->student->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $item->title }}</td>
<td class="border px-2 py-1">{{ $item->level ?? '-' }}</td>
<td class="border px-2 py-1">{{ $item->description ?? '-' }}</td>
<td class="border px-2 py-1 whitespace-nowrap">
<form method="POST" action="/wali/achievements/{{ $item->id }}" class="inline">
@csrf
@method('PUT')
<input type="text" name="title" value="{{ $item->title }}" class="border p-1 rounded w-40">
<input type="text" name="level" value="{{ $item->level }}" class="border p-1 rounded w-24">
<button class="text-blue-600 underline">Ubah</button>
</form>
<form method="POST" action="/wali/achievements/{{ $item->id }}" class="inline ml-2" onsubmit="return confirm('Hapus prestasi ini?')">
@csrf
@method('DELETE')
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="text-gray-500">Belum ada prestasi pada filter ini.</p>
@endif

<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Kembali ke Dashboard</a></div>
</body>
</html>
