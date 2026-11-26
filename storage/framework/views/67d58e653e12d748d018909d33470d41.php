<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Periksa Rapor - <?php echo e($student->name); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Periksa Rapor</h1>
<p class="text-gray-600 text-sm mb-4">Pemeriksaan admin: hasil validasi kelengkapan, nilai, presensi, prestasi, ekstrakurikuler, dan catatan. Persetujuan hanya boleh dilakukan admin.</p>

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
<p><strong>Kelas:</strong> <?php echo e($report_card->schoolClass->name ?? '-'); ?> &middot; <strong>Tahun Ajaran:</strong> <?php echo e($report_card->academicYear->name ?? '-'); ?> &middot; <strong>Semester:</strong> <?php echo e($report_card->semester->name ?? '-'); ?></p>
<p><strong>Status rapor:</strong> <?php echo e($document_label); ?> (<?php echo e($report_card->status); ?>)</p>
<p><strong>Kelengkapan:</strong> <?php echo e($check['complete'] ? 'Lengkap' : 'Belum lengkap ('.count($check['missing']).' kekurangan)'); ?></p>
<?php if(!$check['complete']): ?>
<ul class="list-disc pl-5 mt-2">
<?php $__currentLoopData = $check['missing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($m['message']); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php endif; ?>
<?php if($report_card->issued_at): ?><p><strong>Diterbitkan:</strong> <?php echo e($report_card->issued_at->format('d-m-Y H:i')); ?> oleh <?php echo e($report_card->issuer->name ?? '-'); ?></p><?php endif; ?>
<?php if($report_card->locked_at): ?><p><strong>Dikunci:</strong> <?php echo e($report_card->locked_at->format('d-m-Y H:i')); ?></p><?php endif; ?>
</div>

<h2 class="font-bold mb-2">Nilai Akhir Mata Pelajaran (data live)</h2>
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

<?php if($snapshot->isNotEmpty()): ?>
<h2 class="font-bold mb-2">Snapshot Saat Penerbitan (tidak berubah bila data live berubah)</h2>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Mata Pelajaran</th>
<th class="border px-2 py-2">Nilai Akhir</th>
<th class="border px-2 py-2 text-left">Deskripsi Capaian</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $snapshot; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($row->subject->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($row->final_score !== null ? number_format($row->final_score, 2, ',', '.') : '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($row->description ?? '-'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php endif; ?>

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
<p class="text-sm mb-6"><?php echo e($homeroom_note->content ?? 'Belum ada catatan wali kelas.'); ?></p>

<h2 class="font-bold mb-2">Riwayat Persetujuan</h2>
<?php if($approvals->isNotEmpty()): ?>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Pemeriksa</th>
<th class="border px-2 py-2 text-left">Keputusan</th>
<th class="border px-2 py-2 text-left">Alasan / Catatan</th>
<th class="border px-2 py-2 text-left">Waktu</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($ap->approver->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($ap->status === 'approved' ? 'Disetujui' : ($ap->status === 'rejected' ? 'Dikembalikan (Perlu Perbaikan)' : 'Menunggu')); ?></td>
<td class="border px-2 py-1"><?php echo e($ap->note ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($ap->created_at->format('d-m-Y H:i')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-sm text-gray-500 mb-6">Belum ada keputusan persetujuan.</p>
<?php endif; ?>

<h2 class="font-bold mb-2">Riwayat Status</h2>
<?php if($history->isNotEmpty()): ?>
<ul class="list-disc pl-5 text-sm mb-6">
<?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<li><?php echo e($h->created_at->format('d-m-Y H:i')); ?> — <?php echo e($h->action); ?> <?php if($h->user): ?> (<?php echo e($h->user->name); ?>)<?php endif; ?></li>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php else: ?>
<p class="text-sm text-gray-500 mb-6">Belum ada riwayat status.</p>
<?php endif; ?>

<div class="flex flex-wrap gap-2 mb-6">
<?php if($report_card->status === 'diajukan'): ?>
<form method="POST" action="/admin/report-cards/<?php echo e($report_card->id); ?>/approve">
<?php echo csrf_field(); ?>
<button class="bg-green-600 text-white px-4 py-2 rounded">Setujui Rapor</button>
</form>
<form method="POST" action="/admin/report-cards/<?php echo e($report_card->id); ?>/reject" class="flex gap-2">
<?php echo csrf_field(); ?>
<input type="text" name="note" placeholder="Alasan pengembalian (wajib)" required class="border rounded px-2 py-2 w-80">
<button class="bg-red-600 text-white px-4 py-2 rounded">Kembalikan</button>
</form>
<?php elseif($report_card->status === 'divalidasi'): ?>
<form method="POST" action="/admin/report-cards/<?php echo e($report_card->id); ?>/publish">
<?php echo csrf_field(); ?>
<button class="bg-green-600 text-white px-4 py-2 rounded">Terbitkan Rapor</button>
</form>
<?php elseif($report_card->status === 'diterbitkan'): ?>
<form method="POST" action="/admin/report-cards/<?php echo e($report_card->id); ?>/lock">
<?php echo csrf_field(); ?>
<button class="bg-gray-800 text-white px-4 py-2 rounded">Kunci Rapor</button>
</form>
<a href="/admin/report-cards/<?php echo e($report_card->id); ?>/pdf" class="bg-blue-600 text-white px-4 py-2 rounded">Unduh PDF</a>
<?php elseif($report_card->status === 'dikunci'): ?>
<a href="/admin/report-cards/<?php echo e($report_card->id); ?>/pdf" class="bg-blue-600 text-white px-4 py-2 rounded">Unduh PDF</a>
<p class="text-sm text-gray-600 w-full">Rapor ini sudah terkunci dan tidak dapat diubah lagi.</p>
<?php else: ?>
<p class="text-sm text-gray-600">Rapor ini masih berstatus draft (<?php echo e($document_label); ?>) dan belum diajukan wali kelas.</p>
<?php endif; ?>
</div>

<div class="mt-4 text-sm flex gap-4">
<a href="/admin/report-cards/<?php echo e($report_card->id); ?>/history" class="text-blue-600 underline">Riwayat Lengkap</a>
<a href="/admin/report-cards" class="text-blue-600 underline">Daftar Pemeriksaan</a>
<a href="/admin/dashboard" class="text-blue-600 underline">Dashboard Admin</a>
</div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/report_cards/show.blade.php ENDPATH**/ ?>