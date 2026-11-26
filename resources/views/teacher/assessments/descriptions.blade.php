<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Deskripsi Capaian</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Deskripsi Capaian Pembelajaran</h1>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<p class="text-sm text-gray-600 mb-4">Kosongkan kolom deskripsi untuk membuat deskripsi otomatis dari nilai akhir dan KKTP. Isi sendiri untuk deskripsi yang lebih spesifik per capaian pembelajaran.</p>

<form method="POST" action="/guru/descriptions" class="mb-6">
@csrf
<div class="flex flex-wrap gap-2 mb-2">
<select name="student_id" class="border p-2 rounded" required>
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->nis }})</option>@endforeach
</select>
<select name="subject_id" class="border p-2 rounded" required>
<option value="">Pilih Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
</select>
<select name="academic_year_id" class="border p-2 rounded" required>
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded" required>
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
</div>
<textarea name="description" rows="4" placeholder="Deskripsi capaian (kosongkan untuk otomatis)" class="border p-2 rounded w-full max-w-2xl"></textarea>
<br>
<button class="bg-green-600 text-white px-4 py-2 rounded mt-2">Simpan Deskripsi</button>
</form>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Mapel</th>
<th class="border px-2 py-2 text-left">Deskripsi</th>
<th class="border px-2 py-2">Diperbarui</th>
</tr>
</thead>
<tbody>
@foreach($items as $item)
<tr>
<td class="border px-2 py-1">{{ $item->student?->name }}</td>
<td class="border px-2 py-1">{{ $item->subject?->name }}</td>
<td class="border px-2 py-1">{{ $item->description }}</td>
<td class="border px-2 py-1">{{ $item->updated_at }}</td>
</tr>
@endforeach
</tbody>
</table>
{{ $items->links() }}

<div class="mt-4 text-sm"><a href="/guru/assessments" class="text-blue-600 underline">Kembali ke Input Nilai</a></div>
</body>
</html>
