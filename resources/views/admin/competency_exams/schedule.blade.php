<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal UKK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-2">Jadwal UKK: {{ $subject->name }} &mdash; {{ $exam_date }}</h1>
<p class="text-sm mb-4"><a href="/admin/competency-exams" class="text-blue-600 underline">&larr; Daftar jadwal</a> &middot; <a href="/admin/competency-exams/recap" class="text-blue-600 underline">Rekap Hasil UKK</a></p>
<p class="mb-4">Peserta: <strong>{{ $participants->count() }}</strong> &middot; Rata-rata yang sudah dinilai: <strong>{{ $average === null ? '-' : number_format($average, 2, ',', '.') }}</strong></p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="POST" action="/admin/competency-exams" class="bg-gray-50 border rounded p-4 mb-6">
@csrf
<h2 class="font-bold mb-2">Tambah Peserta ke Jadwal Ini</h2>
<input type="hidden" name="subject_id" value="{{ $subject->id }}">
<input type="hidden" name="exam_date" value="{{ $exam_date }}">
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
@foreach($candidates as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai (boleh kosong)" class="border p-2 rounded">
<input type="text" name="examiner" placeholder="Penguji (opsional)" class="border p-2 rounded">
<input type="text" name="note" placeholder="Catatan (opsional)" class="border p-2 rounded flex-1">
</div>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Tambah Peserta</button>
</form>

@if($participants->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Nilai</th>
<th class="border p-2 text-left">Penguji</th><th class="border p-2 text-left">Catatan</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($participants as $p)
<tr>
<td class="border p-2">{{ $p->student->name ?? '-' }}</td>
<td class="border p-2">{{ $p->score === null ? '-' : number_format((float) $p->score, 2, ',', '.') }}</td>
<td class="border p-2">{{ $p->examiner() ?? '-' }}</td>
<td class="border p-2">{{ $p->noteWithoutExaminer() ?? '-' }}</td>
<td class="border p-2">
<form method="POST" action="/admin/competency-exams/{{ $p->id }}" class="inline-flex flex-wrap gap-1 items-center">
@csrf @method('PUT')
<input type="number" name="score" step="0.01" min="0" max="100" value="{{ $p->score }}" placeholder="Nilai" class="border p-1 rounded w-24">
<input type="text" name="examiner" value="{{ $p->examiner() }}" placeholder="Penguji" class="border p-1 rounded">
<input type="text" name="note" value="{{ $p->noteWithoutExaminer() }}" placeholder="Catatan" class="border p-1 rounded">
<button class="bg-blue-600 text-white px-2 py-1 rounded">Simpan Hasil</button>
</form>
<form method="POST" action="/admin/competency-exams/{{ $p->id }}" class="inline ml-2" onsubmit="return confirm('Hapus peserta ini dari jadwal?')">
@csrf @method('DELETE')
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500">Belum ada peserta pada jadwal ini.</p>
@endif
</body>
</html>
