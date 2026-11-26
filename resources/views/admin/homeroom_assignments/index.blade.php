<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Wali Kelas</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Wali Kelas</h1>
<a href="{{ route('admin.homeroom-assignments.create') }}" class="bg-green-600 text-white px-3 py-2 rounded">Tambah</a>
<form method="GET" class="mt-4 flex gap-2">
<select name="class_id" class="border p-2"><option value="">Semua Kelas</option>@foreach($classes as $c)<option value="{{ $c->id }}" {{ request('class_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
<button class="bg-blue-600 text-white px-3 py-2 rounded">Filter</button>
</form>
<table class="w-full mt-4 border"><thead><tr><th class="border p-2">Kelas</th><th class="border p-2">Wali Kelas</th><th class="border p-2">Tahun Ajaran</th><th class="border p-2">Semester</th><th class="border p-2">Aksi</th></tr></thead><tbody>
@foreach($items as $a)
<tr><td class="border p-2">{{ $a->schoolClass->name }}</td><td class="border p-2">{{ $a->teacher->name }}</td><td class="border p-2">{{ $a->academicYear->name }}</td><td class="border p-2">{{ $a->semester->name }}</td><td class="border p-2">
<a href="{{ route('admin.homeroom-assignments.edit',$a) }}">Edit</a>
<form method="POST" action="{{ route('admin.homeroom-assignments.destroy',$a) }}" style="display:inline">@csrf @method('DELETE')<button>Hapus</button></form>
</td></tr>
@endforeach
</tbody></table>
{{ $items->links() }}
</body></html>
