<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Presensi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Rekap Presensi <?php echo e(($readOnly ?? false) ? '(Wali Kelas - hanya baca)' : ''); ?></h1>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<form method="GET" action="<?php echo e(($readOnly ?? false) ? '/wali/attendance/recap' : '/guru/attendance/recap'); ?>" class="flex flex-wrap gap-2 mb-6">
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
<button class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan Rekap</button>
</form>

<?php if($classTotal !== null): ?>
<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
<strong>Total Kelas:</strong>
Hadir <?php echo e($classTotal['hadir']); ?> &middot; Sakit <?php echo e($classTotal['sakit']); ?> &middot;
Izin <?php echo e($classTotal['izin']); ?> &middot; Tanpa keterangan <?php echo e($classTotal['alpa']); ?> &middot;
Total <?php echo e($classTotal['total']); ?> catatan
&middot; Persentase hadir <?php echo e($classTotal['persentase_hadir'] !== null ? $classTotal['persentase_hadir'].'%' : '-'); ?>

</div>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2">Hadir</th>
<th class="border px-2 py-2">Sakit</th>
<th class="border px-2 py-2">Izin</th>
<th class="border px-2 py-2">Tanpa Ket.</th>
<th class="border px-2 py-2">Total</th>
<th class="border px-2 py-2">% Hadir</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($row['student']->name); ?></td>
<td class="border px-2 py-1"><?php echo e($row['hadir']); ?></td>
<td class="border px-2 py-1"><?php echo e($row['sakit']); ?></td>
<td class="border px-2 py-1"><?php echo e($row['izin']); ?></td>
<td class="border px-2 py-1"><?php echo e($row['alpa']); ?></td>
<td class="border px-2 py-1"><?php echo e($row['total']); ?></td>
<td class="border px-2 py-1"><?php echo e($row['persentase_hadir'] !== null ? $row['persentase_hadir'].'%' : '-'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500 mt-4">Pilih kelas, tahun ajaran, dan semester untuk menampilkan rekap. Rekap memakai satu periode eksplisit dan tidak mencampur semester lain.</p>
<?php endif; ?>

<div class="mt-4 text-sm flex gap-4">
<a href="<?php echo e(($readOnly ?? false) ? '/wali/attendance/academic-recap' : '/guru/attendance/academic-recap'); ?>" class="text-blue-600 underline">Rekap Akademik</a>
<?php if(!($readOnly ?? false)): ?>
<a href="/guru/attendance" class="text-blue-600 underline">Input Presensi</a>
<?php endif; ?>
</div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/teacher/attendance/recap.blade.php ENDPATH**/ ?>