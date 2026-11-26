<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Akademik</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Akademik <?php echo e(($readOnly ?? false) ? '(Wali Kelas - hanya baca, nilai terkirim/dikunci)' : ''); ?></h1>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<form method="GET" action="<?php echo e(($readOnly ?? false) ? '/wali/attendance/academic-recap' : '/guru/attendance/academic-recap'); ?>" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Pilih Kelas</option>
<?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(($filters['class_id'] ?? null) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="subject_id" class="border p-2 rounded">
<option value="">Pilih Mapel</option>
<?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['subject_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?> (<?php echo e($s->code); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Tahun Ajaran</option>
<?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($y->id); ?>" <?php if(($filters['academic_year_id'] ?? null) == $y->id): echo 'selected'; endif; ?>><?php echo e($y->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semester</option>
<?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['semester_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan Rekap</button>
</form>

<?php if($kktp !== null): ?>
<p class="text-sm text-gray-600 mb-4">KKTP: <?php echo e($kktp); ?>. Nilai akhir dihitung dari komponen yang ada nilainya saja (AssessmentService).</p>
<?php endif; ?>

<?php if($stats !== null): ?>
<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
<strong>Ringkasan kelas:</strong>
<?php echo e($stats['total_siswa']); ?> siswa &middot;
Tuntas <?php echo e($stats['tuntas']); ?> &middot;
Belum tuntas <?php echo e($stats['belum_tuntas']); ?> &middot;
Belum dinilai <?php echo e($stats['belum_dinilai']); ?>

</div>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2">KKTP</th>
<th class="border px-2 py-2">Ketuntasan</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $f = $finals[$student->id] ?? null; ?>
<tr>
<td class="border px-2 py-1"><?php echo e($student->name); ?></td>
<td class="border px-2 py-1"><?php echo e($f['final_score'] ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($kktp); ?></td>
<td class="border px-2 py-1">
<?php if($f && $f['passed'] === true): ?> Tuntas
<?php elseif($f && $f['passed'] === false): ?> Belum tuntas
<?php else: ?> -
<?php endif; ?>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500 mt-4">Pilih kelas, mapel, tahun ajaran, dan semester untuk menampilkan rekap akademik.</p>
<?php endif; ?>

<div class="mt-4 text-sm flex gap-4">
<a href="<?php echo e(($readOnly ?? false) ? '/wali/attendance/recap' : '/guru/attendance/recap'); ?>" class="text-blue-600 underline">Rekap Presensi</a>
<?php if(!($readOnly ?? false)): ?>
<a href="/guru/attendance" class="text-blue-600 underline">Input Presensi</a>
<?php endif; ?>
</div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/teacher/attendance/academic-recap.blade.php ENDPATH**/ ?>