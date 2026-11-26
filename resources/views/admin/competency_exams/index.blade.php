<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal UKK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Jadwal Ujian Kompetensi (UKK)</h1>
<p class="text-sm mb-4"><a href="/admin/competency-exams/recap" class="text-blue-600 underline">Rekap Hasil UKK</a></p>
<p class="text-sm text-gray-600 mb-4">Satu jadwal = satu mapel pada satu tanggal ujian. Penguji tidak punya kolom sendiri; catat lewat isian "Penguji" (disimpan sebagai baris "Penguji: ..." di kolom catatan). UKK tidak memakai ambang lulus — rekap hanya menampilkan skor.</p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="GET" action="/admin/competency-exams" class="flex flex-wrap gap-2 mb-6">
<select name="subject_id" class="border p-2 rounded">
<option value="">Semua Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<input type="date" name="exam_date" value="{{ $filters['exam_date'] ?? '' }}" class="border p-2 rounded">
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<form method="POST" action="/admin/competency-exams" class="bg-gray-50 border rounded p-4 mb-6">
@csrf
<h2 class="font-bold mb-2">Tambah Peserta ke Jadwal / Buat Jadwal Baru</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="subject_id" required class="border p-2 rounded">
<option value="">Pilih Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<input type="date" name="exam_date" required class="border p-2 rounded">
<input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai (boleh kosong)" class="border p-2 rounded">
<input type="text" name="examiner" placeholder="Penguji (opsional)" class="border p-2 rounded">
<input type="text" name="note" placeholder="Catatan (opsional)" class="border p-2 rounded flex-1">
</div>
<p class="text-xs text-gray-500 mt-2">Siswa yang sudah terdaftar pada jadwal yang sama tidak diduplikasi — nilainya diperbarui.</p>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Peserta</button>
</form>

@if($schedules->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Mapel</th><th class="border p-2 text-left">Tanggal Ujian</th>
<th class="border p-2 text-left">Peserta</th><th class="border p-2 text-left">Sudah Dinilai</th>
<th class="border p-2 text-left">Rata-rata</th><th class="border p-2 text-left">Aksi</th>
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
<td class="border p-2"><a href="/admin/competency-exams/schedule?subject_id={{ $sch['subject_id'] }}&exam_date={{ $sch['exam_date'] }}" class="text-blue-600 underline">Peserta &amp; Input Hasil</a></td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500">Belum ada jadwal UKK.</p>
@endif
</body>
</html>
