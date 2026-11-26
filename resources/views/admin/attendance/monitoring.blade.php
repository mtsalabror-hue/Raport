<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Monitoring Presensi &amp; Nilai</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Monitoring Presensi &amp; Nilai (Admin)</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/admin/attendance/monitoring" class="flex flex-wrap gap-2 mb-6">
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Semua Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semua Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="teacher_id" class="border p-2 rounded">
<option value="">Semua Guru</option>
@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(($filters['teacher_id'] ?? null) == $t->id)>{{ $t->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<h2 class="text-lg font-semibold mb-2">Nilai Belum Dikirim (status draft)</h2>
<p class="text-sm text-gray-600 mb-4">Total {{ $totalDrafts }} nilai masih berstatus draft dan belum dikirim oleh guru.</p>

<table class="w-full border text-sm mb-8">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Guru</th>
<th class="border px-2 py-2 text-left">Mapel</th>
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2 text-left">Tahun Ajaran</th>
<th class="border px-2 py-2 text-left">Semester</th>
<th class="border px-2 py-2">Jumlah Nilai Draft</th>
<th class="border px-2 py-2">Jumlah Siswa</th>
</tr>
</thead>
<tbody>
@foreach($drafts as $d)
<tr>
<td class="border px-2 py-1">{{ $d->teacher?->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $d->subject?->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $d->schoolClass?->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $d->academicYear?->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $d->semester?->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $d->draft_count }}</td>
<td class="border px-2 py-1">{{ $d->student_count }}</td>
</tr>
@endforeach
</tbody>
</table>

@if($drafts->isEmpty())
<p class="text-gray-500 mb-8">Tidak ada nilai draft untuk filter ini.</p>
@endif

<h2 class="text-lg font-semibold mb-2">Rekap Presensi per Kelas (per semester)</h2>
@if(empty($attendanceByClass))
<p class="text-gray-500">Pilih tahun ajaran dan semester untuk menampilkan rekap presensi per kelas.</p>
@else
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2">Hadir</th>
<th class="border px-2 py-2">Sakit</th>
<th class="border px-2 py-2">Izin</th>
<th class="border px-2 py-2">Tanpa Ket.</th>
<th class="border px-2 py-2">Total</th>
<th class="border px-2 py-2">% Hadir</th>
</tr>
</thead>
<tbody>
@foreach($attendanceByClass as $classId => $row)
<tr>
<td class="border px-2 py-1">{{ $classes->firstWhere('id', $classId)?->name ?? $classId }}</td>
<td class="border px-2 py-1">{{ $row['hadir'] }}</td>
<td class="border px-2 py-1">{{ $row['sakit'] }}</td>
<td class="border px-2 py-1">{{ $row['izin'] }}</td>
<td class="border px-2 py-1">{{ $row['alpa'] }}</td>
<td class="border px-2 py-1">{{ $row['total'] }}</td>
<td class="border px-2 py-1">{{ $row['persentase_hadir'] !== null ? $row['persentase_hadir'].'%' : '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif
</body>
</html>
