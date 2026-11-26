<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rapor - {{ $student->name }}</title>
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

@if($print_watermark ?? false)
<div style="position: fixed; top: 45%; left: 0; right: 0; text-align: center; font-size: 48px; font-weight: bold; color: #eeeeee;">{{ strtoupper($school_name ?? 'SMK IT AL-ABROR') }}</div>
@endif

@if($school_logo_path ?? null)
<div class="center"><img src="{{ $school_logo_path }}" style="height: 56px;"></div>
@endif

<h1>RAPOR PESERTA DIDIK</h1>
<p class="center small" style="margin: 0 0 2px 0;">{{ $school_name ?? 'SMK IT AL-ABROR' }}</p>
@if(!empty($school_npsn) || !empty($school_address))
<p class="center small" style="margin: 0 0 10px 0;">{{ trim(($school_npsn ? 'NPSN: '.$school_npsn : '').(($school_npsn && $school_address) ? ' | ' : '').($school_address ?? '')) }}</p>
@else
<p class="center small" style="margin: 0 0 10px 0;">&nbsp;</p>
@endif

<table class="info">
  <tr>
    <td style="width: 18%;">Nama Peserta Didik</td><td style="width: 2%;">:</td><td style="width: 40%;">{{ $student->name }}</td>
    <td style="width: 14%;">Kelas</td><td style="width: 2%;">:</td><td>{{ $report_card->schoolClass->name ?? '-' }}</td>
  </tr>
  <tr>
    <td>NIS</td><td>:</td><td>{{ $student->nis }}</td>
    <td>Tahun Ajaran</td><td>:</td><td>{{ $report_card->academicYear->name ?? '-' }}</td>
  </tr>
  <tr>
    <td>NISN</td><td>:</td><td>{{ $student->nisn }}</td>
    <td>Semester</td><td>:</td><td>{{ $report_card->semester->name ?? '-' }}</td>
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
    @forelse($snapshot as $i => $row)
    <tr>
      <td class="center">{{ $i + 1 }}</td>
      <td>{{ $row->subject->name ?? '-' }}</td>
      <td class="center">{{ $row->final_score !== null ? number_format($row->final_score, 2, ',', '.') : '-' }}</td>
      <td>{{ $row->description ?? '-' }}</td>
    </tr>
    @empty
    <tr><td colspan="4" class="center">Belum ada nilai pada snapshot rapor ini.</td></tr>
    @endforelse
  </tbody>
</table>

<h2>Presensi</h2>
<table class="bordered">
  <thead>
    <tr><th>Sakit</th><th>Izin</th><th>Tanpa Keterangan</th><th>Total Catatan</th><th>Persentase Hadir</th></tr>
  </thead>
  <tbody>
    <tr>
      <td class="center">{{ $attendance['sakit'] }}</td>
      <td class="center">{{ $attendance['izin'] }}</td>
      <td class="center">{{ $attendance['alpa'] }}</td>
      <td class="center">{{ $attendance['total'] }}</td>
      <td class="center">{{ $attendance['persentase_hadir'] !== null ? $attendance['persentase_hadir'].'%' : '-' }}</td>
    </tr>
  </tbody>
</table>
<p class="small">Hadir: {{ $attendance['hadir'] }} hari.</p>

<h2>Catatan Wali Kelas</h2>
<p>{{ $homeroom_note->content ?? 'Tidak ada catatan wali kelas.' }}</p>

@if($principal_note)
<h2>Catatan Kepala Sekolah</h2>
<p>{{ $principal_note->content }}</p>
@endif

<h2>Prestasi</h2>
@if($achievements->isNotEmpty())
<table class="bordered">
  <thead><tr><th style="width: 6%;">No</th><th style="text-align: left;">Prestasi</th><th style="width: 25%;">Tingkat</th></tr></thead>
  <tbody>
    @foreach($achievements as $i => $a)
    <tr><td class="center">{{ $i + 1 }}</td><td>{{ $a->title }}</td><td class="center">{{ $a->level ?? '-' }}</td></tr>
    @endforeach
  </tbody>
</table>
@else
<p>Tidak ada prestasi pada periode ini.</p>
@endif

<h2>Ekstrakurikuler</h2>
@if($extracurriculars->isNotEmpty())
<table class="bordered">
  <thead><tr><th style="width: 6%;">No</th><th style="text-align: left;">Kegiatan</th><th style="width: 12%;">Nilai</th><th style="text-align: left;">Keterangan</th></tr></thead>
  <tbody>
    @foreach($extracurriculars as $i => $e)
    <tr><td class="center">{{ $i + 1 }}</td><td>{{ $e->extracurricular->name ?? '-' }}</td><td class="center">{{ $e->score ?? '-' }}</td><td>{{ $e->description ?? '-' }}</td></tr>
    @endforeach
  </tbody>
</table>
@else
<p>Tidak ada ekstrakurikuler pada periode ini.</p>
@endif

<p class="small">Diterbitkan: {{ $report_card->issued_at ? $report_card->issued_at->format('d-m-Y H:i') : '-' }}@if($issued_by_name) oleh {{ $issued_by_name }}@endif. Status: {{ $report_card->documentLabel() }}.</p>

@if(($print_city ?? null) || ($print_date_formatted ?? null))
<p class="small">{{ $print_city ?? '' }}@if(($print_city ?? null) && ($print_date_formatted ?? null)), @endif{{ $print_date_formatted ?? '' }}</p>
@endif

<table class="sign">
  <tr>
    <td style="width: 33%;">
      Orang Tua/Wali<br><br><br><br>
      (...........................)
    </td>
    <td style="width: 33%;">
      Wali Kelas<br><br><br><br>
      {{ $homeroom_teacher_name ? '('.$homeroom_teacher_name.')' : '(...........................)' }}
    </td>
    <td style="width: 34%;">
      Kepala Sekolah<br><br><br><br>
      @if($principal_name ?? null)
      ({{ $principal_name }})<br>
      <span class="small">NIP. {{ $principal_nip ?? '-' }}</span>
      @else
      (...........................)
      @endif
    </td>
  </tr>
</table>

</body>
</html>
