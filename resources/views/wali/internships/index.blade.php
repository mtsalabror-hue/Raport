<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>PKL Kelas Wali</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">PKL Siswa Kelas Wali (Hanya Lihat)</h1>
<p class="text-sm text-gray-600 mb-4"><a href="/wali/internships/recap" class="text-blue-600 underline">Rekap Hasil PKL</a> &middot; <a href="/wali/competency-exams" class="text-blue-600 underline">UKK kelas wali</a></p>

<form method="GET" action="/wali/internships" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas Wali Saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

@if($items->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Status</th>
<th class="border p-2 text-left">Pembimbing</th><th class="border p-2 text-left">Grade</th>
<th class="border p-2 text-left">Rata-rata Aspek</th>
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
</tr>
@endforeach
</tbody>
</table>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="text-gray-500">Belum ada penempatan PKL untuk siswa kelas wali Anda.</p>
@endif
</body>
</html>
