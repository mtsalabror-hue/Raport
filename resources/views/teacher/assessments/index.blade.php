<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Nilai</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="p-6" x-data="{ dirty: false }">
<h1 class="text-xl font-bold mb-4">Input Nilai</h1>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="GET" action="/guru/assessments" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Pilih Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="subject_id" class="border p-2 rounded">
<option value="">Pilih Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }} ({{ $s->code }})</option>@endforeach
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
</form>

@if($students->isEmpty())
<p class="text-gray-500">Pilih kelas dan mapel untuk menampilkan daftar siswa dan kolom komponen penilaian.</p>
@else
<p class="text-sm text-gray-600 mb-2">Nilai kosong dihitung sebagai belum dinilai, bukan 0. Nilai yang sudah terkirim atau terkunci tidak dapat diubah langsung, ajukan revisi.</p>

<form method="POST" action="/guru/assessments/bulk" @submit="dirty = false">
@csrf
<input type="hidden" name="subject_id" value="{{ $filters['subject_id'] }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<input type="hidden" name="loaded_at" value="{{ $loadedAt }}">

<div class="overflow-x-auto">
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">NIS</th>
<th class="border px-2 py-2 text-left">Nama Siswa</th>
@foreach($components as $comp)
<th class="border px-2 py-2">{{ $comp->name }}<br><span class="font-normal text-xs">{{ $comp->type }} &middot; {{ rtrim(rtrim(number_format($comp->weight, 2), '0'), '.') }}%</span></th>
@endforeach
<th class="border px-2 py-2">Status</th>
</tr>
</thead>
<tbody>
@php $i = 0; @endphp
@foreach($students as $student)
<tr>
<td class="border px-2 py-1">{{ $student->nis }}</td>
<td class="border px-2 py-1">{{ $student->name }}</td>
@foreach($components as $comp)
@php $existing = $scores[$student->id.'-'.$comp->id] ?? null; @endphp
<td class="border px-1 py-1">
<input type="hidden" name="scores[{{ $i }}][student_id]" value="{{ $student->id }}">
<input type="hidden" name="scores[{{ $i }}][component_id]" value="{{ $comp->id }}">
<input type="number" name="scores[{{ $i }}][score]" min="0" max="100" step="0.01"
value="{{ $existing?->score !== null ? $existing->score : '' }}"
@change="dirty = true"
{{ $existing && $existing->isLocked() ? 'disabled' : '' }}
class="w-20 border rounded px-2 py-1 {{ $existing && $existing->isLocked() ? 'bg-gray-100' : '' }}"
placeholder="-">
</td>
@php $i++; @endphp
@endforeach
<td class="border px-2 py-1 text-xs">
@php $any = $scores->filter(fn($s) => $s->student_id === $student->id)->first(); @endphp
{{ $any ? $any->status : 'belum ada' }}
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="flex gap-2 mt-4">
<button class="bg-green-600 text-white px-4 py-2 rounded" :disabled="!dirty" :class="!dirty && 'opacity-50'">Simpan Draft</button>
<span x-show="dirty" class="text-amber-700 text-sm self-center">Ada perubahan yang belum disimpan.</span>
</div>
</form>

<form method="POST" action="/guru/assessments/submit" class="inline-block mt-4">
@csrf
<input type="hidden" name="subject_id" value="{{ $filters['subject_id'] }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="bg-blue-600 text-white px-4 py-2 rounded" onclick="return confirm('Kirim semua nilai draft pada filter ini? Nilai yang terkirim tidak dapat diubah langsung.')">Kirim Nilai</button>
</form>

<form method="POST" action="/guru/assessments/lock" class="inline-block mt-4 ml-2">
@csrf
<input type="hidden" name="subject_id" value="{{ $filters['subject_id'] }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="bg-gray-700 text-white px-4 py-2 rounded" onclick="return confirm('Kunci semua nilai yang sudah terkirim pada filter ini?')">Kunci Nilai Terkirim</button>
</form>
@endif

<div class="mt-6 text-sm">
<a href="/guru/assessments/recap" class="text-blue-600 underline">Rekap Nilai</a> &middot;
<a href="/guru/assessments/calculate" class="text-blue-600 underline">Hitung Nilai Akhir</a> &middot;
<a href="/guru/assessments/history" class="text-blue-600 underline">Riwayat Perubahan</a> &middot;
<a href="/guru/components" class="text-blue-600 underline">Komponen &amp; Bobot</a> &middot;
<a href="/guru/descriptions" class="text-blue-600 underline">Deskripsi Capaian</a>
</div>
</body>
</html>
