<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Preview Rapor - <?php echo e($student->name); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Preview Rapor</h1>
<p class="text-gray-600 text-sm mb-4">Pratinjau hanya-baca untuk wali kelas. Nilai tidak dapat diubah dari halaman ini.</p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<div class="bg-gray-50 border rounded p-4 mb-6 text-sm">
<p><strong>Nama:</strong> <?php echo e($student->name); ?></p>
<p><strong>NIS:</strong> <?php echo e($student->nis); ?> &middot; <strong>NISN:</strong> <?php echo e($student->nisn); ?></p>
<p><strong>Kelas:</strong> <?php echo e($student->class_id); ?> &middot; <strong>Status rapor:</strong> <?php echo e($reportCard->status ?? 'draft (belum ada rapor)'); ?></p>
<p><strong>Kelengkapan:</strong> <?php echo e($check['complete'] ? 'Lengkap' : 'Belum lengkap ('.count($check['missing']).' kekurangan)'); ?></p>
<?php if(!$check['complete']): ?>
<ul class="list-disc pl-5 mt-2">
<?php $__currentLoopData = $check['missing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a class="text-blue-600 underline" href="<?php echo e($m['url']); ?>"><?php echo e($m['message']); ?></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php endif; ?>
</div>

<h2 class="font-bold mb-2">Nilai Akhir Mata Pelajaran</h2>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Mata Pelajaran</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2">KKTP</th>
<th class="border px-2 py-2">Ketuntasan</th>
<th class="border px-2 py-2 text-left">Deskripsi Capaian</th>
</tr>
</thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $finals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
<td class="border px-2 py-1"><?php echo e($row['subject']->name); ?></td>
<td class="border px-2 py-1"><?php echo e($row['final_score'] !== null ? number_format($row['final_score'], 2, ',', '.') : 'belum terkirim'); ?></td>
<td class="border px-2 py-1"><?php echo e($row['kktp']); ?></td>
<td class="border px-2 py-1"><?php echo e($row['passed'] === null ? '-' : ($row['passed'] ? 'Tuntas' : 'Belum tuntas')); ?></td>
<td class="border px-2 py-1"><?php echo e($row['description'] ?? 'belum ada deskripsi'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr><td colspan="5" class="border px-2 py-1 text-gray-500">Belum ada mata pelajaran yang diajarkan di kelas ini pada periode ini.</td></tr>
<?php endif; ?>
</tbody>
</table>

<h2 class="font-bold mb-2">Presensi</h2>
<p class="text-sm mb-6">Hadir <?php echo e($attendance['hadir']); ?> &middot; Sakit <?php echo e($attendance['sakit']); ?> &middot; Izin <?php echo e($attendance['izin']); ?> &middot; Tanpa keterangan <?php echo e($attendance['alpa']); ?> &middot; Total <?php echo e($attendance['total']); ?> catatan &middot; Persentase hadir <?php echo e($attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-'); ?></p>

<h2 class="font-bold mb-2">Prestasi</h2>
<?php if($achievements->isNotEmpty()): ?>
<ul class="list-disc pl-5 text-sm mb-6">
<?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><strong><?php echo e($a->title); ?></strong><?php if($a->level): ?> (<?php echo e($a->level); ?>)<?php endif; ?> <?php if($a->description): ?> - <?php echo e($a->description); ?><?php endif; ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php else: ?>
<p class="text-sm text-gray-500 mb-6">Belum ada prestasi pada periode ini.</p>
<?php endif; ?>

<h2 class="font-bold mb-2">Ekstrakurikuler</h2>
<?php if($extracurriculars->isNotEmpty()): ?>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Kegiatan</th>
<th class="border px-2 py-2">Nilai</th>
<th class="border px-2 py-2 text-left">Keterangan</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $extracurriculars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($e->extracurricular->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($e->score ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($e->description ?? '-'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-sm text-gray-500 mb-6">Belum ada ekstrakurikuler pada periode ini.</p>
<?php endif; ?>

<h2 class="font-bold mb-2">Catatan Wali Kelas</h2>
<p class="text-sm mb-6"><?php echo e($homeroomNote->content ?? 'Belum ada catatan wali kelas.'); ?></p>

<?php if($check['complete'] && ($reportCard->status ?? 'draft') === 'draft'): ?>
<form method="POST" action="/wali/submit">
<?php echo csrf_field(); ?>
<input type="hidden" name="student_id" value="<?php echo e($student->id); ?>">
<input type="hidden" name="class_id" value="<?php echo e($student->class_id); ?>">
<input type="hidden" name="academic_year_id" value="<?php echo e($filters['academic_year_id']); ?>">
<input type="hidden" name="semester_id" value="<?php echo e($filters['semester_id']); ?>">
<button class="bg-green-600 text-white px-4 py-2 rounded">Ajukan Rapor</button>
</form>
<?php elseif(($reportCard->status ?? null) === 'diajukan'): ?>
<p class="text-sm text-green-700">Rapor ini sudah diajukan dan menunggu pemeriksaan.</p>
<?php endif; ?>

<div class="mt-4 text-sm flex gap-4">
<a href="/wali/completeness?class_id=<?php echo e($student->class_id); ?>&academic_year_id=<?php echo e($filters['academic_year_id']); ?>&semester_id=<?php echo e($filters['semester_id']); ?>" class="text-blue-600 underline">Monitoring Kelengkapan</a>
<a href="/wali/history/<?php echo e($student->id); ?>" class="text-blue-600 underline">Riwayat Akademik</a>
<a href="/wali/dashboard" class="text-blue-600 underline">Dashboard</a>
</div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/wali/preview.blade.php ENDPATH**/ ?>