<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ubah Penempatan PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Ubah Penempatan PKL</h1>
<p class="text-sm mb-4"><a href="/admin/internships" class="text-blue-600 underline">&larr; Kembali ke daftar penempatan</a> &middot; <a href="/admin/internships/{{ $internship->id }}/assessments" class="text-blue-600 underline">Penilaian per aspek</a></p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="POST" action="/admin/internships/{{ $internship->id }}" class="bg-gray-50 border rounded p-4 max-w-2xl">
@csrf @method('PUT')
<div class="flex flex-col gap-3">
<label class="text-sm">Siswa
<select name="student_id" required class="border p-2 rounded w-full mt-1">
@foreach($students as $s)<option value="{{ $s->id }}" @selected($internship->student_id == $s->id)>{{ $s->name }}</option>@endforeach
</select></label>
<label class="text-sm">Perusahaan / mitra industri
<input type="text" name="company_name" required list="companies" value="{{ $internship->company_name }}" class="border p-2 rounded w-full mt-1">
<datalist id="companies">@foreach($companies as $c)<option value="{{ $c }}">@endforeach</datalist></label>
<label class="text-sm">Tanggal mulai
<input type="date" name="start_date" required value="{{ $internship->start_date->format('Y-m-d') }}" class="border p-2 rounded w-full mt-1"></label>
<label class="text-sm">Tanggal selesai
<input type="date" name="end_date" required value="{{ $internship->end_date->format('Y-m-d') }}" class="border p-2 rounded w-full mt-1"></label>
<label class="text-sm">Pembimbing (nama guru)
<input type="text" name="supervisor" value="{{ $internship->supervisor }}" class="border p-2 rounded w-full mt-1"></label>
<label class="text-sm">Nilai akhir / grade (opsional)
<input type="text" name="grade" value="{{ $internship->grade }}" class="border p-2 rounded w-full mt-1"></label>
</div>
<p class="text-xs text-gray-500 mt-2">Periode baru tidak boleh beririsan dengan penempatan lain siswa yang sama. Status saat ini: {{ ucfirst($internship->status()) }}.</p>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-3">Simpan Perubahan</button>
</form>
</body>
</html>
