@extends('layouts.app')

@section('title', 'Kurikulum')

@section('content')
<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
    <div class="flex flex-col gap-gutter-xs">
        <h1 class="font-headline text-headline-lg text-on-surface">Kurikulum</h1>
    </div>
    <div class="flex items-center gap-gutter-sm shrink-0">
        <a href="/admin/curricula/create" class="inline-flex items-center gap-gutter-sm px-gutter-md py-gutter-sm rounded bg-primary text-on-primary font-label text-label-lg hover:bg-primary-hover">Tambah</a>
    </div>
</section>

<div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-dim border-b border-outline-variant">
                <tr>
                    <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-on-surface-muted uppercase">Nama</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($items as $c)
                <tr class="hover:bg-surface-dim transition-colors duration-state-change">
                    <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $c->name }}</td>
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
