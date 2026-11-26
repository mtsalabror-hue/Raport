<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>PKL Bimbingan Saya</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">PKL Bimbingan Saya</h1>
<p class="text-sm text-gray-600 mb-4">Hanya penempatan yang kolom pembimbingnya berisi nama Anda ({{ $teacher->name }}) yang tampil di sini. <a href="/guru/competency-exams" class="text-blue-600 underline">Hasil UKK mapel yang saya ampu</a></p>

@if($items->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Status</th>
<th class="border p-2 text-left">Rata-rata Aspek</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($items as $p)
<tr>
<td class="border p-2">{{ $p->student->name ?? '-' }}</td>
<td class="border p-2">{{ $p->company_name }}</td>
<td class="border p-2">{{ $p->start_date->format('Y-m-d') }} s.d. {{ $p->end_date->format('Y-m-d') }}</td>
<td class="border p-2">{{ ucfirst($p->status()) }}</td>
<td class="border p-2">{{ $p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.') }}</td>
<td class="border p-2"><a href="/guru/internships/{{ $p->id }}" class="text-blue-600 underline">Lihat &amp; Nilai</a></td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-4">{{ $items->links() }}</div>
@else
<p class="text-gray-500">Belum ada penempatan PKL dengan Anda sebagai pembimbing.</p>
@endif
</body>
</html>
