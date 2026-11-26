<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Identitas Rapor - {{ $student->name }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; margin: 0; }
  h1 { font-size: 16px; text-align: center; margin: 0 0 2px 0; }
  h2 { font-size: 13px; margin: 14px 0 4px 0; border-bottom: 1px solid #000; padding-bottom: 2px; }
  .center { text-align: center; }
  .small { font-size: 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  table.info td { padding: 2px 4px; vertical-align: top; }
</style>
</head>
<body>

@if($school_logo_path ?? null)
<div class="center"><img src="{{ $school_logo_path }}" style="height: 56px;"></div>
@endif

<h1>IDENTITAS PESERTA DIDIK</h1>
<p class="center small" style="margin: 0 0 2px 0;">{{ $school_name ?? 'SMK IT AL-ABROR' }}</p>
@if(!empty($school_npsn) || !empty($school_address))
<p class="center small" style="margin: 0 0 10px 0;">{{ trim(($school_npsn ? 'NPSN: '.$school_npsn : '').(($school_npsn && $school_address) ? ' | ' : '').($school_address ?? '')) }}</p>
@else
<p class="center small" style="margin: 0 0 10px 0;">&nbsp;</p>
@endif

<h2>Data Peserta Didik</h2>
<table class="info">
  <tr><td style="width: 22%;">Nama Peserta Didik</td><td style="width: 2%;">:</td><td>{{ $student->name }}</td></tr>
  <tr><td>NIS</td><td>:</td><td>{{ $student->nis }}</td></tr>
  <tr><td>NISN</td><td>:</td><td>{{ $student->nisn ?? '-' }}</td></tr>
  <tr><td>Jenis Kelamin</td><td>:</td><td>{{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }}</td></tr>
  <tr><td>Tanggal Lahir</td><td>:</td><td>{{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->locale('id')->isoFormat('D MMMM Y') : '-' }}</td></tr>
  <tr><td>Alamat</td><td>:</td><td>{{ $student->address ?? '-' }}</td></tr>
  <tr><td>Kelas</td><td>:</td><td>{{ $report_card->schoolClass->name ?? '-' }}</td></tr>
  <tr><td>Tahun Ajaran</td><td>:</td><td>{{ $report_card->academicYear->name ?? '-' }}</td></tr>
  <tr><td>Semester</td><td>:</td><td>{{ $report_card->semester->name ?? '-' }}</td></tr>
</table>

<h2>Data Sekolah</h2>
<table class="info">
  <tr><td style="width: 22%;">Nama Sekolah</td><td style="width: 2%;">:</td><td>{{ $school_name ?? 'SMK IT AL-ABROR' }}</td></tr>
  <tr><td>NPSN</td><td>:</td><td>{{ $school_npsn ?? '-' }}</td></tr>
  <tr><td>Alamat Sekolah</td><td>:</td><td>{{ $school_address ?? '-' }}</td></tr>
  <tr><td>Kepala Sekolah</td><td>:</td><td>{{ $principal_name ?? '-' }}@if($principal_nip ?? null) (NIP. {{ $principal_nip }})@endif</td></tr>
  <tr><td>Wali Kelas</td><td>:</td><td>{{ $homeroom_teacher_name ?? '-' }}</td></tr>
</table>

@if(($print_city ?? null) || ($print_date_formatted ?? null))
<p class="small">{{ $print_city ?? '' }}@if(($print_city ?? null) && ($print_date_formatted ?? null)), @endif{{ $print_date_formatted ?? '' }}</p>
@endif

</body>
</html>
