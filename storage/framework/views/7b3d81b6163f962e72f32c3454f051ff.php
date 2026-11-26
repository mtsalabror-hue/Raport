<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Akademik - <?php echo e($student->name); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Riwayat Akademik</h1>
<p class="text-gray-600 text-sm mb-4">Siswa: <strong><?php echo e($student->name); ?></strong> (NIS <?php echo e($student->nis); ?>) — hanya-baca, lintas semester.</p>

<?php $__empty_1 = true; $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="border rounded p-4 mb-6">
<h2 class="font-bold mb-2"><?php echo e($period['academic_year']->name ?? '-'); ?> &middot; Semester <?php echo e($period['semester']->name ?? '-'); ?></h2>

<h3 class="font-medium mb-1 text-sm">Nilai Akhir</h3>
<?php if(!empty($period['finals'])): ?>
<table class="w-full border text-sm mb-4">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Mata Pelajaran</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2">KKTP</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $period['finals']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($row['subject']->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($row['kktp']); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-sm text-gray-500 mb-4">Belum ada nilai pada periode ini.</p>
<?php endif; ?>

<h3 class="font-medium mb-1 text-sm">Presensi</h3>
<p class="text-sm mb-4">Hadir <?php echo e($period['attendance']['hadir']); ?> &middot; Sakit <?php echo e($period['attendance']['sakit']); ?> &middot; Izin <?php echo e($period['attendance']['izin']); ?> &middot; Tanpa keterangan <?php echo e($period['attendance']['alpa']); ?> &middot; Total <?php echo e($period['attendance']['total']); ?> catatan &middot; Persentase hadir <?php echo e($period['attendance']['persentase_hadir'] !== null ? $period['attendance']['persentase_hadir'].'%' : '-'); ?></p>

<h3 class="font-medium mb-1 text-sm">Prestasi</h3>
<?php if($period['achievements']->isNotEmpty()): ?>
<ul class="list-disc pl-5 text-sm">
<?php $__currentLoopData = $period['achievements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><strong><?php echo e($a->title); ?></strong><?php if($a->level): ?> (<?php echo e($a->level); ?>)<?php endif; ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php else: ?>
<p class="text-sm text-gray-500">Belum ada prestasi pada periode ini.</p>
<?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<p class="text-gray-500">Belum ada riwayat akademik untuk siswa ini.</p>
<?php endif; ?>

<div class="mt-4 text-sm flex gap-4">
<a href="/wali/preview/<?php echo e($student->id); ?>" class="text-blue-600 underline">Preview Rapor</a>
<a href="/wali/dashboard" class="text-blue-600 underline">Dashboard</a>
</div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/wali/history.blade.php ENDPATH**/ ?>