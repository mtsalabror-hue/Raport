<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>UKK Kelas Wali</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">UKK Siswa Kelas Wali (Hanya Lihat)</h1>
<p class="text-sm text-gray-600 mb-4"><a href="/wali/internships" class="text-blue-600 underline">PKL kelas wali</a></p>
<p class="text-sm text-gray-600 mb-4">Tidak ada label lulus/tidak lulus — ambang kelulusan belum ditetapkan sekolah, jadi hanya skor yang ditampilkan.</p>

<form method="GET" action="/wali/competency-exams" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas Wali Saya</option>
<?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(($filters['class_id'] ?? null) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
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
<p class="text-gray-500 mb-6">Belum ada jadwal UKK untuk siswa kelas wali Anda.</p>
<?php endif; ?>

<h2 class="font-bold mb-2">Rekap per Siswa</h2>
<?php if(count($recaps)): ?>
<table class="w-full border text-sm">
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
<p class="text-gray-500">Belum ada data UKK untuk rekap.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/wali/competency_exams/index.blade.php ENDPATH**/ ?>