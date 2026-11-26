@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<section class="bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white rounded-xl shadow-lg p-gutter-lg">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-white">Selamat datang, {{ auth()->user()->name }}!</h1>
    <p class="font-body text-body-md text-blue-100">Ringkasan data sekolah dan tahun ajaran aktif: {{ $activeYear?->name ?? '-' }}.</p>
  </div>
</section>

<section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h1 class="font-headline text-headline-lg text-on-surface">Dashboard Admin</h1>
    <p class="font-body text-body-md text-on-surface-muted">Ringkasan data sekolah dan tahun ajaran aktif.</p>
  </div>
</section>

<section aria-label="Statistik Utama" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-gutter-lg items-stretch">
  <article class="h-full bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 rounded-xl p-gutter-lg flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Siswa</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-blue-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">groups</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-blue-700 tabular-nums">{{ $students }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Siswa terdaftar</p>
    </div>
  </article>

  <article class="h-full bg-violet-50 border border-violet-200 border-l-4 border-l-violet-500 rounded-xl p-gutter-lg flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Guru</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-violet-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">badge</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-violet-700 tabular-nums">{{ $teachers }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Guru terdaftar</p>
    </div>
  </article>

  <article class="h-full bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 rounded-xl p-gutter-lg flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Total Kelas</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-emerald-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">meeting_room</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-emerald-700 tabular-nums">{{ $classes }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Rombongan belajar</p>
    </div>
  </article>

  <article class="h-full bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-xl p-gutter-lg flex flex-col justify-between transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md gap-gutter-md">
    <header class="flex items-center justify-between gap-gutter-sm">
      <h2 class="font-label text-label-lg text-on-surface-muted">Mata Pelajaran</h2>
      <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-amber-600 text-white shrink-0">
        <span class="material-symbols-outlined text-headline-md" aria-hidden="true">menu_book</span>
      </span>
    </header>
    <div class="flex flex-col gap-gutter-xs">
      <p class="font-display text-display-md text-amber-700 tabular-nums">{{ $subjects }}</p>
      <p class="font-body text-body-sm text-on-surface-muted">Mapel terdaftar</p>
    </div>
  </article>
</section>

<section class="bg-indigo-50 border border-indigo-200 rounded-md p-gutter-lg flex flex-col gap-gutter-sm">
  <h2 class="font-headline text-headline-md text-indigo-900">Tahun Ajaran Aktif</h2>
  <p class="font-body text-body-md text-on-surface-muted">Saat ini sistem berjalan pada tahun ajaran <span class="font-label text-label-lg text-on-surface">{{ $activeYear?->name ?? '-' }}</span>.</p>
</section>

{{-- Fase F: status pengiriman nilai per mapel (MonitoringService Fase A, data asli periode aktif) --}}
<section aria-label="Status Pengiriman Nilai" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h2 class="font-headline text-headline-md text-on-surface">Status Pengiriman Nilai</h2>
    <p class="font-body text-body-md text-on-surface-muted">Periode aktif: {{ $activeYear?->name ?? '-' }} &middot; {{ $activeSemester?->name ?? '-' }}. Jumlah nilai per mapel berdasarkan status pengiriman guru.</p>
  </div>
  @if($scoreBySubject->isNotEmpty())
  <div class="overflow-x-auto"><table class="w-full text-left border-collapse">
    <thead class="bg-sidebar">
      <tr>
        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mata Pelajaran</span></th>
        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Draft</span></th>
        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Terkirim</span></th>
        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Dikunci</span></th>
        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Total</span></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-outline-variant">
      @foreach($scoreBySubject as $row)
      <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['subject'] }}</td>
        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['draft'] }}</td>
        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['terkirim'] }}</td>
        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['dikunci'] }}</td>
        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums font-semibold">{{ $row['total'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table></div>
  @else
  <p class="font-body text-body-md text-on-surface-muted">Belum ada penugasan atau nilai pada periode aktif.</p>
  @endif
</section>

{{-- Fase F: aktivitas guru terakhir (activity_logs, data asli) --}}
<section aria-label="Aktivitas Guru Terakhir" class="bg-surface border border-outline-variant rounded-md p-gutter-lg flex flex-col gap-gutter-md">
  <div class="flex flex-col gap-gutter-xs">
    <h2 class="font-headline text-headline-md text-on-surface">Aktivitas Guru Terakhir</h2>
    <p class="font-body text-body-md text-on-surface-muted">Aktivitas terbaru yang tercatat di log sistem.</p>
  </div>
  @if($recentActivities->isNotEmpty())
  <ul class="flex flex-col divide-y divide-outline-variant">
    @foreach($recentActivities as $log)
    <li class="py-gutter-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-gutter-xs">
      <span class="font-body text-body-md text-on-surface"><strong>{{ $log->user->name ?? 'Sistem' }}</strong> &middot; {{ $log->action }}</span>
      <span class="font-body text-body-sm text-on-surface-muted tabular-nums">{{ $log->created_at?->format('d-m-Y H:i') }}</span>
    </li>
    @endforeach
  </ul>
  @else
  <p class="font-body text-body-md text-on-surface-muted">Belum ada aktivitas tercatat.</p>
  @endif
</section>
@endsection
