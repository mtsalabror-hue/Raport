<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit TA</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Tahun Ajaran</h1>
<form method="POST" action="/admin/academic_years/{{ $academicYear->id }}">
@csrf @method('PUT')
<input name="name" value="{{ $academicYear->name }}" class="border p-2 mb-2">
<input name="start_date" type="date" value="{{ $academicYear->start_date }}" class="border p-2 mb-2">
<input name="end_date" type="date" value="{{ $academicYear->end_date }}" class="border p-2 mb-2">
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
