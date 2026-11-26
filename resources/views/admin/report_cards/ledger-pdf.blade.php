<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Leger - {{ $class->name }} - {{ $period_label }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; margin: 0; }
  h1 { font-size: 15px; text-align: center; margin: 0 0 2px 0; }
  .center { text-align: center; }
  .small { font-size: 9px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  table.bordered th, table.bordered td { border: 1px solid #000; padding: 3px 5px; }
  table.bordered th { background: #e5e5e5; }
  table.info td { padding: 2px 4px; vertical-align: top; }
</style>
</head>
<body>

<h1>LEGER NILAI AKHIR</h1>
<p class="center small" style="margin: 0 0 2px 0;">{{ $school_name ?? 'SMK IT AL-ABROR' }}</p>
@if(!empty($school_npsn) || !empty($school_address))
<p class="center small" style="margin: 0 0 10px 0;">{{ trim(($school_npsn ? 'NPSN: '.$school_npsn : '').(($school_npsn && $school_address) ? ' | ' : '').($school_address ?? '')) }}</p>
@else
<p class="center small" style="margin: 0 0 10px 0;">&nbsp;</p>
@endif

<table class="info">
  <tr>
    <td style="width: 14%;">Kelas</td><td style="width: 2%;">:</td><td>{{ $class->name }}</td>
    <td style="width: 14%;">Periode</td><td style="width: 2%;">:</td><td>{{ $period_label }}</td>
  </tr>
  <tr>
    <td>Jumlah Siswa</td><td>:</td><td>{{ $student_count }} siswa</td>
    <td>Sumber Nilai</td><td>:</td><td>{{ $source_label }}</td>
  </tr>
</table>

<table class="bordered">
  <thead>
    <tr>
      <th style="width: 4%;">No</th>
      <th style="width: 8%;">NIS</th>
      <th style="text-align: left;">Nama Siswa</th>
      @foreach($subjects as $subject)
      <th>{{ $subject->name }}</th>
      @endforeach
      <th>Rata-rata</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $i => $row)
    <tr>
      <td class="center">{{ $i + 1 }}</td>
      <td class="center">{{ $row['student']->nis }}</td>
      <td>{{ $row['student']->name }}</td>
      @foreach($subjects as $subject)
      <td class="center">{{ $row['scores'][$subject->id] !== null ? number_format($row['scores'][$subject->id], 2, ',', '.') : '-' }}</td>
      @endforeach
      <td class="center">{{ $row['average'] !== null ? number_format($row['average'], 2, ',', '.') : '-' }}</td>
    </tr>
    @empty
    <tr><td colspan="{{ 4 + $subjects->count() }}" class="center">Belum ada siswa di kelas ini.</td></tr>
    @endforelse
  </tbody>
</table>

<p class="small">Sumber nilai: {{ $source_label }}.</p>

</body>
</html>
