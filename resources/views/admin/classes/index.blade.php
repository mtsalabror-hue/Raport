<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Kelas</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Data Kelas</h1>
<a href="{{ route('admin.classes.create') }}" class="bg-green-600 text-white px-3 py-2 rounded">Tambah</a>
<table class="w-full mt-4 border"><thead><tr><th>Nama</th><th>Level</th><th>Aksi</th></tr></thead><tbody>
@foreach($items as $c)
<tr><td>{{ $c->name }}</td><td>{{ $c->level }}</td><td>
<a href="{{ route('admin.classes.edit',$c) }}">Edit</a>
<form method="POST" action="{{ route('admin.classes.destroy',$c) }}" style="display:inline">@csrf @method('DELETE')<button>Hapus</button></form>
</td></tr>
@endforeach
</tbody></table>
{{ $items->links() }}
</body></html>
