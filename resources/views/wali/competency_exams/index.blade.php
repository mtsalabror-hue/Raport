@extends('layouts.app')

@section('title', 'UKK Kelas Wali')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">UKK Siswa Kelas Wali (Hanya Lihat)</h1><p class="font-body text-body-md text-on-surface-muted"><a href="/wali/internships" class="text-primary hover:underline">PKL kelas wali</a></p><p class="font-body text-body-md text-on-surface-muted">Tidak ada label lulus/tidak lulus — ambang kelulusan belum ditetapkan sekolah, jadi hanya skor yang ditampilkan.</p></div></section>

<form method="GET" action="/wali/competency-exams" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Kelas Wali Saya</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="student_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Siswa</option>
@foreach($allStudents as $s)<option value="{{ $s->id }}" @selected(($filters['student_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<select name="subject_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
<option value="">Semua Mapel</option>
@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(($filters['subject_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<h2 class="font-headline text-headline-md text-on-surface mb-2">Jadwal Ujian</h2>
@if($schedules->isNotEmpty())
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Tanggal Ujian</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Peserta</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Sudah Dinilai</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($schedules as $sch)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['subject']->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['exam_date'] ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['participants'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['scored'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $sch['average'] === null ? '-' : number_format($sch['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted mb-6">Belum ada jadwal UKK untuk siswa kelas wali Anda.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface mb-2">Rekap per Siswa</h2>
@if(count($recaps))
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-surface-dim border-b border-outline-variant">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Per Mapel</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Rata-rata Gabungan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($recaps as $r)
<tr class="hover:bg-surface-dim transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $students[$r['student_id']]->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<ul class="list-disc pl-5">
@foreach($r['per_subject'] as $ps)
<li>{{ $ps['subject']->name ?? '-' }} ({{ $ps['count'] }} ujian) &mdash; rata-rata: {{ $ps['average'] === null ? '-' : number_format($ps['average'], 2, ',', '.') }}</li>
@endforeach
</ul>
</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface align-top">{{ $r['average'] === null ? '-' : number_format($r['average'], 2, ',', '.') }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
@else
<p class="font-body text-body-md text-on-surface-muted">Belum ada data UKK untuk rekap.</p>
@endif
</div>
@endsection
