<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Perubahan Nilai</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Riwayat Perubahan Nilai</h1>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<h2 class="text-lg font-semibold mt-6 mb-2">Pengajuan Revisi</h2>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Mapel</th>
<th class="border px-2 py-2">Nilai Lama</th>
<th class="border px-2 py-2">Nilai Baru</th>
<th class="border px-2 py-2 text-left">Alasan</th>
<th class="border px-2 py-2">Status</th>
<th class="border px-2 py-2">Diajukan</th>
</tr>
</thead>
<tbody>
@foreach($revisions as $r)
<tr>
<td class="border px-2 py-1">{{ $r->score?->student?->name }}</td>
<td class="border px-2 py-1">{{ $r->score?->subject?->name }}</td>
<td class="border px-2 py-1">{{ $r->old_score }}</td>
<td class="border px-2 py-1">{{ $r->new_score }}</td>
<td class="border px-2 py-1">{{ $r->reason }}</td>
<td class="border px-2 py-1">{{ $r->status }}</td>
<td class="border px-2 py-1">{{ $r->created_at }}</td>
</tr>
@endforeach
</tbody>
</table>
{{ $revisions->links() }}

<h2 class="text-lg font-semibold mt-6 mb-2">Log Aktivitas Nilai</h2>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Aksi</th>
<th class="border px-2 py-2 text-left">Model</th>
<th class="border px-2 py-2">ID</th>
<th class="border px-2 py-2 text-left">Detail</th>
<th class="border px-2 py-2">Waktu</th>
</tr>
</thead>
<tbody>
@foreach($logs as $log)
<tr>
<td class="border px-2 py-1">{{ $log->action }}</td>
<td class="border px-2 py-1">{{ class_basename($log->model_type) }}</td>
<td class="border px-2 py-1">{{ $log->model_id }}</td>
<td class="border px-2 py-1">{{ json_encode($log->metadata) }}</td>
<td class="border px-2 py-1">{{ $log->created_at }}</td>
</tr>
@endforeach
</tbody>
</table>
{{ $logs->links() }}

<div class="mt-4 text-sm"><a href="/guru/assessments" class="text-blue-600 underline">Kembali ke Input Nilai</a></div>
</body>
</html>
