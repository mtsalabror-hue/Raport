<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Penilaian PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-2">Penilaian PKL per Aspek</h1>
<p class="text-sm mb-4"><a href="/admin/internships" class="text-blue-600 underline">&larr; Daftar penempatan</a></p>
<p class="mb-1"><strong>{{ $internship->student->name ?? '-' }}</strong> &middot; {{ $internship->company_name }}</p>
<p class="text-sm text-gray-600 mb-4">{{ $internship->start_date->format('Y-m-d') }} s.d. {{ $internship->end_date->format('Y-m-d') }} &middot; Status: {{ ucfirst($recap['status']) }} &middot; Pembimbing: {{ $internship->supervisor ?? '-' }} &middot; Grade: {{ $internship->grade ?? '-' }}</p>
<p class="mb-4">Rata-rata aspek: <strong>{{ $recap['average'] === null ? 'Belum ada nilai' : number_format($recap['average'], 2, ',', '.') }}</strong></p>

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif

<form method="POST" action="/admin/internships/{{ $internship->id }}/assessments" class="bg-gray-50 border rounded p-4 mb-6">
@csrf
<h2 class="font-bold mb-2">Tambah / Perbarui Penilaian Aspek</h2>
<div class="flex flex-wrap gap-2">
<input type="text" name="aspect" required list="aspects" placeholder="Aspek (mis. kedisiplinan, keterampilan, sikap)" class="border p-2 rounded flex-1">
<datalist id="aspects"><option value="kedisiplinan"><option value="keterampilan"><option value="sikap"><option value="kerjasama"><option value="tanggung jawab"></datalist>
<input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai 0-100" class="border p-2 rounded">
<input type="text" name="note" placeholder="Catatan (opsional)" class="border p-2 rounded flex-1">
</div>
<p class="text-xs text-gray-500 mt-2">Aspek yang sama pada penempatan ini hanya satu baris; mengirim aspek yang sama akan memperbarui nilainya.</p>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Penilaian</button>
</form>

@if($internship->assessments->isNotEmpty())
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Aspek</th><th class="border p-2 text-left">Nilai</th>
<th class="border p-2 text-left">Catatan</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
@foreach($internship->assessments as $a)
<tr>
<td class="border p-2">{{ $a->aspect }}</td>
<td class="border p-2">{{ $a->score === null ? '-' : number_format((float) $a->score, 2, ',', '.') }}</td>
<td class="border p-2">{{ $a->note ?? '-' }}</td>
<td class="border p-2">
<form method="POST" action="/admin/internships/assessments/{{ $a->id }}" class="inline-flex flex-wrap gap-1 items-center">
@csrf @method('PUT')
<input type="hidden" name="aspect" value="{{ $a->aspect }}">
<input type="number" name="score" step="0.01" min="0" max="100" value="{{ $a->score }}" class="border p-1 rounded w-24">
<input type="text" name="note" value="{{ $a->note }}" placeholder="Catatan" class="border p-1 rounded">
<button class="bg-blue-600 text-white px-2 py-1 rounded">Ubah</button>
</form>
<form method="POST" action="/admin/internships/assessments/{{ $a->id }}" class="inline ml-2" onsubmit="return confirm('Hapus penilaian aspek ini?')">
@csrf @method('DELETE')
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
@else
<p class="text-gray-500">Belum ada penilaian aspek untuk penempatan ini.</p>
@endif
</body>
</html>
