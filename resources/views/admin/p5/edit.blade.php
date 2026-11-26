@extends('layouts.app')

@section('title', 'Edit Proyek P5')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Edit Proyek P5</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
    </div>
</section>

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md">
<span class="material-symbols-outlined" aria-hidden="true">error</span>
<ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="{{ route('admin.p5-projects.update', $project) }}" class="flex flex-col gap-gutter-md">
        @csrf
        @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label for="tema" class="font-label text-label-md text-on-surface">Tema</label>
            <input id="tema" name="tema" value="{{ old('tema', $project->tema) }}" placeholder="Tema proyek P5" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="dimensi" class="font-label text-label-md text-on-surface">Dimensi Profil Pelajar Pancasila</label>
            <select id="dimensi" name="dimensi" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <option value="">Pilih Dimensi</option>
                @foreach($dimensiOptions as $d)<option value="{{ $d }}" @selected(old('dimensi', $project->dimensi) === $d)>{{ $d }}</option>@endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="class_id" class="font-label text-label-md text-on-surface">Kelas</label>
            <select id="class_id" name="class_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <option value="">Pilih Kelas</option>
                @foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('class_id', $project->class_id) == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="academic_year_id" class="font-label text-label-md text-on-surface">Tahun Ajaran</label>
            <select id="academic_year_id" name="academic_year_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <option value="">Pilih Tahun Ajaran</option>
                @foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(old('academic_year_id', $project->academic_year_id) == $y->id)>{{ $y->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="semester_id" class="font-label text-label-md text-on-surface">Semester</label>
            <select id="semester_id" name="semester_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <option value="">Pilih Semester</option>
                @foreach($semesters as $s)<option value="{{ $s->id }}" @selected(old('semester_id', $project->semester_id) == $s->id)>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="coordinator_id" class="font-label text-label-md text-on-surface">Koordinator (Guru)</label>
            <select id="coordinator_id" name="coordinator_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                <option value="">Tanpa Koordinator</option>
                @foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('coordinator_id', $project->coordinator_id) == $t->id)>{{ $t->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex items-center gap-gutter-sm">
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
            <a href="{{ route('admin.p5-projects.index') }}" class="inline-flex items-center px-gutter-md py-gutter-sm rounded bg-white border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim">Batal</a>
        </div>
    </form>
</div>
@endsection
