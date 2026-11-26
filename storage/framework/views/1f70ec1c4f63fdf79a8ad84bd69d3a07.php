<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>PKL Bimbingan Saya</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">PKL Bimbingan Saya</h1>
<p class="text-sm text-gray-600 mb-4">Hanya penempatan yang kolom pembimbingnya berisi nama Anda (<?php echo e($teacher->name); ?>) yang tampil di sini. <a href="/guru/competency-exams" class="text-blue-600 underline">Hasil UKK mapel yang saya ampu</a></p>

<?php if($items->isNotEmpty()): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Status</th>
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
<td class="border p-2"><?php echo e($p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.')); ?></td>
<td class="border p-2"><a href="/guru/internships/<?php echo e($p->id); ?>" class="text-blue-600 underline">Lihat &amp; Nilai</a></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<div class="mt-4"><?php echo e($items->links()); ?></div>
<?php else: ?>
<p class="text-gray-500">Belum ada penempatan PKL dengan Anda sebagai pembimbing.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/guru/internships/index.blade.php ENDPATH**/ ?>