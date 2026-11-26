@extends('layouts.app')

@section('title', 'Tambah Wali Kelas')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Tambah Wali Kelas</h1>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="{{ route('admin.homeroom-assignments.store') }}" class="flex flex-col gap-gutter-md">
        @csrf
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Guru</label>
            <select name="teacher_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Pilih Guru</option>@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Kelas</label>
            <select name="class_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Pilih Kelas</option>@foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Tahun Ajaran</label>
            <select name="academic_year_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Pilih Tahun Ajaran</option>@foreach($years as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach</select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label class="font-label text-label-md text-on-surface">Semester</label>
            <select name="semester_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary"><option value="">Pilih Semester</option>@foreach($semesters as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
        </div>
        <div>
            <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
