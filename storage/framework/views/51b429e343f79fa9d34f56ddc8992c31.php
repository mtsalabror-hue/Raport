<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Penilaian PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-2">Penilaian PKL per Aspek</h1>
<p class="text-sm mb-4"><a href="/guru/internships" class="text-blue-600 underline">&larr; PKL bimbingan saya</a></p>
<p class="mb-1"><strong><?php echo e($internship->student->name ?? '-'); ?></strong> &middot; <?php echo e($internship->company_name); ?></p>
<p class="text-sm text-gray-600 mb-4"><?php echo e($internship->start_date->format('Y-m-d')); ?> s.d. <?php echo e($internship->end_date->format('Y-m-d')); ?> &middot; Status: <?php echo e(ucfirst($recap['status'])); ?> &middot; Pembimbing: <?php echo e($internship->supervisor ?? '-'); ?> &middot; Grade: <?php echo e($internship->grade ?? '-'); ?></p>
<p class="mb-4">Rata-rata aspek: <strong><?php echo e($recap['average'] === null ? 'Belum ada nilai' : number_format($recap['average'], 2, ',', '.')); ?></strong></p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="POST" action="/guru/internships/<?php echo e($internship->id); ?>/assessments" class="bg-gray-50 border rounded p-4 mb-6">
<?php echo csrf_field(); ?>
<h2 class="font-bold mb-2">Tambah / Perbarui Penilaian Aspek</h2>
<div class="flex flex-wrap gap-2">
<input type="text" name="aspect" required list="aspects" placeholder="Aspek (mis. kedisiplinan, keterampilan, sikap)" class="border p-2 rounded flex-1">
<datalist id="aspects"><option value="kedisiplinan"><option value="keterampilan"><option value="sikap"><option value="kerjasama"><option value="tanggung jawab"></datalist>
<input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai 0-100" class="border p-2 rounded">
<input type="text" name="note" placeholder="Catatan (opsional)" class="border p-2 rounded flex-1">
</div>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Penilaian</button>
</form>

<?php if($internship->assessments->isNotEmpty()): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Aspek</th><th class="border p-2 text-left">Nilai</th>
<th class="border p-2 text-left">Catatan</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $internship->assessments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2"><?php echo e($a->aspect); ?></td>
<td class="border p-2"><?php echo e($a->score === null ? '-' : number_format((float) $a->score, 2, ',', '.')); ?></td>
<td class="border p-2"><?php echo e($a->note ?? '-'); ?></td>
<td class="border p-2">
<form method="POST" action="/guru/internships/assessments/<?php echo e($a->id); ?>" class="inline-flex flex-wrap gap-1 items-center">
<?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<input type="hidden" name="aspect" value="<?php echo e($a->aspect); ?>">
<input type="number" name="score" step="0.01" min="0" max="100" value="<?php echo e($a->score); ?>" class="border p-1 rounded w-24">
<input type="text" name="note" value="<?php echo e($a->note); ?>" placeholder="Catatan" class="border p-1 rounded">
<button class="bg-blue-600 text-white px-2 py-1 rounded">Ubah</button>
</form>
<form method="POST" action="/guru/internships/assessments/<?php echo e($a->id); ?>" class="inline ml-2" onsubmit="return confirm('Hapus penilaian aspek ini?')">
<?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
<button class="text-red-600 underline">Hapus</button>
</form>
</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500">Belum ada penilaian aspek untuk penempatan ini.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/guru/internships/show.blade.php ENDPATH**/ ?>