<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Edit Pengguna</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Edit Pengguna</h1>
<form method="POST" action="{{ route('admin.users.update',$user) }}">
@csrf @method('PUT')
<input name="name" value="{{ $user->name }}" class="border p-2 mb-2 block">
<input name="email" type="email" value="{{ $user->email }}" class="border p-2 mb-2 block">
<input name="password" type="password" placeholder="Password baru (kosongkan bila tidak diubah)" class="border p-2 mb-2 block">
<input name="password_confirmation" type="password" placeholder="Ulangi password baru" class="border p-2 mb-2 block">
<select name="role_id" class="border p-2 mb-2 block">@foreach($roles as $r)<option value="{{ $r->id }}" {{ $user->role_id==$r->id?'selected':'' }}>{{ $r->name }}</option>@endforeach</select>
<select name="teacher_id" class="border p-2 mb-2 block"><option value="">Bukan Guru (tanpa data guru)</option>@foreach($teachers as $t)<option value="{{ $t->id }}" {{ $user->teacher_id==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select>
<label class="block mb-2"><input type="checkbox" name="is_active" value="1" {{ $user->is_active?'checked':'' }}> Aktif</label>
<button class="bg-blue-600 text-white px-3 py-2 rounded">Simpan</button>
</form>
</body></html>
