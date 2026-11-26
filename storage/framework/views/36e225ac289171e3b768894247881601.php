<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Status Rapor Kelas Saya</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Status Rapor Kelas Saya</h1>
<p class="text-gray-600 text-sm mb-4">Status rapor siswa di kelas wali Anda. PDF hanya tersedia untuk rapor yang sudah diterbitkan atau dikunci.</p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="GET" action="/wali/report-cards" class="flex flex-wrap gap-2 mb-4 text-sm">
<select name="class_id" class="border rounded px-2 py-1">
<option value="">Semua kelas saya</option>
<?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(($filters['class_id'] ?? null) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="academic_year_id" class="border rounded px-2 py-1">
<option value="">Semua tahun ajaran</option>
<?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($y->id); ?>" <?php if(($filters['academic_year_id'] ?? null) == $y->id): echo 'selected'; endif; ?>><?php echo e($y->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="semester_id" class="border rounded px-2 py-1">
<option value="">Semua semester</option>
<?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['semester_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="status" class="border rounded px-2 py-1">
<option value="">Semua status</option>
<?php $__currentLoopData = ['draft' => 'Belum Lengkap', 'diajukan' => 'Diajukan', 'divalidasi' => 'Disetujui', 'diterbitkan' => 'Diterbitkan', 'dikunci' => 'Terkunci']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<option value="<?php echo e($status); ?>" <?php if(($filters['status'] ?? null) === $status): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-3 py-1 rounded">Filter</button>
</form>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2 text-left">Tahun Ajaran</th>
<th class="border px-2 py-2 text-left">Semester</th>
<th class="border px-2 py-2 text-left">Status</th>
<th class="border px-2 py-2">Aksi</th>
</tr>
</thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td class="border px-2 py-1"><?php echo e($card->student->name ?? '-'); ?><br><span class="text-gray-500 text-xs">NIS <?php echo e($card->student->nis ?? '-'); ?></span></td>
<td class="border px-2 py-1"><?php echo e($card->schoolClass->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($card->academicYear->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($card->semester->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($card->documentLabel()); ?></td>
<td class="border px-2 py-1">
<a href="/wali/preview/<?php echo e($card->student_id); ?>?academic_year_id=<?php echo e($card->academic_year_id); ?>&semester_id=<?php echo e($card->semester_id); ?>" class="text-blue-600 underline">Preview</a>
<?php if($card->isPublished()): ?>
| <a href="/wali/report-cards/<?php echo e($card->id); ?>/pdf" class="text-blue-600 underline">Unduh PDF</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr><td colspan="6" class="border px-2 py-2 text-gray-500">Belum ada rapor pada filter ini.</td></tr>
<?php endif; ?>
</tbody>
</table>

<div class="mt-4"><?php echo e($cards->links()); ?></div>
<div class="mt-4 text-sm"><a href="/wali/dashboard" class="text-blue-600 underline">Dashboard Wali</a></div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/wali/report_cards/index.blade.php ENDPATH**/ ?>