<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Status Rapor Kelas Saya</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Status Rapor Kelas Saya</h1>
<p class="text-gray-600 text-sm mb-4">Status rapor siswa di kelas wali Anda. PDF hanya tersedia untuk rapor yang sudah diterbitkan atau dikunci.</p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="GET" action="/wali/report-cards" class="flex flex-wrap gap-2 mb-4 text-sm">
<select name="class_id" class="border rounded px-2 py-1">
<option value="">Semua kelas saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border rounded px-2 py-1">
<option value="">Semua tahun ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border rounded px-2 py-1">
<option value="">Semua semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="status" class="border rounded px-2 py-1">
<option value="">Semua status</option>
@foreach(['draft' => 'Belum Lengkap', 'diajukan' => 'Diajukan', 'divalidasi' => 'Disetujui', 'diterbitkan' => 'Diterbitkan', 'dikunci' => 'Terkunci'] as $status => $label)
<option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ $label }}</option>
@endforeach
</select>
<button class="bg-blue-600 text-white px-3 py-1 rounded">Filter</button>
</form>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2 text-left">Tahun Ajaran</th>
<th class="border px-2 py-2 text-left">Semester</th>
<th class="border px-2 py-2 text-left">Status</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
@forelse($cards as $card)
<tr>
<td class="border px-2 py-1">{{ $card->student->name ?? '-' }}<br><span class="text-gray-500 text-xs">NIS {{ $card->student->nis ?? '-' }}</span></td>
<td class="border px-2 py-1">{{ $card->schoolClass->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $card->academicYear->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $card->semester->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $card->documentLabel() }}</td>
<td class="border px-2 py-1">
<a href="/wali/preview/{{ $card->student_id }}?academic_year_id={{ $card->academic_year_id }}&semester_id={{ $card->semester_id }}" class="text-blue-600 underline">Preview</a>
@if($card->isPublished())
| <a href="/wali/report-cards/{{ $card->id }}/pdf" class="text-blue-600 underline">Unduh PDF</a>
@endif
</td>
</tr>
@empty
<tr><td colspan="6" class="border px-2 py-2 text-gray-500">Belum ada rapor pada filter ini.</td></tr>
@endforelse
</tbody>
</table>

<div class="mt-4">{{ $cards->links() }}</div>
<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Dashboard Wali</a></div>
</body>
</html>
