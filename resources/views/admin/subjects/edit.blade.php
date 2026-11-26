<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Mapel</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Mata Pelajaran</h1>
<form method="POST" action="/admin/subjects/{{ $subject->id }}">
@csrf @method('PUT')
<input name="code" value="{{ $subject->code }}" class="border p-2 mb-2">
<input name="name" value="{{ $subject->name }}" class="border p-2 mb-2">
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
