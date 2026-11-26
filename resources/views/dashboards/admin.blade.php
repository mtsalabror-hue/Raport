<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Admin Dashboard</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 p-6">
<h1 class="text-2xl font-bold mb-4">Dashboard Admin</h1>
<div class="grid grid-cols-4 gap-4">
<div class="bg-white p-4 rounded shadow">Siswa: {{ $students }}</div>
<div class="bg-white p-4 rounded shadow">Guru: {{ $teachers }}</div>
<div class="bg-white p-4 rounded shadow">Kelas: {{ $classes }}</div>
<div class="bg-white p-4 rounded shadow">Mapel: {{ $subjects }}</div>
<div class="bg-white p-4 rounded shadow">TA Aktif: {{ $activeYear?->name ?? '-' }}</div>
</div>
<form method="POST" action="/logout" class="mt-4">@csrf<button class="bg-red-600 text-white px-4 py-2 rounded">Logout</button></form>
</body></html>
