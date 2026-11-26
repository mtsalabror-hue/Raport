<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Tambah Pengguna</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Tambah Pengguna</h1>
<form method="POST" action="{{ route('admin.users.store') }}">
@csrf
<input name="name" placeholder="Nama" class="border p-2 mb-2 block">
<input name="email" type="email" placeholder="Email" class="border p-2 mb-2 block">
<input name="password" type="password" placeholder="Password (min 8 karakter)" class="border p-2 mb-2 block">
<input name="password_confirmation" type="password" placeholder="Ulangi password" class="border p-2 mb-2 block">
<select name="role_id" class="border p-2 mb-2 block"><option value="">Pilih Role</option>@foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach</select>
<select name="teacher_id" class="border p-2 mb-2 block"><option value="">Bukan Guru (tanpa data guru)</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
<label class="block mb-2"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
<button class="bg-blue-600 text-white px-3 py-2 rounded">Simpan</button>
</form>
</body></html>
