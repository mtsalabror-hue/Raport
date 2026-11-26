<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal UKK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-4">Jadwal Ujian Kompetensi (UKK)</h1>
<p class="text-sm mb-4"><a href="/admin/competency-exams/recap" class="text-blue-600 underline">Rekap Hasil UKK</a></p>
<p class="text-sm text-gray-600 mb-4">Satu jadwal = satu mapel pada satu tanggal ujian. Penguji tidak punya kolom sendiri; catat lewat isian "Penguji" (disimpan sebagai baris "Penguji: ..." di kolom catatan). UKK tidak memakai ambang lulus — rekap hanya menampilkan skor.</p>

<?php if($errors->any()): ?>
<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4">
<ul class="list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>
<?php if(session('success')): ?>
<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<form method="GET" action="/admin/competency-exams" class="flex flex-wrap gap-2 mb-6">
<select name="subject_id" class="border p-2 rounded">
<option value="">Semua Mapel</option>
<?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php if(($filters['subject_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<input type="date" name="exam_date" value="<?php echo e($filters['exam_date'] ?? ''); ?>" class="border p-2 rounded">
<button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
</form>

<form method="POST" action="/admin/competency-exams" class="bg-gray-50 border rounded p-4 mb-6">
<?php echo csrf_field(); ?>
<h2 class="font-bold mb-2">Tambah Peserta ke Jadwal / Buat Jadwal Baru</h2>
<div class="flex flex-wrap gap-2">
<select name="student_id" required class="border p-2 rounded">
<option value="">Pilih Siswa</option>
<?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<select name="subject_id" required class="border p-2 rounded">
<option value="">Pilih Mapel</option>
<?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<input type="date" name="exam_date" required class="border p-2 rounded">
<input type="number" name="score" step="0.01" min="0" max="100" placeholder="Nilai (boleh kosong)" class="border p-2 rounded">
<input type="text" name="examiner" placeholder="Penguji (opsional)" class="border p-2 rounded">
<input type="text" name="note" placeholder="Catatan (opsional)" class="border p-2 rounded flex-1">
</div>
<p class="text-xs text-gray-500 mt-2">Siswa yang sudah terdaftar pada jadwal yang sama tidak diduplikasi — nilainya diperbarui.</p>
<button class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Simpan Peserta</button>
</form>

<?php if($schedules->isNotEmpty()): ?>
<table class="w-full border text-sm">
<thead>
<tr class="bg-gray-100">
<th class="border p-2 text-left">Mapel</th><th class="border p-2 text-left">Tanggal Ujian</th>
<th class="border p-2 text-left">Peserta</th><th class="border p-2 text-left">Sudah Dinilai</th>
<th class="border p-2 text-left">Rata-rata</th><th class="border p-2 text-left">Aksi</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border p-2"><?php echo e($sch['subject']->name ?? '-'); ?></td>
<td class="border p-2"><?php echo e($sch['exam_date'] ?? '-'); ?></td>
<td class="border p-2"><?php echo e($sch['participants']); ?></td>
<td class="border p-2"><?php echo e($sch['scored']); ?></td>
<td class="border p-2"><?php echo e($sch['average'] === null ? '-' : number_format($sch['average'], 2, ',', '.')); ?></td>
<td class="border p-2"><a href="/admin/competency-exams/schedule?subject_id=<?php echo e($sch['subject_id']); ?>&exam_date=<?php echo e($sch['exam_date']); ?>" class="text-blue-600 underline">Peserta &amp; Input Hasil</a></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-gray-500">Belum ada jadwal UKK.</p>
<?php endif; ?>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/competency_exams/index.blade.php ENDPATH**/ ?>