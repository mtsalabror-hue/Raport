<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Rapor - <?php echo e($student->name); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
<h1 class="text-xl font-bold mb-1">Riwayat Rapor</h1>
<p class="text-gray-600 text-sm mb-4"><?php echo e($student->name); ?> (NIS <?php echo e($student->nis); ?>) &middot; <?php echo e($report_card->schoolClass->name ?? '-'); ?> &middot; <?php echo e($report_card->academicYear->name ?? '-'); ?> &middot; <?php echo e($report_card->semester->name ?? '-'); ?> &middot; Status: <?php echo e($report_card->documentLabel()); ?> (<?php echo e($report_card->status); ?>)</p>

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

<h2 class="font-bold mb-2">Riwayat Status (ActivityLog)</h2>
<?php if($history->isNotEmpty()): ?>
<table class="w-full border text-sm mb-6">
<thead>
<tr class="bg-gray-100">
<th class="border px-2 py-2 text-left">Waktu</th>
<th class="border px-2 py-2 text-left">Aksi</th>
<th class="border px-2 py-2 text-left">Oleh</th>
<th class="border px-2 py-2 text-left">Keterangan</th>
</tr>
</thead>
<tbody>
<?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
<td class="border px-2 py-1"><?php echo e($h->created_at->format('d-m-Y H:i')); ?></td>
<td class="border px-2 py-1"><?php echo e($h->action); ?></td>
<td class="border px-2 py-1"><?php echo e($h->user->name ?? '-'); ?></td>
<td class="border px-2 py-1"><?php echo e($h->metadata['reason'] ?? ($h->metadata['note'] ?? '-')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php else: ?>
<p class="text-sm text-gray-500 mb-6">Belum ada riwayat status.</p>
<?php endif; ?>

<div class="mt-4 text-sm flex gap-4">
<a href="/admin/report-cards/<?php echo e($report_card->id); ?>" class="text-blue-600 underline">Kembali ke Detail Pemeriksaan</a>
<a href="/admin/report-cards" class="text-blue-600 underline">Daftar Pemeriksaan</a>
</div>
</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/report_cards/history.blade.php ENDPATH**/ ?>