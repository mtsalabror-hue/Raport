<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Presensi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Riwayat Presensi</h1>

<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<form method="GET" action="/guru/attendance/history" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border p-2 rounded">
<option value="">Semua Kelas</option>
<?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(($filters['class_id'] ?? null) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>" class="border p-2 rounded" title="Dari tanggal">
<input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>" class="border p-2 rounded" title="Sampai tanggal">
<select name="academic_year_id" class="border p-2 rounded">
<option value="">Semua Tahun Ajaran</option>
<?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($y->id); ?>" <?php if(($filters['academic_year_id'] ?? null) == $y->id): echo 'selected'; endif; ?>><?php echo e($y->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="semester_id" class="border p-2 rounded">
<option value="">Semua Semester</option>
<?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['semester_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="status" class="border p-2 rounded">
<option value="">Semua Status</option>
<?php $__currentLoopData = ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<option value="<?php echo e($code); ?>" <?php if(($filters['status'] ?? null) === $code): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Tanggal</th>
<th class="border px-2 py-2 text-left">Siswa</th>
<th class="border px-2 py-2 text-left">Kelas</th>
<th class="border px-2 py-2">Status</th>
<th class="border px-2 py-2 text-left">Catatan</th>
<th class="border px-2 py-2">Koreksi</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($r->date->toDateString()); ?></td>
<td class="border px-2 py-1"><?php echo e($r->student?->name); ?></td>
<td class="border px-2 py-1"><?php echo e($r->schoolClass?->name); ?></td>
<td class="border px-2 py-1"><?php echo e(\App\Models\StudentAttendance::STATUSES[$r->status] ?? $r->status); ?></td>
<td class="border px-2 py-1"><?php echo e($r->note ?? '-'); ?></td>
<td class="border px-2 py-1">
<form method="POST" action="/guru/attendance/<?php echo e($r->id); ?>/correct" class="flex gap-1">
<?php echo csrf_field(); ?>
<select name="status" class="border p-1 rounded">
<?php $__currentLoopData = ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<option value="<?php echo e($code); ?>" <?php if($r->status === $code): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<button class="bg-amber-600 text-white px-2 py-1 rounded text-xs">Koreksi</button>
</form>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>

<?php if($records->isEmpty()): ?>
<p class="text-gray-500 mt-4">Tidak ada catatan presensi untuk filter ini.</p>
<?php endif; ?>

<div class="mt-4"><?php echo e($records->links()); ?></div>

<div class="mt-4 text-sm"><a href="/guru/attendance" class="text-blue-600 underline">Kembali ke Input Presensi</a></div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/teacher/attendance/history.blade.php ENDPATH**/ ?>