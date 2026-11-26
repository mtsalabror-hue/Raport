<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Monitoring PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Monitoring PKL</h1>
<p class="text-sm mb-4"><a href="/admin/internships" class="text-blue-600 underline">&larr; Daftar penempatan</a> &middot; <a href="/admin/internships/recap" class="text-blue-600 underline">Rekap Hasil PKL</a></p>

<form method="GET" action="/admin/internships/monitoring" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="company_name" class="border p-2 rounded">
<option value="">Semua Perusahaan</option>
@foreach($companies as $c)<option value="{{ $c }}" @selected(($filters['company_name'] ?? null) == $c)>{{ $c }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

@foreach(['berjalan' => 'Berjalan / Aktif', 'mendatang' => 'Mendatang', 'selesai' => 'Selesai'] as $key => $label)
<h2 class="font-bold mt-6 mb-2">{{ $label }} ({{ $groups[$key]->count() }})</h2>
@if($groups[$key]->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Pembimbing</th>
<th class="border p-2 text-left">Grade</th><th class="border p-2 text-left">Rata-rata Aspek</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($groups[$key] as $p)
<tr>
<td class="border p-2">{{ $p->student->name ?? '-' }}</td>
<td class="border p-2">{{ $p->company_name }}</td>
<td class="border p-2">{{ $p->start_date->format('Y-m-d') }} s.d. {{ $p->end_date->format('Y-m-d') }}</td>
<td class="border p-2">{{ $p->supervisor ?? '-' }}</td>
<td class="border p-2">{{ $p->grade ?? '-' }}</td>
<td class="border p-2">{{ $p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.') }}</td>
<td class="border p-2"><a href="/admin/internships/{{ $p->id }}/assessments" class="text-blue-600 underline">Penilaian</a></td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500 text-sm">Tidak ada penempatan {{ strtolower($label) }}.</p>
@endif
@endforeach
</body>
</html>
