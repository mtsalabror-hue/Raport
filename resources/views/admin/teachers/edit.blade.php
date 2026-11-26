<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Guru</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Guru</h1>
<form method="POST" action="/admin/teachers/{{ $teacher->id }}">
@csrf @method('PUT')
<input name="nip" value="{{ $teacher->nip }}" class="border p-2 mb-2">
<input name="name" value="{{ $teacher->name }}" class="border p-2 mb-2">
<select name="gender" class="border p-2 mb-2"><option value="L" {{ $teacher->gender=='L'?'selected':'' }}>L</option><option value="P" {{ $teacher->gender=='P'?'selected':'' }}>P</option></select>
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
