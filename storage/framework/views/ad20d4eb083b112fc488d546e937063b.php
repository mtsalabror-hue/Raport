<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Monitoring PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Monitoring PKL</h1>
<p class="text-sm mb-4"><a href="/admin/internships" class="text-blue-600 underline">&larr; Daftar penempatan</a> &middot; <a href="/admin/internships/recap" class="text-blue-600 underline">Rekap Hasil PKL</a></p>

<form method="GET" action="/admin/internships/monitoring" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
<?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['student_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="company_name" class="border p-2 rounded">
<option value="">Semua Perusahaan</option>
<?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>" <?php if(($filters['company_name'] ?? null) == $c): echo 'selected'; endif; ?>><?php echo e($c); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<?php $__currentLoopData = ['berjalan' => 'Berjalan / Aktif', 'mendatang' => 'Mendatang', 'selesai' => 'Selesai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<h2 class="font-bold mt-6 mb-2"><?php echo e($label); ?> (<?php echo e($groups[$key]->count()); ?>)</h2>
<?php if($groups[$key]->isNotEmpty()): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Pembimbing</th>
<th class="border p-2 text-left">Grade</th><th class="border p-2 text-left">Rata-rata Aspek</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $groups[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2"><?php echo e($p->student->name ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->company_name); ?></td>
<td class="border p-2"><?php echo e($p->start_date->format('Y-m-d')); ?> s.d. <?php echo e($p->end_date->format('Y-m-d')); ?></td>
<td class="border p-2"><?php echo e($p->supervisor ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->grade ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->averageScore() === null ? '-' : number_format($p->averageScore(), 2, ',', '.')); ?></td>
<td class="border p-2"><a href="/admin/internships/<?php echo e($p->id); ?>/assessments" class="text-blue-600 underline">Penilaian</a></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500 text-sm">Tidak ada penempatan <?php echo e(strtolower($label)); ?>.</p>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/internships/monitoring.blade.php ENDPATH**/ ?>