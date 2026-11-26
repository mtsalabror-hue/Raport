<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Tambah Wali Kelas</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Tambah Wali Kelas</h1>
<form method="POST" action="{{ route('admin.homeroom-assignments.store') }}">
@csrf
<select name="teacher_id" class="border p-2 mb-2 block"><option value="">Pilih Guru</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
<select name="class_id" class="border p-2 mb-2 block"><option value="">Pilih Kelas</option>@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
<select name="academic_year_id" class="border p-2 mb-2 block"><option value="">Pilih Tahun Ajaran</option>@foreach($years as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach</select>
<select name="semester_id" class="border p-2 mb-2 block"><option value="">Pilih Semester</option>@foreach($semesters as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
<button class="bg-blue-600 text-white px-3 py-2 rounded">Simpan</button>
</form>
</body></html>
