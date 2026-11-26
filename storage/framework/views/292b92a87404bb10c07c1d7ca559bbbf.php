<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil UKK Mapel Saya</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Hasil UKK (Mapel yang Saya Ampu)</h1>
<p class="text-sm text-gray-600 mb-4">Hanya-baca. Hanya jadwal untuk mapel yang Anda ampu (teaching_assignments) yang tampil. Tidak ada label lulus/tidak lulus — ambang kelulusan belum ditetapkan sekolah.</p>

<h2 class="font-bold mb-2">Jadwal Ujian</h2>
<?php if($schedules->isNotEmpty()): ?>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Mapel</th><th class="border p-2 text-left">Tanggal Ujian</th>
<th class="border p-2 text-left">Peserta</th><th class="border p-2 text-left">Sudah Dinilai</th>
<th class="border p-2 text-left">Rata-rata</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2"><?php echo e($sch['subject']->name ?? '-'); ?></td>
<td class="border p-2"><?php echo e($sch['exam_date'] ?? '-'); ?></td>
<td class="border p-2"><?php echo e($sch['participants']); ?></td>
<td class="border p-2"><?php echo e($sch['scored']); ?></td>
<td class="border p-2"><?php echo e($sch['average'] === null ? '-' : number_format($sch['average'], 2, ',', '.')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500 mb-6">Belum ada jadwal UKK untuk mapel yang Anda ampu.</p>
<?php endif; ?>

<h2 class="font-bold mb-2">Rekap per Mapel</h2>
<?php if(count($perSubject)): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Mapel</th><th class="border p-2 text-left">Peserta</th>
<th class="border p-2 text-left">Sudah Dinilai</th><th class="border p-2 text-left">Rata-rata</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $perSubject; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2"><?php echo e($ps['subject']->name ?? '-'); ?></td>
<td class="border p-2"><?php echo e($ps['participants']); ?></td>
<td class="border p-2"><?php echo e($ps['scored']); ?></td>
<td class="border p-2"><?php echo e($ps['average'] === null ? '-' : number_format($ps['average'], 2, ',', '.')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500">Belum ada data UKK untuk rekap.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/guru/competency_exams/index.blade.php ENDPATH**/ ?>