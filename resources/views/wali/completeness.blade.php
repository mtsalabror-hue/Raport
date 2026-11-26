<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Monitoring Kelengkapan Rapor</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Monitoring Kelengkapan Rapor</h1>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/completeness" class="flex flex-wrap gap-2 mb-6">
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
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
</form>

@if($stats !== null)
<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
<strong>Total {{ $stats['total'] }} siswa</strong> &middot; Lengkap: {{ $stats['lengkap'] }} &middot; Belum lengkap: {{ $stats['belum_lengkap'] }}
</div>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Status</th>
<th class="border px-2 py-2 text-left">Kekurangan (klik untuk ke sumber data)</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($checks as $check)
<tr>
<td class="border px-2 py-1">{{ $check['student']->name }}</td>
<td class="border px-2 py-1">{{ $check['complete'] ? 'Lengkap' : 'Belum lengkap' }}</td>
<td class="border px-2 py-1">
@if($check['complete']) <span class="text-green-700">Tidak ada kekurangan.</span>
@else
<ul class="list-disc pl-4">
@foreach($check['missing'] as $m)<li><a class="text-blue-600 underline" href="{{ $m['url'] }}">{{ $m['message'] }}</a></li>@endforeach
</ul>
@endif
</td>
<td class="border px-2 py-1 whitespace-nowrap">
<a href="/wali/preview/{{ $check['student']->id }}?academic_year_id={{ $filters['academic_year_id'] }}&semester_id={{ $filters['semester_id'] }}" class="text-blue-600 underline">Preview</a>
@if($check['complete'] && ($check['report_card']->status ?? 'draft') === 'draft')
<form method="POST" action="/wali/submit" class="inline ml-2">
@csrf
<input type="hidden" name="student_id" value="{{ $check['student']->id }}">
<input type="hidden" name="class_id" value="{{ $filters['class_id'] }}">
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button class="text-green-700 underline">Ajukan Rapor</button>
</form>
@elseif(($check['report_card']->status ?? null) === 'diajukan')
<span class="text-gray-500 ml-2">Sudah diajukan</span>
@endif
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500 mt-4">Pilih kelas, tahun ajaran, dan semester untuk memantau kelengkapan rapor.</p>
@endif

<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Kembali ke Dashboard</a></div>
</body>
</html>
