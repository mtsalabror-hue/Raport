<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Monitoring Presensi &amp; Nilai</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Monitoring Presensi &amp; Nilai (Admin)</h1>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<form method="GET" action="/admin/attendance/monitoring" class="flex flex-wrap gap-2 mb-6">
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Semua Tahun Ajaran</option>
<?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($y->id); ?>" <?php if(($filters['academic_year_id'] ?? null) == $y->id): echo 'selected'; endif; ?>><?php echo e($y->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semua Semester</option>
<?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['semester_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="teacher_id" class="border p-2 rounded">
<option value="">Semua Guru</option>
<?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($t->id); ?>" <?php if(($filters['teacher_id'] ?? null) == $t->id): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<h2 class="text-lg font-semibold mb-2">Nilai Belum Dikirim (status draft)</h2>
<p class="text-sm text-gray-600 mb-4">Total <?php echo e($totalDrafts); ?> nilai masih berstatus draft dan belum dikirim oleh guru.</p>

<table class="w-full border text-sm mb-8">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Guru</th>
<th class="border px-2 py-2 text-left">Mapel</th>
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2 text-left">Tahun Ajaran</th>
<th class="border px-2 py-2 text-left">Semester</th>
<th class="border px-2 py-2">Jumlah Nilai Draft</th>
<th class="border px-2 py-2">Jumlah Siswa</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $drafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($d->teacher?->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($d->subject?->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($d->schoolClass?->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($d->academicYear?->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($d->semester?->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($d->draft_count); ?></td>
<td class="border px-2 py-1"><?php echo e($d->student_count); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>

<?php if($drafts->isEmpty()): ?>
<p class="text-gray-500 mb-8">Tidak ada nilai draft untuk filter ini.</p>
<?php endif; ?>

<h2 class="text-lg font-semibold mb-2">Rekap Presensi per Kelas (per semester)</h2>
<?php if(empty($attendanceByClass)): ?>
<p class="text-gray-500">Pilih tahun ajaran dan semester untuk menampilkan rekap presensi per kelas.</p>
<?php else: ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2">Hadir</th>
<th class="border px-2 py-2">Sakit</th>
<th class="border px-2 py-2">Izin</th>
<th class="border px-2 py-2">Tanpa Ket.</th>
<th class="border px-2 py-2">Total</th>
<th class="border px-2 py-2">% Hadir</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $attendanceByClass; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classId => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($classes->firstWhere('id', $classId)?->name ?? $classId); ?></td>
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
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/attendance/monitoring.blade.php ENDPATH**/ ?>