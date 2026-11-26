<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Presensi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Presensi {{ ($readOnly ?? false) ? '(Wali Kelas - hanya baca)' : '' }}</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="{{ ($readOnly ?? false) ? '/wali/attendance/recap' : '/guru/attendance/recap' }}" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan Rekap</button>
</form>

@if($classTotal !== null)
<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
<strong>Total Kelas:</strong>
Hadir {{ $classTotal['hadir'] }} &middot; Sakit {{ $classTotal['sakit'] }} &middot;
Izin {{ $classTotal['izin'] }} &middot; Tanpa keterangan {{ $classTotal['alpa'] }} &middot;
Total {{ $classTotal['total'] }} catatan
&middot; Persentase hadir {{ $classTotal['persentase_hadir'] !== null ? $classTotal['persentase_hadir'].'%' : '-' }}
</div>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Hadir</th>
<th class="border px-2 py-2">Sakit</th>
<th class="border px-2 py-2">Izin</th>
<th class="border px-2 py-2">Tanpa Ket.</th>
<th class="border px-2 py-2">Total</th>
<th class="border px-2 py-2">% Hadir</th>
</tr>
</thead>
<tbody>
@foreach($rows as $row)
<tr>
<td class="border px-2 py-1">{{ $row['student']->name }}</td>
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
@else
<p class="text-gray-500 mt-4">Pilih kelas, tahun ajaran, dan semester untuk menampilkan rekap. Rekap memakai satu periode eksplisit dan tidak mencampur semester lain.</p>
@endif

<div class="mt-4 text-sm flex gap-4">
<a href="{{ ($readOnly ?? false) ? '/wali/attendance/academic-recap' : '/guru/attendance/academic-recap' }}" class="text-blue-600 underline">Rekap Akademik</a>
@if(!($readOnly ?? false))
<a href="/guru/attendance" class="text-blue-600 underline">Input Presensi</a>
@endif
</div>
</body>
</html>
