<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Presensi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Riwayat Presensi</h1>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/guru/attendance/history" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="border p-2 rounded" title="Dari tanggal">
<input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="border p-2 rounded" title="Sampai tanggal">
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Semua Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semua Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="status" class="border p-2 rounded">
<option value="">Semua Status</option>
@foreach(['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'] as $code => $label)
<option value="{{ $code }}" @selected(($filters['status'] ?? null) === $code)>{{ $label }}</option>
@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Tanggal</th>
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2">Status</th>
<th class="border px-2 py-2 text-left">Catatan</th>
<th class="border px-2 py-2">Koreksi</th>
</tr>
</thead>
<tbody>
@foreach($records as $r)
<tr>
<td class="border px-2 py-1">{{ $r->date->toDateString() }}</td>
<td class="border px-2 py-1">{{ $r->student?->name }}</td>
<td class="border px-2 py-1">{{ $r->schoolClass?->name }}</td>
<td class="border px-2 py-1">{{ \App\Models\StudentAttendance::STATUSES[$r->status] ?? $r->status }}</td>
<td class="border px-2 py-1">{{ $r->note ?? '-' }}</td>
<td class="border px-2 py-1">
<form method="POST" action="/guru/attendance/{{ $r->id }}/correct" class="flex gap-1">
@csrf
<select name="status" class="border p-1 rounded">
@foreach(['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'] as $code => $label)
<option value="{{ $code }}" @selected($r->status === $code)>{{ $label }}</option>
@endforeach
</select>
<button class="bg-amber-600 text-white px-2 py-1 rounded text-xs">Koreksi</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>

@if($records->isEmpty())
<p class="text-gray-500 mt-4">Tidak ada catatan presensi untuk filter ini.</p>
@endif

<div class="mt-4">{{ $records->links() }}</div>

<div class="mt-4 text-sm"><a href="/guru/attendance" class="text-blue-600 underline">Kembali ke Input Presensi</a></div>
</body>
</html>
