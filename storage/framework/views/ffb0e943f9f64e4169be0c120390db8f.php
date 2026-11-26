<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pemeriksaan Rapor</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Pemeriksaan Rapor</h1>
<p class="text-gray-600 text-sm mb-4">Rapor yang diajukan wali kelas diperiksa di sini. Hanya admin yang dapat menyetujui, mengembalikan, menerbitkan, dan mengunci rapor.</p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<div class="flex gap-4 text-sm mb-4">
<?php $__currentLoopData = ['draft' => 'Belum Lengkap', 'diajukan' => 'Diajukan', 'divalidasi' => 'Disetujui', 'diterbitkan' => 'Diterbitkan', 'dikunci' => 'Terkunci']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<span class="bg-gray-100 border rounded px-2 py-1"><?php echo e($label); ?>: <strong><?php echo e($counts[$status] ?? 0); ?></strong></span>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<form method="GET" action="/admin/report-cards" class="flex flex-wrap gap-2 mb-4 text-sm">
<select name="class_id" class="border rounded px-2 py-1">
<option value="">Semua kelas</option>
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
<th class="border px-2 py-2 text-left">Diterbitkan</th>
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
<td class="border px-2 py-1"><?php echo e($card->issued_at ? $card->issued_at->format('d-m-Y H:i') : '-'); ?></td>
<td class="border px-2 py-1">
<a href="/admin/report-cards/<?php echo e($card->id); ?>" class="text-blue-600 underline">Periksa</a>
<?php if($card->isPublished()): ?>
| <a href="/admin/report-cards/<?php echo e($card->id); ?>/pdf" class="text-blue-600 underline">PDF</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr><td colspan="7" class="border px-2 py-2 text-gray-500">Belum ada rapor pada filter ini.</td></tr>
<?php endif; ?>
</tbody>
</table>

<div class="mt-4"><?php echo e($cards->links()); ?></div>
<div class="mt-4 text-sm"><a href="/admin/dashboard" class="text-blue-600 underline">Dashboard Admin</a></div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/report_cards/index.blade.php ENDPATH**/ ?>