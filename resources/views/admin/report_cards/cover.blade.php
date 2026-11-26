<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Sampul Rapor - {{ $student->name }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; margin: 0; }
  .center { text-align: center; }
  .small { font-size: 11px; }
  table { width: 100%; border-collapse: collapse; }
  table.info td { padding: 4px 6px; vertical-align: top; }
  .cover-title { font-size: 36px; font-weight: bold; letter-spacing: 6px; margin: 60px 0 8px 0; }
  .cover-sub { font-size: 16px; margin: 0 0 60px 0; }
  .spacer { height: 40px; }
</style>
</head>
<body>

@if($print_watermark ?? false)
<div style="position: fixed; top: 45%; left: 0; right: 0; text-align: center; font-size: 48px; font-weight: bold; color: #eeeeee;">{{ strtoupper($school_name ?? 'SMK IT AL-ABROR') }}</div>
@endif

<div class="center" style="margin-top: 70px;">
  @if($school_logo_path ?? null)
  <img src="{{ $school_logo_path }}" style="height: 90px;">
  @endif
  <p style="font-size: 18px; font-weight: bold; margin: 16px 0 2px 0;">{{ $school_name ?? 'SMK IT AL-ABROR' }}</p>
  @if(!empty($school_npsn) || !empty($school_address))
  <p class="small" style="margin: 0;">{{ trim(($school_npsn ? 'NPSN: '.$school_npsn : '').(($school_npsn && $school_address) ? ' | ' : '').($school_address ?? '')) }}</p>
  @endif
</div>

<p class="center cover-title">RAPOR</p>
<p class="center cover-sub">PESERTA DIDIK</p>

<table class="info" style="width: 70%; margin: 0 auto;">
  <tr>
    <td style="width: 32%;">Nama Peserta Didik</td><td style="width: 4%;">:</td><td><strong>{{ $student->name }}</strong></td>
  </tr>
  <tr>
    <td>NIS</td><td>:</td><td>{{ $student->nis }}</td>
  </tr>
  <tr>
    <td>NISN</td><td>:</td><td>{{ $student->nisn ?? '-' }}</td>
  </tr>
  <tr>
    <td>Kelas</td><td>:</td><td>{{ $report_card->schoolClass->name ?? '-' }}</td>
  </tr>
  <tr>
    <td>Tahun Ajaran</td><td>:</td><td>{{ $report_card->academicYear->name ?? '-' }}</td>
  </tr>
  <tr>
    <td>Semester</td><td>:</td><td>{{ $report_card->semester->name ?? '-' }}</td>
  </tr>
</table>

<div class="spacer"></div>

@if(($print_city ?? null) || ($print_date_formatted ?? null))
<p class="center small">{{ $print_city ?? '' }}@if(($print_city ?? null) && ($print_date_formatted ?? null)), @endif{{ $print_date_formatted ?? '' }}</p>
@endif

</body>
</html>
