@extends('layouts.app')

@section('title', 'Edit Penugasan Guru')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Edit Penugasan Guru</h1>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="{{ route('admin.teaching-assignments.update',$assignment) }}" class="flex flex-col gap-gutter-md">
        @csrf @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Guru</label>
            <select name="teacher_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">@foreach($teachers as $t)<option value="{{ $t->id }}" {{ $assignment->teacher_id==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Mapel</label>
            <select name="subject_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">@foreach($subjects as $s)<option value="{{ $s->id }}" {{ $assignment->subject_id==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Kelas</label>
            <select name="class_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">@foreach($classes as $c)<option value="{{ $c->id }}" {{ $assignment->class_id==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Tahun Ajaran</label>
            <select name="academic_year_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">@foreach($years as $y)<option value="{{ $y->id }}" {{ $assignment->academic_year_id==$y->id?'selected':'' }}>{{ $y->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Semester</label>
            <select name="semester_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">@foreach($semesters as $s)<option value="{{ $s->id }}" {{ $assignment->semester_id==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Jam</label>
            <input name="hours" type="number" min="0" max="40" value="{{ $assignment->hours }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div>
            <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
