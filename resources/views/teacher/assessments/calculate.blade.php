<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Perhitungan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Hasil Perhitungan Nilai Akhir</h1>
<p class="text-sm text-gray-600 mb-4">Nilai akhir = (jumlah skor &times; bobot) dibagi (jumlah bobot komponen yang ada nilainya). Komponen kosong tidak dihitung sebagai 0. Perhitungan hanya berjalan jika total bobot komponen aktif tepat 100.</p>

@if(!empty($errors))
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors as $k => $e)<li>{{ $k }}: {{ $e }}</li>@endforeach</ul>
</div>
@endif

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa - Mapel</th>
<th class="border px-2 py-2">Nilai Akhir</th>
</tr>
</thead>
<tbody>
@foreach($results as $k => $v)
<tr>
<td class="border px-2 py-1">{{ $k }}</td>
<td class="border px-2 py-1">{{ $v }}</td>
</tr>
@endforeach
</tbody>
</table>

@if(empty($results))
<p class="text-gray-500 mt-4">Belum ada nilai untuk dihitung.</p>
@endif

<div class="mt-4 text-sm"><a href="/guru/assessments" class="text-blue-600 underline">Kembali ke Input Nilai</a></div>
</body>
</html>
