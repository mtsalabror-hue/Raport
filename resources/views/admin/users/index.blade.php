<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Pengguna</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Pengguna</h1>
<a href="{{ route('admin.users.create') }}" class="bg-green-600 text-white px-3 py-2 rounded">Tambah</a>
<form method="GET" class="mt-4 flex gap-2">
<input name="q" value="{{ $q }}" placeholder="Cari nama/email" class="border p-2">
<button class="bg-blue-600 text-white px-3 py-2 rounded">Cari</button>
</form>
<table class="w-full mt-4 border"><thead><tr><th class="border p-2">Nama</th><th class="border p-2">Email</th><th class="border p-2">Role</th><th class="border p-2">Aktif</th><th class="border p-2">Aksi</th></tr></thead><tbody>
@foreach($items as $u)
<tr><td class="border p-2">{{ $u->name }}</td><td class="border p-2">{{ $u->email }}</td><td class="border p-2">{{ $u->role->name ?? '-' }}</td><td class="border p-2">{{ $u->is_active ? 'Ya' : 'Tidak' }}</td><td class="border p-2">
<a href="{{ route('admin.users.edit',$u) }}">Edit</a>
<form method="POST" action="{{ route('admin.users.destroy',$u) }}" style="display:inline">@csrf @method('DELETE')<button>Hapus</button></form>
</td></tr>
@endforeach
</tbody></table>
{{ $items->links() }}
</body></html>
