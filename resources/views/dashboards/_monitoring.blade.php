{{-- Bagian pemantauan bersama Kepala Sekolah & Waka Kurikulum (Fase A).
     Variabel: $activeYear, $semester, $scoreStatus, $classCompleteness,
     $published, $lockedCount, $fullySentCount, $canLock. --}}

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md px-4 py-3 flex items-center gap-2 font-body text-body-md"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="bg-error-container border border-error/30 text-on-error-container rounded-md px-4 py-3 font-body text-body-md">
    <ul class="list-disc pl-5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

@if(!$activeYear || !$semester)
<section class="bg-amber-50 border border-amber-200 rounded-md p-gutter-lg">
    <h2 class="font-headline text-headline-md text-amber-900">Periode aktif belum diatur</h2>
    <p class="font-body text-body-md text-on-surface-muted">Belum ada tahun ajaran dan semester aktif. Minta admin mengaktifkan tahun ajaran dan semester terlebih dahulu.</p>
</section>
@else
<section aria-label="Statistik Utama" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-gutter-lg items-stretch">
    <article class="h-full bg-blue-50 border border-blue-200 border-l-4 border-l-blue-500 rounded-xl p-gutter-lg flex flex-col justify-between gap-gutter-md">
        <header class="flex items-center justify-between gap-gutter-sm">
            <h2 class="font-label text-label-lg text-on-surface-muted">Guru–Mapel</h2>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-blue-600 text-white shrink-0"><span class="material-symbols-outlined text-headline-md" aria-hidden="true">assignment</span></span>
        </header>
        <div class="flex flex-col gap-gutter-xs">
            <p class="font-display text-display-md text-blue-700 tabular-nums">{{ $scoreStatus->count() }}</p>
            <p class="font-body text-body-sm text-on-surface-muted">Pasangan guru–mapel periode ini</p>
        </div>
    </article>
    <article class="h-full bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 rounded-xl p-gutter-lg flex flex-col justify-between gap-gutter-md">
        <header class="flex items-center justify-between gap-gutter-sm">
            <h2 class="font-label text-label-lg text-on-surface-muted">Nilai Terkirim</h2>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-emerald-600 text-white shrink-0"><span class="material-symbols-outlined text-headline-md" aria-hidden="true">send</span></span>
        </header>
        <div class="flex flex-col gap-gutter-xs">
            <p class="font-display text-display-md text-emerald-700 tabular-nums">{{ $fullySentCount }}</p>
            <p class="font-body text-body-sm text-on-surface-muted">dari {{ $scoreStatus->count() }} guru–mapel sudah terkirim semua</p>
        </div>
    </article>
    <article class="h-full bg-violet-50 border border-violet-200 border-l-4 border-l-violet-500 rounded-xl p-gutter-lg flex flex-col justify-between gap-gutter-md">
        <header class="flex items-center justify-between gap-gutter-sm">
            <h2 class="font-label text-label-lg text-on-surface-muted">Rapor Diterbitkan</h2>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-violet-600 text-white shrink-0"><span class="material-symbols-outlined text-headline-md" aria-hidden="true">description</span></span>
        </header>
        <div class="flex flex-col gap-gutter-xs">
            <p class="font-display text-display-md text-violet-700 tabular-nums">{{ $published->count() }}</p>
            <p class="font-body text-body-sm text-on-surface-muted">Siap dikunci Kepala Sekolah</p>
        </div>
    </article>
    <article class="h-full bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-xl p-gutter-lg flex flex-col justify-between gap-gutter-md">
        <header class="flex items-center justify-between gap-gutter-sm">
            <h2 class="font-label text-label-lg text-on-surface-muted">Rapor Terkunci</h2>
            <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-amber-600 text-white shrink-0"><span class="material-symbols-outlined text-headline-md" aria-hidden="true">lock</span></span>
        </header>
        <div class="flex flex-col gap-gutter-xs">
            <p class="font-display text-display-md text-amber-700 tabular-nums">{{ $lockedCount }}</p>
            <p class="font-body text-body-sm text-on-surface-muted">Rapor sudah dikunci periode ini</p>
        </div>
    </article>
</section>

<section class="flex flex-col gap-gutter-sm">
    <h2 class="font-headline text-headline-md text-on-surface">Status Nilai per Guru &amp; Mapel</h2>
    <p class="font-body text-body-md text-on-surface-muted">Periode: {{ $activeYear->name }} — {{ $semester->name }}.</p>
    <div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sidebar border-b border-outline-variant">
                    <tr>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Guru</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Mata Pelajaran</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Draft</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Terkirim</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Dikunci</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Status</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($scoreStatus as $row)
                    <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['teacher']?->name ?? '-' }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['subject']?->name ?? '-' }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['draft'] }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['terkirim'] }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['dikunci'] }}</td>
                        <td class="px-gutter-md py-gutter-md">
                            @if($row['total'] === 0)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-label text-label-md">Belum input</span>
                            @elseif($row['draft'] > 0)
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-label text-label-md">Masih draft</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-label text-label-md">Terkirim semua</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada penugasan mengajar pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="flex flex-col gap-gutter-sm">
    <h2 class="font-headline text-headline-md text-on-surface">Kelengkapan Rapor per Kelas</h2>
    <div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sidebar border-b border-outline-variant">
                    <tr>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Rapor Lengkap</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Diajukan</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Disetujui</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Diterbitkan</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Terkunci</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($classCompleteness as $row)
                    <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $row['class']->name }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['students'] }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['complete'] }} / {{ $row['students'] }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['cards']['diajukan'] ?? 0 }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['cards']['divalidasi'] ?? 0 }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['cards']['diterbitkan'] ?? 0 }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface tabular-nums">{{ $row['cards']['dikunci'] ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Belum ada kelas pada tahun ajaran aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="flex flex-col gap-gutter-sm">
    <h2 class="font-headline text-headline-md text-on-surface">Rapor Diterbitkan — Siap Dikunci</h2>
    <p class="font-body text-body-md text-on-surface-muted">Mengunci rapor berarti nilai rapor tidak dapat diubah lagi. @if(!$canLock) Aksi kunci hanya tersedia untuk Kepala Sekolah. @endif</p>
    <div class="bg-surface border border-outline-variant rounded-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-sidebar border-b border-outline-variant">
                    <tr>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Siswa</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Kelas</span></th>
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Diterbitkan</span></th>
                        @if($canLock)
                        <th class="px-gutter-md py-gutter-md"><span class="font-label text-label-md text-white uppercase">Aksi</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($published as $card)
                    <tr class="hover:bg-surface-dim even:bg-blue-50/50 transition-colors duration-state-change">
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->student->name }} <span class="text-on-surface-muted">({{ $card->student->nis }})</span></td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->schoolClass->name ?? '-' }}</td>
                        <td class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface">{{ $card->issued_at ? $card->issued_at->format('d-m-Y H:i') : '-' }}</td>
                        @if($canLock)
                        <td class="px-gutter-md py-gutter-md">
                            <form method="POST" action="{{ route('kepala.report-cards.lock', $card) }}" class="inline">@csrf
                                <button type="submit" class="inline-flex items-center gap-gutter-xs px-gutter-sm py-gutter-xs rounded bg-amber-600 text-white font-label text-label-md hover:bg-amber-700">Kunci</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="{{ $canLock ? 4 : 3 }}" class="px-gutter-md py-gutter-md font-body text-body-md text-on-surface-muted">Tidak ada rapor berstatus diterbitkan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif
