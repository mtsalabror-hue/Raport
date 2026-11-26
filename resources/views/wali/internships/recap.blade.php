<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Hasil PKL Kelas Wali</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Hasil PKL Kelas Wali (Hanya Lihat)</h1>
<p class="text-sm mb-4"><a href="/wali/internships" class="text-blue-600 underline">&larr; Daftar PKL</a></p>

<form method="GET" action="/wali/internships/recap" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas Wali Saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
@foreach($allStudents as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

@if(count($recaps))
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Penempatan</th>
<th class="border p-2 text-left">Selesai</th><th class="border p-2 text-left">Berjalan</th>
<th class="border p-2 text-left">Rata-rata Gabungan</th>
</tr>
</thead>
<tbody>
@foreach($recaps as $r)
<tr>
<td class="border p-2 align-top">{{ $students[$r['student_id']]->name ?? '-' }}</td>
<td class="border p-2">
<ul class="list-disc pl-5">
@foreach($r['placements'] as $p)
<li>{{ $p['internship']->company_name }} ({{ $p['internship']->start_date->format('Y-m-d') }} s.d. {{ $p['internship']->end_date->format('Y-m-d') }}, {{ ucfirst($p['status']) }}) &mdash; rata-rata: {{ $p['average'] === null ? '-' : number_format($p['average'], 2, ',', '.') }}</li>
@endforeach
</ul>
</td>
<td class="border p-2 align-top">{{ $r['finished'] }}</td>
<td class="border p-2 align-top">{{ $r['active'] }}</td>
<td class="border p-2 align-top">{{ $r['average'] === null ? '-' : number_format($r['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500">Belum ada data PKL untuk rekap.</p>
@endif
</body>
</html>
