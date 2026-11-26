<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Guru</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Data Guru</h1>
<a href="{{ route('admin.teachers.create') }}" class="bg-green-600 text-white px-3 py-2 rounded">Tambah</a>
<table class="w-full mt-4 border"><thead><tr><th>NIP</th><th>Nama</th><th>Aksi</th></tr></thead><tbody>
@foreach($items as $t)
<tr><td>{{ $t->nip }}</td><td>{{ $t->name }}</td><td>
<a href="{{ route('admin.teachers.edit',$t) }}">Edit</a>
<form method="POST" action="{{ route('admin.teachers.destroy',$t) }}" style="display:inline">@csrf @method('DELETE')<button>Hapus</button></form>
</td></tr>
@endforeach
</tbody></table>
{{ $items->links() }}
</body></html>
