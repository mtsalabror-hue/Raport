@extends('layouts.app')

@section('title', 'Edit Kelas')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Edit Kelas</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/classes/{{ $class->id }}" class="flex flex-col gap-gutter-md">
        @csrf @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label for="department_id" class="font-label text-label-md text-on-surface">Jurusan</label>
            <select id="department_id" name="department_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                @foreach($deps as $d)
                <option value="{{ $d->id }}" {{ $class->department_id==$d->id?'selected':'' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="academic_year_id" class="font-label text-label-md text-on-surface">Tahun Ajaran</label>
            <select id="academic_year_id" name="academic_year_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
                @foreach($years as $y)
                <option value="{{ $y->id }}" {{ $class->academic_year_id==$y->id?'selected':'' }}>{{ $y->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="name" class="font-label text-label-md text-on-surface">Nama</label>
            <input id="name" name="name" value="{{ $class->name }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="level" class="font-label text-label-md text-on-surface">Tingkat</label>
            <input id="level" name="level" value="{{ $class->level }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
