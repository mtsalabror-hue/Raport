<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Jurusan</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Jurusan</h1>
<form method="POST" action="/admin/departments/{{ $department->id }}">
@csrf @method('PUT')
<input name="code" value="{{ $department->code }}" class="border p-2 mb-2">
<input name="name" value="{{ $department->name }}" class="border p-2 mb-2">
<textarea name="description" class="border p-2 mb-2">{{ $department->description }}</textarea>
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
