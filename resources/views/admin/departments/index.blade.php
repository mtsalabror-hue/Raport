@extends('layouts.app')

@section('title', 'Jurusan')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Data Jurusan</h1>
    </div>
    <div class="flex items-center gap-gutter-sm shrink-0">
        <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tambah</a>
    </div>
</section>

<form method="GET" class="flex flex-wrap gap-2 mb-6">
    <input name="q" value="{{ $q }}" class="border border-outline-variant rounded px-3 py-2 font-body text-body-md focus:border-primary focus:ring-1 focus:ring-primary">
    <button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Cari</button>
</form>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Kode</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nama</span></th>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($items as $d)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $d->code }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $d->name }}</td>
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">
                        <div class="flex items-center gap-gutter-sm">
                            <a href="{{ route('admin.departments.edit',$d) }}" class="text-primary hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.departments.destroy',$d) }}" class="inline">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-gutter-sm px-gutter-sm py-gutter-xs rounded bg-error text-on-error font-label text-label-md">Hapus</button></form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $items->links() }}
</div>
@endsection
