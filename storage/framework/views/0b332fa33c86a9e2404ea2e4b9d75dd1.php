<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal UKK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-2">Jadwal UKK: <?php echo e($subject->name); ?> &mdash; <?php echo e($exam_date); ?></h1>
<p class="text-sm mb-4"><a href="/admin/competency-exams" class="text-blue-600 underline">&larr; Daftar jadwal</a> &middot; <a href="/admin/competency-exams/recap" class="text-blue-600 underline">Rekap Hasil UKK</a></p>
<p class="mb-4">Peserta: <strong><?php echo e($participants->count()); ?></strong> &middot; Rata-rata yang sudah dinilai: <strong><?php echo e($average === null ? '-' : number_format($average, 2, ',', '.')); ?></strong></p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="POST" action="/admin/competency-exams" class="bg-gray-50 border rounded p-4 mb-6">
<?php echo csrf_field(); ?>
<h2 class="font-bold mb-2">Tambah Peserta ke Jadwal Ini</h2>
<input type="hidden" name="subject_id" value="<?php echo e($subject->id); ?>">
<input type="hidden" name="exam_date" value="<?php echo e($exam_date); ?>">
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
<?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai (boleh kosong)" class="border p-2 rounded">
<input type="text" name="examiner" placeholder="Penguji (opsional)" class="border p-2 rounded">
<input type="text" name="note" placeholder="Catatan (opsional)" class="border p-2 rounded flex-1">
</div>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Tambah Peserta</button>
</form>

<?php if($participants->isNotEmpty()): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Siswa</th><th class="border p-2 text-left">Nilai</th>
<th class="border p-2 text-left">Penguji</th><th class="border p-2 text-left">Catatan</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2"><?php echo e($p->student->name ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->score === null ? '-' : number_format((float) $p->score, 2, ',', '.')); ?></td>
<td class="border p-2"><?php echo e($p->examiner() ?? '-'); ?></td>
<td class="border p-2"><?php echo e($p->noteWithoutExaminer() ?? '-'); ?></td>
<td class="border p-2">
<form method="POST" action="/admin/competency-exams/<?php echo e($p->id); ?>" class="inline-flex flex-wrap gap-1 items-center">
<?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<input type="number" name="score" step="0.01" min="0" max="100" value="<?php echo e($p->score); ?>" placeholder="Nilai" class="border p-1 rounded w-24">
<input type="text" name="examiner" value="<?php echo e($p->examiner()); ?>" placeholder="Penguji" class="border p-1 rounded">
<input type="text" name="note" value="<?php echo e($p->noteWithoutExaminer()); ?>" placeholder="Catatan" class="border p-1 rounded">
<button class="bg-blue-600 text-white px-2 py-1 rounded">Simpan Hasil</button>
</form>
<form method="POST" action="/admin/competency-exams/<?php echo e($p->id); ?>" class="inline ml-2" onsubmit="return confirm('Hapus peserta ini dari jadwal?')">
<?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500">Belum ada peserta pada jadwal ini.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/competency_exams/schedule.blade.php ENDPATH**/ ?>