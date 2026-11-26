<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Siswa</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Siswa</h1>
<form method="POST" action="/admin/students/{{ $student->id }}">
@csrf @method('PUT')
<input name="nis" value="{{ $student->nis }}" class="border p-2 mb-2">
<input name="nisn" value="{{ $student->nisn }}" class="border p-2 mb-2">
<input name="name" value="{{ $student->name }}" class="border p-2 mb-2">
<select name="gender" class="border p-2 mb-2"><option value="L" {{ $student->gender=='L'?'selected':'' }}>L</option><option value="P" {{ $student->gender=='P'?'selected':'' }}>P</option></select>
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
