<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Komponen Penilaian</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Komponen &amp; Bobot Penilaian</h1>

<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<p class="text-sm text-gray-600 mb-4">Total bobot komponen aktif dalam satu cakupan (mapel/kurikulum yang sama, atau global jika mapel dikosongkan) harus tepat 100 sebelum nilai akhir dapat dihitung. KKTP default 75, dapat diubah per komponen.</p>

<form method="POST" action="/guru/components" class="flex flex-wrap gap-2 mb-6">
<?php echo csrf_field(); ?>
<input name="name" placeholder="Nama komponen" class="border p-2 rounded" required>
<select name="type" class="border p-2 rounded">
<option value="formatif">Formatif</option>
<option value="sumatif">Sumatif</option>
<option value="praktik">Praktik</option>
<option value="proyek">Proyek</option>
</select>
<input name="weight" type="number" min="0" max="100" step="0.01" placeholder="Bobot (%)" class="border p-2 rounded w-28" required>
<select name="subject_id" class="border p-2 rounded">
<option value="">Semua mapel (global)</option>
<?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?> (<?php echo e($s->code); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<input name="kktp" type="number" min="0" max="100" step="0.01" placeholder="KKTP (75)" class="border p-2 rounded w-28">
<button class="bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
</form>

<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Nama</th>
<th class="border px-2 py-2">Jenis</th>
<th class="border px-2 py-2">Bobot</th>
<th class="border px-2 py-2">Mapel</th>
<th class="border px-2 py-2">KKTP</th>
<th class="border px-2 py-2">Aktif</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $components; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($c->name); ?></td>
<td class="border px-2 py-1"><?php echo e($c->type); ?></td>
<td class="border px-2 py-1"><?php echo e($c->weight); ?>%</td>
<td class="border px-2 py-1"><?php echo e($c->subject?->name ?? 'Semua mapel'); ?></td>
<td class="border px-2 py-1"><?php echo e($c->kktp); ?></td>
<td class="border px-2 py-1"><?php echo e($c->is_active ? 'Ya' : 'Tidak'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>

<p class="text-sm text-gray-600 mt-4">Total bobot aktif (global): <?php echo e($components->whereNull('subject_id')->where('is_active', true)->sum('weight')); ?>%</p>
<div class="mt-4 text-sm"><a href="/guru/assessments" class="text-blue-600 underline">Kembali ke Input Nilai</a></div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/teacher/assessments/components.blade.php ENDPATH**/ ?>