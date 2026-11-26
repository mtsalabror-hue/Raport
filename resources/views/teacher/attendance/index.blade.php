<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Presensi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Input Presensi</h1>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/guru/attendance" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="border p-2 rounded">
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan Siswa</button>
</form>

@if($students->isNotEmpty())
<form method="POST" action="/guru/attendance">
@csrf
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="date" value="{{ $filters['date'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] ?? '' }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] ?? '' }}">

<p class="text-sm text-gray-600 mb-4">Tanggal: <strong>{{ $filters['date'] }}</strong>. Satu baris per siswa; siswa yang sudah tercatat pada tanggal ini tidak dapat diinput ulang (gunakan koreksi di riwayat).</p>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Hadir</th>
<th class="border px-2 py-2">Sakit</th>
<th class="border px-2 py-2">Izin</th>
<th class="border px-2 py-2">Tanpa Keterangan</th>
<th class="border px-2 py-2 text-left">Catatan</th>
</tr>
</thead>
<tbody>
@foreach($students as $i => $student)
@php $done = $existing->get($student->id); @endphp
<tr>
<td class="border px-2 py-1">
{{ $student->name }}
@if($done)
<span class="text-xs text-gray-500">(sudah tercatat: {{ $done->status }})</span>
@endif
</td>
@if($done)
<td colspan="4" class="border px-2 py-1 text-center text-gray-500">Sudah tercatat</td>
<td class="border px-2 py-1">{{ $done->note ?? '-' }}</td>
@else
<input type="hidden" name="records[{{ $i }}][student_id]" value="{{ $student->id }}">
@foreach(['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'] as $code => $label)
<td class="border px-2 py-1 text-center">
<input type="radio" name="records[{{ $i }}][status]" value="{{ $code }}" @checked($code === 'H') title="{{ $label }}">
</td>
@endforeach
<td class="border px-2 py-1"><input type="text" name="records[{{ $i }}][note]" class="border p-1 rounded w-full" placeholder="Opsional"></td>
@endif
</tr>
@endforeach
</tbody>
</table>

<button class="bg-green-600 text-white px-4 py-2 rounded mt-4">Simpan Presensi</button>
</form>
@elseif(!empty($filters['class_id']) && !empty($filters['date']))
<p class="text-gray-500 mt-4">Tidak ada siswa di kelas ini.</p>
@else
<p class="text-gray-500 mt-4">Pilih kelas dan tanggal untuk menampilkan daftar siswa.</p>
@endif

<div class="mt-4 text-sm flex gap-4">
<a href="/guru/attendance/history" class="text-blue-600 underline">Riwayat Presensi</a>
<a href="/guru/attendance/recap" class="text-blue-600 underline">Rekap Presensi</a>
<a href="/guru/attendance/academic-recap" class="text-blue-600 underline">Rekap Akademik</a>
</div>
</body>
</html>
