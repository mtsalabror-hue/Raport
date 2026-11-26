<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Jurusan</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Data Jurusan</h1>
<form method="GET" class="mb-4"><input name="q" value="{{ $q }}" class="border p-2"><button class="bg-blue-600 text-white px-3 py-2">Cari</button></form>
<a href="{{ route('admin.departments.create') }}" class="bg-green-600 text-white px-3 py-2 rounded">Tambah</a>
<table class="w-full mt-4 border"><thead><tr><th>Kode</th><th>Nama</th><th>Aksi</th></tr></thead><tbody>
@foreach($items as $d)
<tr><td>{{ $d->code }}</td><td>{{ $d->name }}</td><td>
<a href="{{ route('admin.departments.edit',$d) }}">Edit</a>
<form method="POST" action="{{ route('admin.departments.destroy',$d) }}" style="display:inline">@csrf @method('DELETE')<button>Hapus</button></form>
</td></tr>
@endforeach
</tbody></table>
{{ $items->links() }}
</body></html>
