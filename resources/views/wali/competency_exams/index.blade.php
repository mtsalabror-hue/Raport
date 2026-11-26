<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>UKK Kelas Wali</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">UKK Siswa Kelas Wali (Hanya Lihat)</h1>
<p class="text-sm text-gray-600 mb-4"><a href="/wali/internships" class="text-blue-600 underline">PKL kelas wali</a></p>
<p class="text-sm text-gray-600 mb-4">Tidak ada label lulus/tidak lulus — ambang kelulusan belum ditetapkan sekolah, jadi hanya skor yang ditampilkan.</p>

<form method="GET" action="/wali/competency-exams" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas Wali Saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
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

<h2 class="font-bold mb-2">Jadwal Ujian</h2>
@if($schedules->isNotEmpty())
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Mapel</th><th class="border p-2 text-left">Tanggal Ujian</th>
<th class="border p-2 text-left">Peserta</th><th class="border p-2 text-left">Sudah Dinilai</th>
<th class="border p-2 text-left">Rata-rata</th>
</tr>
</thead>
<tbody>
@foreach($schedules as $sch)
<tr>
<td class="border p-2">{{ $sch['subject']->name ?? '-' }}</td>
<td class="border p-2">{{ $sch['exam_date'] ?? '-' }}</td>
<td class="border p-2">{{ $sch['participants'] }}</td>
<td class="border p-2">{{ $sch['scored'] }}</td>
<td class="border p-2">{{ $sch['average'] === null ? '-' : number_format($sch['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500 mb-6">Belum ada jadwal UKK untuk siswa kelas wali Anda.</p>
@endif

<h2 class="font-bold mb-2">Rekap per Siswa</h2>
@if(count($recaps))
<table class="w-full border text-sm">
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
<p class="text-gray-500">Belum ada data UKK untuk rekap.</p>
@endif
</body>
</html>
