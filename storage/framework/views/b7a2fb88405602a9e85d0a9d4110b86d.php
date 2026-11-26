<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Monitoring Kelengkapan Rapor</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Monitoring Kelengkapan Rapor</h1>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="GET" action="/wali/completeness" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Pilih Kelas</option>
<?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(($filters['class_id'] ?? null) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
<?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($y->id); ?>" <?php if(($filters['academic_year_id'] ?? null) == $y->id): echo 'selected'; endif; ?>><?php echo e($y->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
<?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['semester_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
</form>

<?php if($stats !== null): ?>
<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
<strong>Total <?php echo e($stats['total']); ?> siswa</strong> &middot; Lengkap: <?php echo e($stats['lengkap']); ?> &middot; Belum lengkap: <?php echo e($stats['belum_lengkap']); ?>

</div>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Status</th>
<th class="border px-2 py-2 text-left">Kekurangan (klik untuk ke sumber data)</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($check['student']->name); ?></td>
<td class="border px-2 py-1"><?php echo e($check['complete'] ? 'Lengkap' : 'Belum lengkap'); ?></td>
<td class="border px-2 py-1">
<?php if($check['complete']): ?> <span class="text-green-700">Tidak ada kekurangan.</span>
<?php else: ?>
<ul class="list-disc pl-4">
<?php $__currentLoopData = $check['missing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a class="text-blue-600 underline" href="<?php echo e($m['url']); ?>"><?php echo e($m['message']); ?></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php endif; ?>
</td>
<td class="border px-2 py-1 whitespace-nowrap">
<a href="/wali/preview/<?php echo e($check['student']->id); ?>?academic_year_id=<?php echo e($filters['academic_year_id']); ?>&semester_id=<?php echo e($filters['semester_id']); ?>" class="text-blue-600 underline">Preview</a>
<?php if($check['complete'] && ($check['report_card']->status ?? 'draft') === 'draft'): ?>
<form method="POST" action="/wali/submit" class="inline ml-2">
<?php echo csrf_field(); ?>
<input type="hidden" name="student_id" value="<?php echo e($check['student']->id); ?>">
<input type="hidden" name="class_id" value="<?php echo e($filters['class_id']); ?>">
<input type="hidden" name="academic_year_id" value="<?php echo e($filters['academic_year_id']); ?>">
<input type="hidden" name="semester_id" value="<?php echo e($filters['semester_id']); ?>">
<button class="text-green-700 underline">Ajukan Rapor</button>
</form>
<?php elseif(($check['report_card']->status ?? null) === 'diajukan'): ?>
<span class="text-gray-500 ml-2">Sudah diajukan</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500 mt-4">Pilih kelas, tahun ajaran, dan semester untuk memantau kelengkapan rapor.</p>
<?php endif; ?>

<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Kembali ke Dashboard</a></div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/wali/completeness.blade.php ENDPATH**/ ?>