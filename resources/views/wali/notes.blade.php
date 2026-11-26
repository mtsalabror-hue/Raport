<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Catatan Wali Kelas</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Catatan Wali Kelas</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/notes" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Pilih Siswa</option>
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
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
</form>

@if($student)
<p class="text-sm mb-4">Siswa: <strong>{{ $student->name }}</strong> &middot; Status rapor: <strong>{{ $reportCard->status ?? 'belum ada rapor (akan dibuat sebagai draft saat catatan disimpan)' }}</strong></p>

<form method="POST" action="/wali/notes" class="mb-6">
@csrf
<input type="hidden" name="student_id" value="{{ $student->id }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<label class="block text-sm font-medium mb-1" for="content">Catatan baru</label>
<textarea id="content" name="content" rows="4" required class="border p-2 rounded w-full">{{ old('content') }}</textarea>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Catatan</button>
</form>

@if($notes->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Isi Catatan</th>
<th class="border px-2 py-2">Terakhir diubah</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($notes as $note)
<tr>
<td class="border px-2 py-1">{{ $note->content }}</td>
<td class="border px-2 py-1">{{ $note->updated_at }}</td>
<td class="border px-2 py-1 whitespace-nowrap">
<form method="POST" action="/wali/notes/{{ $note->id }}" class="inline">
@csrf
@method('PUT')
<input type="text" name="content" value="{{ $note->content }}" class="border p-1 rounded w-64">
<button class="text-blue-600 underline">Ubah</button>
</form>
<form method="POST" action="/wali/notes/{{ $note->id }}" class="inline ml-2" onsubmit="return confirm('Hapus catatan ini?')">
@csrf
@method('DELETE')
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500">Belum ada catatan wali kelas untuk siswa ini pada periode ini.</p>
@endif
@else
<p class="text-gray-500 mt-4">Pilih siswa, tahun ajaran, dan semester untuk mengelola catatan wali kelas.</p>
@endif

<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Kembali ke Dashboard</a></div>
</body>
</html>
