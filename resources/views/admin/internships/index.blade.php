<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Penempatan PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Penempatan PKL</h1>
<p class="text-sm text-gray-600 mb-4">Periode PKL ditentukan dari tanggal mulai s.d. tanggal selesai (tidak ada kolom tahun ajaran/semester pada tabel internships). <a href="/admin/internships/monitoring" class="text-blue-600 underline">Monitoring</a> &middot; <a href="/admin/internships/recap" class="text-blue-600 underline">Rekap Hasil PKL</a></p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<h2 class="font-bold mb-2">Mitra Industri ({{ count($companies) }})</h2>
@if(count($companies))
<p class="text-sm mb-4">{{ implode(', ', $companies) }}</p>
@else
<p class="text-sm text-gray-500 mb-4">Belum ada mitra industri. Mitra baru tercatat otomatis saat penempatan ditambahkan.</p>
@endif

<form method="GET" action="/admin/internships" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="company_name" class="border p-2 rounded">
<option value="">Semua Perusahaan</option>
@foreach($companies as $c)<option value="{{ $c }}" @selected(($filters['company_name'] ?? null) == $c)>{{ $c }}</option>@endforeach
</select>
<select name="status" class="border p-2 rounded">
<option value="">Semua Status</option>
@foreach(['mendatang' => 'Mendatang', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $v => $l)<option value="{{ $v }}" @selected(($filters['status'] ?? null) == $v)>{{ $l }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<form method="POST" action="/admin/internships" class="bg-gray-50 border rounded p-4 mb-6">
@csrf
<h2 class="font-bold mb-2">Tambah Penempatan PKL</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<input type="text" name="company_name" required list="companies" placeholder="Nama perusahaan / mitra industri" class="border p-2 rounded flex-1">
<datalist id="companies">@foreach($companies as $c)<option value="{{ $c }}">@endforeach</datalist>
<input type="date" name="start_date" required class="border p-2 rounded">
<input type="date" name="end_date" required class="border p-2 rounded">
<input type="text" name="supervisor" placeholder="Pembimbing (nama guru)" class="border p-2 rounded">
<input type="text" name="grade" placeholder="Nilai akhir / grade (opsional)" class="border p-2 rounded">
</div>
<p class="text-xs text-gray-500 mt-2">Penempatan yang tanggalnya beririsan dengan penempatan lain siswa yang sama akan ditolak.</p>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Penempatan</button>
</form>

@if($items->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Status</th>
<th class="border p-2 text-left">Pembimbing</th><th class="border p-2 text-left">Grade</th>
<th class="border p-2 text-left">Rata-rata Aspek</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($items as $p)
<tr>
<td class="border p-2">{{ $p->student->name ?? '-' }}</td>
<td class="border p-2">{{ $p->company_name }}</td>
<td class="border p-2">{{ $p->start_date->format('Y-m-d') }} s.d. {{ $p->end_date->format('Y-m-d') }}</td>
<td class="border p-2">{{ ucfirst($p->status()) }}</td>
<td class="border p-2">{{ $p->supervisor ?? '-' }}</td>
<td class="border p-2">{{ $p->grade ?? '-' }}</td>
<td class="border p-2">{{ $p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.') }}</td>
<td class="border p-2 whitespace-nowrap">
<a href="/admin/internships/{{ $p->id }}/assessments" class="text-blue-600 underline">Penilaian</a>
<a href="/admin/internships/{{ $p->id }}/edit" class="text-blue-600 underline ml-2">Ubah</a>
<form method="POST" action="/admin/internships/{{ $p->id }}" class="inline ml-2" onsubmit="return confirm('Hapus penempatan ini beserta penilaian aspeknya?')">
@csrf @method('DELETE')
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="text-gray-500">Belum ada penempatan PKL.</p>
@endif
</body>
</html>
