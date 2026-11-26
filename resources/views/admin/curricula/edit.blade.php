<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Kurikulum</title></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Kurikulum</h1>
<form method="POST" action="/admin/curricula/{{ $curriculum->id }}">
@csrf @method('PUT')
<input name="name" value="{{ $curriculum->name }}" class="border p-2 mb-2">
<button class="bg-blue-600 text-white px-3 py-2">Simpan</button>
</form>
</body></html>
