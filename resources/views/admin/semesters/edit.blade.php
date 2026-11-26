<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Semester</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Semester</h1>
<form method="POST" action="/admin/semesters/{{ $semester->id }}">
@csrf @method('PUT')
<select name="academic_year_id" class="border p-2 mb-2">
@foreach($years as $y)
<option value="{{ $y->id }}" {{ $semester->academic_year_id==$y->id?'selected':'' }}>{{ $y->name }}</option>
@endforeach
</select>
<input name="name" value="{{ $semester->name }}" class="border p-2 mb-2">
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
