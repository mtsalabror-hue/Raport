<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rapor - <?php echo e($student->name); ?></title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; margin: 0; }
  h1 { font-size: 16px; text-align: center; margin: 0 0 2px 0; }
  h2 { font-size: 13px; margin: 14px 0 4px 0; border-bottom: 1px solid #000; padding-bottom: 2px; }
  .center { text-align: center; }
  .small { font-size: 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  table.bordered th, table.bordered td { border: 1px solid #000; padding: 4px 6px; }
  table.bordered th { background: #e5e5e5; }
  table.info td { padding: 2px 4px; vertical-align: top; }
  table.sign { margin-top: 28px; }
  table.sign td { text-align: center; vertical-align: top; padding: 2px 4px; }
  .space { height: 48px; }
</style>
</head>
<body>

<h1>RAPOR PESERTA DIDIK</h1>
<p class="center small" style="margin: 0 0 10px 0;"><?php echo e($school_name ?? 'E-Rapor SMK'); ?></p>

<table class="info">
  <tr>
    <td style="width: 18%;">Nama Peserta Didik</td><td style="width: 2%;">:</td><td style="width: 40%;"><?php echo e($student->name); ?></td>
    <td style="width: 14%;">Kelas</td><td style="width: 2%;">:</td><td><?php echo e($report_card->schoolClass->name ?? '-'); ?></td>
  </tr>
  <tr>
    <td>NIS</td><td>:</td><td><?php echo e($student->nis); ?></td>
    <td>Tahun Ajaran</td><td>:</td><td><?php echo e($report_card->academicYear->name ?? '-'); ?></td>
  </tr>
  <tr>
    <td>NISN</td><td>:</td><td><?php echo e($student->nisn); ?></td>
    <td>Semester</td><td>:</td><td><?php echo e($report_card->semester->name ?? '-'); ?></td>
  </tr>
</table>

<h2>Nilai Akhir Mata Pelajaran</h2>
<table class="bordered">
  <thead>
    <tr>
      <th style="width: 6%;">No</th>
      <th style="width: 30%;">Mata Pelajaran</th>
      <th style="width: 12%;">Nilai Akhir</th>
      <th style="width: 52%; text-align: left;">Deskripsi Capaian Pembelajaran</th>
    </tr>
  </thead>
  <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $snapshot; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr>
      <td class="center"><?php echo e($i + 1); ?></td>
      <td><?php echo e($row->subject->name ?? '-'); ?></td>
      <td class="center"><?php echo e($row->final_score !== null ? number_format($row->final_score, 2, ',', '.') : '-'); ?></td>
      <td><?php echo e($row->description ?? '-'); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr><td colspan="4" class="center">Belum ada nilai pada snapshot rapor ini.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<h2>Presensi</h2>
<table class="bordered">
  <thead>
    <tr><th>Sakit</th><th>Izin</th><th>Tanpa Keterangan</th><th>Total Catatan</th><th>Persentase Hadir</th></tr>
  </thead>
  <tbody>
    <tr>
      <td class="center"><?php echo e($attendance['sakit']); ?></td>
      <td class="center"><?php echo e($attendance['izin']); ?></td>
      <td class="center"><?php echo e($attendance['alpa']); ?></td>
      <td class="center"><?php echo e($attendance['total']); ?></td>
      <td class="center"><?php echo e($attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-'); ?></td>
    </tr>
  </tbody>
</table>
<p class="small">Hadir: <?php echo e($attendance['hadir']); ?> hari.</p>

<h2>Catatan Wali Kelas</h2>
<p><?php echo e($homeroom_note->content ?? 'Tidak ada catatan wali kelas.'); ?></p>

<?php if($principal_note): ?>
<h2>Catatan Kepala Sekolah</h2>
<p><?php echo e($principal_note->content); ?></p>
<?php endif; ?>

<h2>Prestasi</h2>
<?php if($achievements->isNotEmpty()): ?>
<table class="bordered">
  <thead><tr><th style="width: 6%;">No</th><th style="text-align: left;">Prestasi</th><th style="width: 25%;">Tingkat</th></tr></thead>
  <tbody>
    <?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr><td class="center"><?php echo e($i + 1); ?></td><td><?php echo e($a->title); ?></td><td class="center"><?php echo e($a->level ?? '-'); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php else: ?>
<p>Tidak ada prestasi pada periode ini.</p>
<?php endif; ?>

<h2>Ekstrakurikuler</h2>
<?php if($extracurriculars->isNotEmpty()): ?>
<table class="bordered">
  <thead><tr><th style="width: 6%;">No</th><th style="text-align: left;">Kegiatan</th><th style="width: 12%;">Nilai</th><th style="text-align: left;">Keterangan</th></tr></thead>
  <tbody>
    <?php $__currentLoopData = $extracurriculars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr><td class="center"><?php echo e($i + 1); ?></td><td><?php echo e($e->extracurricular->name ?? '-'); ?></td><td class="center"><?php echo e($e->score ?? '-'); ?></td><td><?php echo e($e->description ?? '-'); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php else: ?>
<p>Tidak ada ekstrakurikuler pada periode ini.</p>
<?php endif; ?>

<p class="small">Diterbitkan: <?php echo e($report_card->issued_at ? $report_card->issued_at->format('d-m-Y H:i') : '-'); ?><?php if($issued_by_name): ?> oleh <?php echo e($issued_by_name); ?><?php endif; ?>. Status: <?php echo e($report_card->documentLabel()); ?>.</p>

<table class="sign">
  <tr>
    <td style="width: 33%;">
      Orang Tua/Wali<br><br><br><br>
      (...........................)
    </td>
    <td style="width: 33%;">
      Wali Kelas<br><br><br><br>
      <?php echo e($homeroom_teacher_name ? '('.$homeroom_teacher_name.')' : '(...........................)'); ?>

    </td>
    <td style="width: 34%;">
      Kepala Sekolah<br><br><br><br>
      (...........................)
    </td>
  </tr>
</table>

</body>
</html>
<?php /**PATH /home/hatch/workspace/RAPOR/resources/views/admin/report_cards/pdf.blade.php ENDPATH**/ ?>