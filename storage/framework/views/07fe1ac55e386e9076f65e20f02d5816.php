<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Hasil UKK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Hasil UKK</h1>
<p class="text-sm mb-4"><a href="/admin/competency-exams" class="text-blue-600 underline">&larr; Daftar jadwal</a></p>
<p class="text-sm text-gray-600 mb-4">Nilai UKK tidak otomatis masuk ke rapor. Tidak ada label lulus/tidak lulus — ambang kelulusan belum ditetapkan sekolah, jadi hanya skor yang ditampilkan.</p>

<form method="GET" action="/admin/competency-exams/recap" class="flex flex-wrap gap-2 mb-6">
<select name="student_id" class="border p-2 rounded">
<option value="">Semua Siswa</option>
<?php $__currentLoopData = $allStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['student_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="subject_id" class="border p-2 rounded">
<option value="">Semua Mapel</option>
<?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['subject_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<h2 class="font-bold mb-2">Per Siswa</h2>
<?php if(count($recaps)): ?>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Per Mapel</th>
<th class="border p-2 text-left">Rata-rata Gabungan</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $recaps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2 align-top"><?php echo e($students[$r['student_id']]->name ?? '-'); ?></td>
<td class="border p-2">
<ul class="list-disc pl-5">
<?php $__currentLoopData = $r['per_subject']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<li><?php echo e($ps['subject']->name ?? '-'); ?> (<?php echo e($ps['count']); ?> ujian) &mdash; rata-rata: <?php echo e($ps['average'] === null ? '-' : number_format($ps['average'], 2, ',', '.')); ?></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
</td>
<td class="border p-2 align-top"><?php echo e($r['average'] === null ? '-' : number_format($r['average'], 2, ',', '.')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500 mb-6">Belum ada data UKK untuk rekap.</p>
<?php endif; ?>

<h2 class="font-bold mb-2">Per Mapel</h2>
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
<p class="text-gray-500">Belum ada data UKK per mapel.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/competency_exams/recap.blade.php ENDPATH**/ ?>