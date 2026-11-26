<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Perubahan Nilai</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Riwayat Perubahan Nilai</h1>

<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<h2 class="text-lg font-semibold mt-6 mb-2">Pengajuan Revisi</h2>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Mapel</th>
<th class="border px-2 py-2">Nilai Lama</th>
<th class="border px-2 py-2">Nilai Baru</th>
<th class="border px-2 py-2 text-left">Alasan</th>
<th class="border px-2 py-2">Status</th>
<th class="border px-2 py-2">Diajukan</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $revisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($r->score?->student?->name); ?></td>
<td class="border px-2 py-1"><?php echo e($r->score?->subject?->name); ?></td>
<td class="border px-2 py-1"><?php echo e($r->old_score); ?></td>
<td class="border px-2 py-1"><?php echo e($r->new_score); ?></td>
<td class="border px-2 py-1"><?php echo e($r->reason); ?></td>
<td class="border px-2 py-1"><?php echo e($r->status); ?></td>
<td class="border px-2 py-1"><?php echo e($r->created_at); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php echo e($revisions->links()); ?>


<h2 class="text-lg font-semibold mt-6 mb-2">Log Aktivitas Nilai</h2>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Aksi</th>
<th class="border px-2 py-2 text-left">Model</th>
<th class="border px-2 py-2">ID</th>
<th class="border px-2 py-2 text-left">Detail</th>
<th class="border px-2 py-2">Waktu</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($log->action); ?></td>
<td class="border px-2 py-1"><?php echo e(class_basename($log->model_type)); ?></td>
<td class="border px-2 py-1"><?php echo e($log->model_id); ?></td>
<td class="border px-2 py-1"><?php echo e(json_encode($log->metadata)); ?></td>
<td class="border px-2 py-1"><?php echo e($log->created_at); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php echo e($logs->links()); ?>


<div class="mt-4 text-sm"><a href="/guru/assessments" class="text-blue-600 underline">Kembali ke Input Nilai</a></div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/teacher/assessments/history.blade.php ENDPATH**/ ?>