<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Admin Dashboard</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 p-6">
<h1 class="text-2xl font-bold mb-4">Dashboard Admin</h1>
<div class="grid grid-cols-4 gap-4">
<div class="bg-white p-4 rounded shadow">Siswa: <?php echo e($students); ?></div>
<div class="bg-white p-4 rounded shadow">Guru: <?php echo e($teachers); ?></div>
<div class="bg-white p-4 rounded shadow">Kelas: <?php echo e($classes); ?></div>
<div class="bg-white p-4 rounded shadow">Mapel: <?php echo e($subjects); ?></div>
<div class="bg-white p-4 rounded shadow">TA Aktif: <?php echo e($activeYear?->name ?? '-'); ?></div>
</div>
<form method="POST" action="/logout" class="mt-4"><?php echo csrf_field(); ?><button class="bg-red-600 text-white px-4 py-2 rounded">Logout</button></form>
</body></html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/dashboards/admin.blade.php ENDPATH**/ ?>