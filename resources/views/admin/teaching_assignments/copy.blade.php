@extends('layouts.app')

@section('title', 'Salin Mengajar')

@section('content')
<div class="flex flex-col gap-gutter-lg">
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md"><div class="flex flex-col gap-gutter-xs"><h1 class="font-headline text-headline-lg text-on-surface">Salin Mengajar</h1><div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div><p class="font-body text-body-md text-on-surface-muted">Salin seluruh penugasan mengajar dari satu semester ke semester lain — biasanya Ganjil ke Genap dalam tahun ajaran yang sama. Lintas tahun ajaran juga diizinkan. Penugasan yang sudah ada di semester tujuan (guru, mapel, dan kelas yang sama) dilewati, tidak diduplikasi.</p></div></section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><span>{{ session('success') }}</span></div>
@endif

<section class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
<form method="POST" action="{{ route('admin.teaching-copy.store') }}" class="flex flex-col gap-gutter-md max-w-xl">
@csrf
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface">Semester asal</span>
<select name="from_semester_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">— Pilih semester asal —</option>
@foreach($semesters as $s)
<option value="{{ $s->id }}" @selected(old('from_semester_id') == $s->id)>{{ $s->academicYear->name ?? '-' }} — {{ $s->name }}</option>
@endforeach
</select>
</label>
<label class="flex flex-col gap-gutter-xs">
<span class="font-label text-label-lg text-on-surface">Semester tujuan</span>
<select name="to_semester_id" required class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
<option value="">— Pilih semester tujuan —</option>
@foreach($semesters as $s)
<option value="{{ $s->id }}" @selected(old('to_semester_id') == $s->id)>{{ $s->academicYear->name ?? '-' }} — {{ $s->name }}</option>
@endforeach
</select>
</label>
<div class="flex items-center gap-gutter-sm">
<button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover"><span class="material-symbols-outlined" aria-hidden="true">content_copy</span>Salin Penugasan</button>
<a href="{{ route('admin.teaching-assignments.index') }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-surface border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim">Kembali ke Penugasan</a>
</div>
</form>
</section>
</div>
@endsection
