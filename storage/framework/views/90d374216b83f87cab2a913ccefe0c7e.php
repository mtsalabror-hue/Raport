<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Hasil PKL Kelas Wali</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Hasil PKL Kelas Wali (Hanya Lihat)</h1>
<p class="text-sm mb-4"><a href="/wali/internships" class="text-blue-600 underline">&larr; Daftar PKL</a></p>

<form method="GET" action="/wali/internships/recap" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas Wali Saya</option>
<?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(($filters['class_id'] ?? null) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
<?php $__currentLoopData = $allStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['student_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<?php if(count($recaps)): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Penempatan</th>
<th class="border p-2 text-left">Selesai</th><th class="border p-2 text-left">Berjalan</th>
<th class="border p-2 text-left">Rata-rata Gabungan</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $recaps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2 align-top"><?php echo e($students[$r['student_id']]->name ?? '-'); ?></td>
<td class="border p-2">
<ul class="list-disc pl-5">
<?php $__currentLoopData = $r['placements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<li><?php echo e($p['internship']->company_name); ?> (<?php echo e($p['internship']->start_date->format('Y-m-d')); ?> s.d. <?php echo e($p['internship']->end_date->format('Y-m-d')); ?>, <?php echo e(ucfirst($p['status'])); ?>) &mdash; rata-rata: <?php echo e($p['average'] === null ? '-' : number_format($p['average'], 2, ',', '.')); ?></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
</td>
<td class="border p-2 align-top"><?php echo e($r['finished']); ?></td>
<td class="border p-2 align-top"><?php echo e($r['active']); ?></td>
<td class="border p-2 align-top"><?php echo e($r['average'] === null ? '-' : number_format($r['average'], 2, ',', '.')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500">Belum ada data PKL untuk rekap.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/wali/internships/recap.blade.php ENDPATH**/ ?>