<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Kelas</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Kelas</h1>
<form method="POST" action="/admin/classes/{{ $class->id }}">
@csrf @method('PUT')
<select name="department_id" class="border p-2 mb-2">
@foreach($deps as $d)
<option value="{{ $d->id }}" {{ $class->department_id==$d->id?'selected':'' }}>{{ $d->name }}</option>
@endforeach
</select>
<select name="academic_year_id" class="border p-2 mb-2">
@foreach($years as $y)
<option value="{{ $y->id }}" {{ $class->academic_year_id==$y->id?'selected':'' }}>{{ $y->name }}</option>
@endforeach
</select>
<input name="name" value="{{ $class->name }}" class="border p-2 mb-2">
<input name="level" value="{{ $class->level }}" class="border p-2 mb-2">
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
