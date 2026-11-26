<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ubah Penempatan PKL</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Ubah Penempatan PKL</h1>
<p class="text-sm mb-4"><a href="/admin/internships" class="text-blue-600 underline">&larr; Kembali ke daftar penempatan</a> &middot; <a href="/admin/internships/<?php echo e($internship->id); ?>/assessments" class="text-blue-600 underline">Penilaian per aspek</a></p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="POST" action="/admin/internships/<?php echo e($internship->id); ?>" class="bg-gray-50 border rounded p-4 max-w-2xl">
<?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
<div class="flex flex-col gap-3">
<label class="text-sm">Siswa
<select name="student_id" required class="border p-2 rounded w-full mt-1">
<?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if($internship->student_id == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select></label>
<label class="text-sm">Perusahaan / mitra industri
<input type="text" name="company_name" required list="companies" value="<?php echo e($internship->company_name); ?>" class="border p-2 rounded w-full mt-1">
<datalist id="companies"><?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></datalist></label>
<label class="text-sm">Tanggal mulai
<input type="date" name="start_date" required value="<?php echo e($internship->start_date->format('Y-m-d')); ?>" class="border p-2 rounded w-full mt-1"></label>
<label class="text-sm">Tanggal selesai
<input type="date" name="end_date" required value="<?php echo e($internship->end_date->format('Y-m-d')); ?>" class="border p-2 rounded w-full mt-1"></label>
<label class="text-sm">Pembimbing (nama guru)
<input type="text" name="supervisor" value="<?php echo e($internship->supervisor); ?>" class="border p-2 rounded w-full mt-1"></label>
<label class="text-sm">Nilai akhir / grade (opsional)
<input type="text" name="grade" value="<?php echo e($internship->grade); ?>" class="border p-2 rounded w-full mt-1"></label>
</div>
<p class="text-xs text-gray-500 mt-2">Periode baru tidak boleh beririsan dengan penempatan lain siswa yang sama. Status saat ini: <?php echo e(ucfirst($internship->status())); ?>.</p>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-3">Simpan Perubahan</button>
</form>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/internships/edit.blade.php ENDPATH**/ ?>