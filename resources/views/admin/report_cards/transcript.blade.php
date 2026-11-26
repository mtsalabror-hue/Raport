<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Transkrip Nilai - {{ $student->name }}</title>
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
</style>
</head>
<body>

@if($school_logo_path ?? null)
<div class="center"><img src="{{ $school_logo_path }}" style="height: 56px;"></div>
@endif

<h1>TRANSKRIP NILAI PESERTA DIDIK</h1>
<p class="center small" style="margin: 0 0 2px 0;">{{ $school_name ?? 'SMK IT AL-ABROR' }}</p>
@if(!empty($school_npsn) || !empty($school_address))
<p class="center small" style="margin: 0 0 10px 0;">{{ trim(($school_npsn ? 'NPSN: '.$school_npsn : '').(($school_npsn && $school_address) ? ' | ' : '').($school_address ?? '')) }}</p>
@else
<p class="center small" style="margin: 0 0 10px 0;">&nbsp;</p>
@endif

<table class="info">
  <tr>
    <td style="width: 18%;">Nama Peserta Didik</td><td style="width: 2%;">:</td><td style="width: 40%;">{{ $student->name }}</td>
    <td style="width: 14%;">Kelas</td><td style="width: 2%;">:</td><td>{{ $student->class_id ? ($cards->first()?->schoolClass->name ?? '-') : '-' }}</td>
  </tr>
  <tr>
    <td>NIS</td><td>:</td><td>{{ $student->nis }}</td>
    <td>NISN</td><td>:</td><td>{{ $student->nisn ?? '-' }}</td>
  </tr>
</table>

<p class="small">Nilai di bawah ini diambil dari snapshot rapor yang sudah diterbitkan (report_card_subjects). Semester yang rapornya belum diterbitkan tidak muncul di transkrip ini.</p>

@forelse($cards as $card)
<h2>{{ $card->academicYear->name ?? '-' }} — Semester {{ $card->semester->name ?? '-' }} ({{ $card->schoolClass->name ?? '-' }})</h2>
<table class="bordered">
  <thead>
    <tr>
      <th style="width: 6%;">No</th>
      <th style="width: 40%; text-align: left;">Mata Pelajaran</th>
      <th style="width: 14%;">Nilai Akhir</th>
      <th style="text-align: left;">Deskripsi Capaian Pembelajaran</th>
    </tr>
  </thead>
  <tbody>
    @forelse($card->reportCardSubjects as $i => $row)
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
@empty
<p>Tidak ada rapor yang sudah diterbitkan untuk siswa ini, sehingga transkrip masih kosong.</p>
@endforelse

@if(($print_city ?? null) || ($print_date_formatted ?? null))
<p class="small">{{ $print_city ?? '' }}@if(($print_city ?? null) && ($print_date_formatted ?? null)), @endif{{ $print_date_formatted ?? '' }}</p>
@endif

<table class="sign">
  <tr>
    <td style="width: 50%;">
      Wali Kelas<br><br><br><br>
      (...........................)
    </td>
    <td style="width: 50%;">
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
