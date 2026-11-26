@extends('layouts.app')

@section('title', 'Edit Semester')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Edit Semester</h1>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md p-gutter-lg">
    <form method="POST" action="/admin/semesters/{{ $semester->id }}" class="flex flex-col gap-gutter-md">
        @csrf @method('PUT')
        <div class="flex flex-col gap-gutter-xs">
            <label for="academic_year_id" class="font-label text-label-md text-on-surface">Tahun Ajaran</label>
            <select id="academic_year_id" name="academic_year_id" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
                @foreach($years as $y)
                <option value="{{ $y->id }}" {{ $semester->academic_year_id==$y->id?'selected':'' }}>{{ $y->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-gutter-xs">
            <label for="name" class="font-label text-label-md text-on-surface">Nama</label>
            <input id="name" name="name" value="{{ $semester->name }}" class="w-full border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Simpan</button>
        </div>
    </form>
</div>
@endsection
