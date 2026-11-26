<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>PKL Kelas Wali</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">PKL Siswa Kelas Wali (Hanya Lihat)</h1>
<p class="text-sm text-gray-600 mb-4"><a href="/wali/internships/recap" class="text-blue-600 underline">Rekap Hasil PKL</a> &middot; <a href="/wali/competency-exams" class="text-blue-600 underline">UKK kelas wali</a></p>

<form method="GET" action="/wali/internships" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas Wali Saya</option>
<?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(($filters['class_id'] ?? null) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
<?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['student_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<?php if($items->isNotEmpty()): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Perusahaan</th>
<th class="border p-2 text-left">Periode</th><th class="border p-2 text-left">Status</th>
<th class="border p-2 text-left">Pembimbing</th><th class="border p-2 text-left">Grade</th>
<th class="border p-2 text-left">Rata-rata Aspek</th>
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
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<div class="mt-4"><?php echo e($items->links()); ?></div>
<?php else: ?>
<p class="text-gray-500">Belum ada penempatan PKL untuk siswa kelas wali Anda.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/wali/internships/index.blade.php ENDPATH**/ ?>