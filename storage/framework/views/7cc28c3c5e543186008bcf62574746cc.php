<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Penempatan PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Penempatan PKL</h1>
<p class="text-sm text-gray-600 mb-4">Periode PKL ditentukan dari tanggal mulai s.d. tanggal selesai (tidak ada kolom tahun ajaran/semester pada tabel internships). <a href="/admin/internships/monitoring" class="text-blue-600 underline">Monitoring</a> &middot; <a href="/admin/internships/recap" class="text-blue-600 underline">Rekap Hasil PKL</a></p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<h2 class="font-bold mb-2">Mitra Industri (<?php echo e(count($companies)); ?>)</h2>
<?php if(count($companies)): ?>
<p class="text-sm mb-4"><?php echo e(implode(', ', $companies)); ?></p>
<?php else: ?>
<p class="text-sm text-gray-500 mb-4">Belum ada mitra industri. Mitra baru tercatat otomatis saat penempatan ditambahkan.</p>
<?php endif; ?>

<form method="GET" action="/admin/internships" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
<?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['student_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="company_name" class="border p-2 rounded">
<option value="">Semua Perusahaan</option>
<?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>" <?php if(($filters['company_name'] ?? null) == $c): echo 'selected'; endif; ?>><?php echo e($c); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="status" class="border p-2 rounded">
<option value="">Semua Status</option>
<?php $__currentLoopData = ['mendatang' => 'Mendatang', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>" <?php if(($filters['status'] ?? null) == $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<form method="POST" action="/admin/internships" class="bg-gray-50 border rounded p-4 mb-6">
<?php echo csrf_field(); ?>
<h2 class="font-bold mb-2">Tambah Penempatan PKL</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
<?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<input type="text" name="company_name" required list="companies" placeholder="Nama perusahaan / mitra industri" class="border p-2 rounded flex-1">
<datalist id="companies"><?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></datalist>
<input type="date" name="start_date" required class="border p-2 rounded">
<input type="date" name="end_date" required class="border p-2 rounded">
<input type="text" name="supervisor" placeholder="Pembimbing (nama guru)" class="border p-2 rounded">
<input type="text" name="grade" placeholder="Nilai akhir / grade (opsional)" class="border p-2 rounded">
</div>
<p class="text-xs text-gray-500 mt-2">Penempatan yang tanggalnya beririsan dengan penempatan lain siswa yang sama akan ditolak.</p>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Penempatan</button>
</form>

<?php if($items->isNotEmpty()): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Status</th>
<th class="border p-2 text-left">Pembimbing</th><th class="border p-2 text-left">Grade</th>
<th class="border p-2 text-left">Rata-rata Aspek</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2"><?php echo e($p->student->name ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->company_name); ?></td>
<td class="border p-2"><?php echo e($p->start_date->format('Y-m-d')); ?> s.d. <?php echo e($p->end_date->format('Y-m-d')); ?></td>
<td class="border p-2"><?php echo e(ucfirst($p->status())); ?></td>
<td class="border p-2"><?php echo e($p->supervisor ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->grade ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.')); ?></td>
<td class="border p-2 whitespace-nowrap">
<a href="/admin/internships/<?php echo e($p->id); ?>/assessments" class="text-blue-600 underline">Penilaian</a>
<a href="/admin/internships/<?php echo e($p->id); ?>/edit" class="text-blue-600 underline ml-2">Ubah</a>
<form method="POST" action="/admin/internships/<?php echo e($p->id); ?>" class="inline ml-2" onsubmit="return confirm('Hapus penempatan ini beserta penilaian aspeknya?')">
<?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<div class="mt-4"><?php echo e($items->links()); ?></div>
<?php else: ?>
<p class="text-gray-500">Belum ada penempatan PKL.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/internships/index.blade.php ENDPATH**/ ?>