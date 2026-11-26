@extends('layouts.app')

@section('title', 'Penugasan Guru')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Penugasan Guru</h1>
        <div class="h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-violet-500"></div>
    </div>
    <div class="flex items-center gap-gutter-sm shrink-0">
        <a href="{{ route('admin.teaching-copy.index') }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-surface border border-outline-variant text-on-surface font-label text-label-lg hover:bg-surface-dim">Salin Mengajar</a>
        <a href="{{ route('admin.teaching-assignments.create') }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tambah</a>
    </div>
</section>

<form method="GET" class="flex flex-wrap gap-2 mb-6">
    <select name="teacher_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30"><option value="">Semua Guru</option>@foreach($teachers as $t)<option value="{{ $t->id }}" {{ request('teacher_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach</select>
    <select name="class_id" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-2 focus:ring-primary/30"><option value="">Semua Kelas</option>@foreach($classes as $c)<option value="{{ $c->id }}" {{ request('class_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select>
    <button class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Filter</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-sidebar border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Guru</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mapel</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Jam</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($items as $a)
                <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $a->teacher->name }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $a->subject->name }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $a->schoolClass->name }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $a->hours }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
                        <a href="{{ route('admin.teaching-assignments.edit',$a) }}" class="inline-flex items-center px-2.5 py-1 rounded bg-amber-100 text-amber-800 font-label text-label-md hover:bg-amber-200">Edit</a>
                        <form method="POST" action="{{ route('admin.teaching-assignments.destroy',$a) }}" style="display:inline">@csrf @method('DELETE')<button class="inline-flex items-center gap-gutter-sm px-gutter-sm py-gutter-xs rounded bg-error text-on-error font-label text-label-md">Hapus</button></form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{ $items->links() }}
@endsection
