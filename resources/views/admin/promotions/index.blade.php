@extends('layouts.app')

@section('title', 'Naik Kelas')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Naik Kelas</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Proses akhir tahun ajaran: luluskan kelas XII dulu, baru naikkan kelas di bawahnya. Hanya siswa yang diputuskan "Naik Kelas" oleh wali kelas yang pindah atau lulus; siswa yang diputuskan "Tidak Naik Kelas" atau belum diputuskan tetap di kelas asal.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="{{ route('admin.promotions.index') }}" class="flex flex-wrap gap-2 mb-6">
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan</button>
</form>

@if(!$isEven)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">info</span><span>Periode yang dipilih adalah semester {{ $semester->name ?? '-' }}. Kenaikan kelas dan kelulusan hanya dapat diproses pada semester Genap.</span></div>
@endif

@if($isEven && $pendingGraduation > 0)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">warning</span><span>Masih ada {{ $pendingGraduation }} siswa kelas XII yang diputuskan naik tetapi belum diluluskan. Selesaikan Langkah 1 (Luluskan Kelas XII) dulu — kenaikan kelas akan ditolak selama masih ada yang tertunda.</span></div>
@endif

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Langkah 1 — Luluskan Kelas XII</h2>
@if(empty($xiiStats))
<p class="font-body text-body-md text-on-surface-muted">Tidak ada kelas XII pada tahun ajaran ini.</p>
@else
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas XII</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Diputuskan Naik</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tetap di XII</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($xiiStats as $row)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['class']->name }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['total'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['decided_naik'] }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['total'] - $row['decided_naik'] }}</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<form method="POST" action="{{ route('admin.promotions.graduate') }}" onsubmit="return confirm('Luluskan semua siswa kelas XII yang diputuskan naik menjadi alumni? Siswa yang tidak naik atau belum diputuskan tetap di kelas XII.');">
@csrf
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<button @disabled(!$isEven) class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-brand-emerald text-white font-label text-label-lg hover:opacity-90 disabled:bg-surface-container-high disabled:text-on-surface-muted">Luluskan Kelas XII</button>
</form>
<p class="font-body text-body-sm text-on-surface-muted">Siswa yang lulus menjadi alumni (kelas dikosongkan, tahun lulus tercatat) dan datanya tetap tersimpan; siswa yang tidak naik tetap di kelas XII.</p>
@endif

<h2 class="font-headline text-headline-md text-on-surface border-l-4 border-primary pl-3">Langkah 2 — Naikkan Kelas</h2>
@php $sourceClasses = collect($classesByLevel['X'] ?? [])->merge($classesByLevel['XI'] ?? []); @endphp
@if($sourceClasses->isEmpty())
<p class="font-body text-body-md text-on-surface-muted">Tidak ada kelas X atau XI pada tahun ajaran ini.</p>
@else
<form method="POST" action="{{ route('admin.promotions.promote') }}" onsubmit="return confirm('Proses kenaikan kelas untuk pemetaan yang dipilih? Hanya siswa yang diputuskan naik yang pindah kelas.');">
@csrf
<input type="hidden" name="academic_year_id" value="{{ $filters['academic_year_id'] }}">
<input type="hidden" name="semester_id" value="{{ $filters['semester_id'] }}">
<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas Asal</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Keputusan Wali</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas Tujuan</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@foreach($sourceClasses as $i => $class)
@php
  $nextLevel = $class->level === 'X' ? 'XI' : 'XII';
  $targets = $classesByLevel[$nextLevel] ?? collect();
  $summary = $decisionSummary[$class->id] ?? ['total' => 0, 'naik' => 0, 'tidak_naik' => 0, 'belum' => 0];
@endphp
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<input type="hidden" name="mappings[{{ $i }}][from_class_id]" value="{{ $class->id }}">
{{ $class->name }}<br><span class="text-on-surface-muted text-body-sm">{{ $summary['total'] }} siswa</span>
</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $summary['naik'] }} naik &middot; {{ $summary['tidak_naik'] }} tidak naik &middot; {{ $summary['belum'] }} belum ditetapkan</td>
<td class="px-gutter-md py-gutter-md">
<select name="mappings[{{ $i }}][to_class_id]" @disabled(!$isEven) class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30 w-full disabled:bg-surface-dim disabled:text-on-surface-muted">
<option value="">— Tidak dipindahkan —</option>
@foreach($targets as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
</select>
</td>
</tr>
@endforeach
</tbody>
</table></div></div>
<button @disabled(!$isEven || $pendingGraduation > 0) class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover disabled:bg-surface-container-high disabled:text-on-surface-muted">Proses Kenaikan Kelas</button>
@if($pendingGraduation > 0)
<p class="font-body text-body-sm text-amber-800">Tombol tidak aktif: selesaikan dulu Langkah 1 (Luluskan Kelas XII).</p>
@endif
</form>
@endif
</div>
@endsection
