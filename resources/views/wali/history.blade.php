<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Akademik - {{ $student->name }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Riwayat Akademik</h1>
<p class="text-gray-600 text-sm mb-4">Siswa: <strong>{{ $student->name }}</strong> (NIS {{ $student->nis }}) — hanya-baca, lintas semester.</p>

@forelse($periods as $period)
<div class="border rounded p-4 mb-6">
<h2 class="font-bold mb-2">{{ $period['academic_year']->name ?? '-' }} &middot; Semester {{ $period['semester']->name ?? '-' }}</h2>

<h3 class="font-medium mb-1 text-sm">Nilai Akhir</h3>
@if(!empty($period['finals']))
<table class="w-full border text-sm mb-4">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Mata Pelajaran</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2">KKTP</th>
</tr>
</thead>
<tbody>
@foreach($period['finals'] as $row)
<tr>
<td class="border px-2 py-1">{{ $row['subject']->name ?? '-' }}</td>
<td class="border px-2 py-1">{{ $row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : '-' }}</td>
<td class="border px-2 py-1">{{ $row['kktp'] }}</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-sm text-gray-500 mb-4">Belum ada nilai pada periode ini.</p>
@endif

<h3 class="font-medium mb-1 text-sm">Presensi</h3>
<p class="text-sm mb-4">Hadir {{ $period['attendance']['hadir'] }} &middot; Sakit {{ $period['attendance']['sakit'] }} &middot; Izin {{ $period['attendance']['izin'] }} &middot; Tanpa keterangan {{ $period['attendance']['alpa'] }} &middot; Total {{ $period['attendance']['total'] }} catatan &middot; Persentase hadir {{ $period['attendance']['persentase_hadir'] !== null ? $period['attendance']['persentase_hadir'].'%' : '-' }}</p>

<h3 class="font-medium mb-1 text-sm">Prestasi</h3>
@if($period['achievements']->isNotEmpty())
<ul class="list-disc pl-5 text-sm">
@foreach($period['achievements'] as $a)<li><strong>{{ $a->title }}</strong>@if($a->level) ({{ $a->level }})@endif</li>@endforeach
</ul>
@else
<p class="text-sm text-gray-500">Belum ada prestasi pada periode ini.</p>
@endif
</div>
@empty
<p class="text-gray-500">Belum ada riwayat akademik untuk siswa ini.</p>
@endforelse

<div class="mt-4 text-sm flex gap-4">
<a href="/wali/preview/{{ $student->id }}" class="text-blue-600 underline">Preview Rapor</a>
<a href="/wali/dashboard" class="text-blue-600 underline">Dashboard</a>
</div>
</body>
</html>
