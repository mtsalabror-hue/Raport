<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Hasil UKK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Hasil UKK</h1>
<p class="text-sm mb-4"><a href="/admin/competency-exams" class="text-blue-600 underline">&larr; Daftar jadwal</a></p>
<p class="text-sm text-gray-600 mb-4">Nilai UKK tidak otomatis masuk ke rapor. Tidak ada label lulus/tidak lulus — ambang kelulusan belum ditetapkan sekolah, jadi hanya skor yang ditampilkan.</p>

<form method="GET" action="/admin/competency-exams/recap" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
@foreach($allStudents as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="subject_id" class="border p-2 rounded">
<option value="">Semua Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<h2 class="font-bold mb-2">Per Siswa</h2>
@if(count($recaps))
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Per Mapel</th>
<th class="border p-2 text-left">Rata-rata Gabungan</th>
</tr>
</thead>
<tbody>
@foreach($recaps as $r)
<tr>
<td class="border p-2 align-top">{{ $students[$r['student_id']]->name ?? '-' }}</td>
<td class="border p-2">
<ul class="list-disc pl-5">
@foreach($r['per_subject'] as $ps)
<li>{{ $ps['subject']->name ?? '-' }} ({{ $ps['count'] }} ujian) &mdash; rata-rata: {{ $ps['average'] === null ? '-' : number_format($ps['average'], 2, ',', '.') }}</li>
@endforeach
</ul>
</td>
<td class="border p-2 align-top">{{ $r['average'] === null ? '-' : number_format($r['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500 mb-6">Belum ada data UKK untuk rekap.</p>
@endif

<h2 class="font-bold mb-2">Per Mapel</h2>
@if(count($perSubject))
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Mapel</th><th class="border p-2 text-left">Peserta</th>
<th class="border p-2 text-left">Sudah Dinilai</th><th class="border p-2 text-left">Rata-rata</th>
</tr>
</thead>
<tbody>
@foreach($perSubject as $ps)
<tr>
<td class="border p-2">{{ $ps['subject']->name ?? '-' }}</td>
<td class="border p-2">{{ $ps['participants'] }}</td>
<td class="border p-2">{{ $ps['scored'] }}</td>
<td class="border p-2">{{ $ps['average'] === null ? '-' : number_format($ps['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500">Belum ada data UKK per mapel.</p>
@endif
</body>
</html>
