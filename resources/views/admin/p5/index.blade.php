@extends('layouts.app')

@section('title', 'Proyek P5')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Proyek P5</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Projek Penguatan Profil Pelajar Pancasila per kelas dan periode. Satu proyek = satu kelas + satu koordinator.</p></div><div class="flex items-center gap-gutter-sm shrink-0"><a href="{{ route('admin.p5-projects.create') }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tambah Proyek</a></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<form method="GET" action="{{ route('admin.p5-projects.index') }}" class="flex flex-wrap gap-2 mb-6">
<select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Kelas</option>
@foreach($classes as $c)<option value="{{ $c->id }}" @selected(($filters['class_id'] ?? null) == $c->id)>{{ $c->name }}</option>@endforeach
</select>
<select name="academic_year_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Tahun Ajaran</option>
@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? null) == $y->id)>{{ $y->name }}</option>@endforeach
</select>
<select name="semester_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">Semua Semester</option>
@foreach($semesters as $s)<option value="{{ $s->id }}" @selected(($filters['semester_id'] ?? null) == $s->id)>{{ $s->name }}</option>@endforeach
</select>
<button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tampilkan</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden mb-6"><div class="overflow-x-auto"><table class="w-full text-left border-collapse">
<thead class="bg-sidebar">
<tr>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tema</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Dimensi</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Tahun Ajaran</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Semester</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Koordinator</span></th>
<th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant">
@forelse($items as $p)
<tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->tema }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->dimensi }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->schoolClass->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->academicYear->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->semester->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $p->coordinator->name ?? '-' }}</td>
<td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
<div class="flex items-center gap-gutter-sm">
<a href="{{ route('admin.p5-projects.edit', $p) }}" class="inline-flex items-center px-2.5 py-1 rounded bg-amber-100 text-amber-800 font-label text-label-md hover:bg-amber-200">Edit</a>
<form method="POST" action="{{ route('admin.p5-projects.destroy', $p) }}" class="inline">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-sm py-gutter-xs rounded bg-error text-on-error font-label text-label-md">Hapus</button></form>
</div>
</td>
</tr>
@empty
<tr><td colspan="7" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada proyek P5.</td></tr>
@endforelse
</tbody>
</table></div></div>

<div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
