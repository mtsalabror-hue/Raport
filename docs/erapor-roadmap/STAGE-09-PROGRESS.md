# Stage 9 Progress

Diperbarui 2026-11-26 setelah implementasi Stage 9 (Laravel 13, PHP 8.3).
Seluruh pengujian Feature + Unit: 64 lolos, 0 gagal
(`php artisan test`, 200 assertions) — 49 pengujian lama tetap hijau,
ditambah 15 pengujian baru StageNineTest.

- Dashboard kelas: COMPLETE (kelas dibatasi ke homeroom_assignments
  milik wali; ringkasan total siswa, rapor lengkap/belum lengkap,
  sudah diajukan, nilai belum terkirim; progres kelengkapan per siswa
  dengan daftar kekurangan bertautan ke sumber data)
- Data siswa: COMPLETE (daftar siswa kelas wali + status rapor per
  siswa, tautan preview/riwayat/catatan)
- Rekap nilai: COMPLETE (dipakai ulang, tidak diduplikasi: route
  /wali/assessments/recap dari Stage 7 dan
  /wali/attendance/academic-recap dari Stage 8, hanya nilai
  terkirim/dikunci; wali tidak dapat mengubah nilai — endpoint guru
  menolak wali lewat middleware role:guru, teruji 403)
- Rekap presensi: COMPLETE (dipakai ulang, tidak diduplikasi: route
  /wali/attendance/recap dari Stage 8 via AttendanceService)
- Catatan wali kelas: COMPLETE (CRUD report_card_notes note_type
  wali_kelas; menyimpan catatan otomatis membuat rapor draft bila
  belum ada; ubah/hapus mencatat ActivityLog dengan isi lama dan baru)
- Prestasi siswa: COMPLETE (CRUD student_achievements per periode
  AY + semester, dibatasi ke siswa kelas wali, ActivityLog)
- Ekstrakurikuler: COMPLETE (CRUD student_extracurriculars per
  siswa + periode; duplikasi siswa + ekskul + periode ditolak lewat
  validasi pra-simpan dan unique database; CRUD data master
  extracurriculars; master yang masih dipakai catatan siswa tidak
  dapat dihapus)
- Monitoring kelengkapan rapor: COMPLETE (ReportCompletenessService
  memeriksa 4 komponen wajib per siswa; setiap kekurangan membawa
  pesan dan tautan ke sumbernya: input nilai, presensi, deskripsi,
  catatan; ringkasan lengkap/belum lengkap per kelas)
- Preview rapor: COMPLETE (satu siswa: identitas, nilai akhir +
  deskripsi per mapel, presensi H/S/I/A + persentase, prestasi,
  ekstrakurikuler, catatan wali, status kelengkapan dan status rapor;
  hanya-baca)
- Pengajuan rapor: COMPLETE (draft -> diajukan hanya bila lengkap,
  dalam transaksi; ditolak bila tidak lengkap dengan daftar
  kekurangan; pengajuan kedua ditolak karena status bukan draft;
  pengaju dan waktu dicatat lewat ActivityLog
  `report_card.submitted` dengan metadata submitted_by dan
  submitted_at; kolom issued_by TIDAK dipakai — itu untuk penerbit
  di Stage 10)
- Riwayat akademik: COMPLETE (nilai akhir + presensi + prestasi
  lintas semester per siswa, hanya-baca, urut periode terbaru dulu)
- Batas kelas/penugasan: COMPLETE (semua endpoint wali memeriksa
  homeroom_assignments miliknya di backend, bukan hanya di menu;
  kelas lain 403 pada dashboard, data siswa, kelengkapan, preview,
  pengajuan, catatan, prestasi, dan ekstrakurikuler)
- Comprehensive tests: COMPLETE (StageNineTest 15 pengujian:
  dashboard hanya kelas sendiri, wali tidak bisa ubah nilai,
  kelengkapan kurang vs lengkap + daftar kekurangan, kekurangan
  deskripsi saat nilai sudah terkirim, CRUD catatan + rapor draft
  otomatis, catatan kelas lain 403, CRUD prestasi, CRUD ekskul +
  duplikasi ditolak, preview semua komponen, preview kelas lain
  403, pengajuan ditolak bila tidak lengkap, pengajuan berhasil +
  ActivityLog pengaju, pengajuan kelas lain 403, riwayat lintas
  semester)

## Keputusan desain

- Wali kelas TIDAK BOLEH mengubah nilai mapel (read-only). Tidak ada
  endpoint tulis nilai di bawah prefix /wali; percobaan POST wali ke
  endpoint guru ditolak middleware role:guru (403).
- Wali hanya mengakses kelas pada homeroom_assignments miliknya
  untuk periode AY + semester yang diminta (assertHomeroom /
  assertStudentAccess, pola yang sama dengan Stage 8).
- Daftar komponen wajib kelengkapan rapor (keputusan sementara,
  sekolah belum menetapkan): (1) semua mapel yang diajarkan di kelas
  itu punya nilai akhir yang sudah terkirim/dikunci untuk siswa
  tersebut, (2) ada minimal satu catatan presensi pada periode itu,
  (3) ada deskripsi capaian (teacher_descriptions) untuk tiap mapel,
  (4) ada catatan wali kelas (report_card_notes note_type
  wali_kelas). Mapel yang diajarkan diambil dari
  teaching_assignments; bila belum ada penugasan sama sekali untuk
  kelas/periode itu, dipakai mapel yang sudah punya nilai pada
  kelas/periode itu (pola soft-check Stage 7/8).
- Pengajuan mencatat pengaju dan waktu lewat ActivityLog
  (report_card.submitted, metadata submitted_by + submitted_at),
  bukan kolom issued_by — kolom itu untuk penerbit di Stage 10.
- Menyimpan catatan wali kelas otomatis membuat report_cards draft
  (unique student + AY + semester), karena catatan menempel pada
  rapor. Wali hanya boleh membuat/mengubah/menghapus catatan
  note_type wali_kelas; catatan kepala_sekolah bukan wewenangnya
  (Stage 10).
- ReportCardSubject dan ReportCardApproval TIDAK dibuat di tahap ini
  (Stage 10). Relasi ReportCard::subjects hanya-baca lewat
  belongsToMany pada tabel report_card_subjects, tanpa model.
- Tidak ada migrasi baru; tidak ada perubahan pada file Stage 7/8
  kecuali pemakaian AssessmentService dan AttendanceService yang
  sudah ada. Dashboard wali dialihkan dari DashboardController::wali
  ke HomeroomController::dashboard (route /wali/dashboard).

## File dibuat

- app/Models/ReportCard.php
- app/Models/ReportCardNote.php
- app/Models/StudentAchievement.php
- app/Models/Extracurricular.php
- app/Models/StudentExtracurricular.php
- app/Services/ReportCompletenessService.php
- app/Http/Controllers/Wali/HomeroomController.php
- resources/views/wali/dashboard.blade.php
- resources/views/wali/students.blade.php
- resources/views/wali/notes.blade.php
- resources/views/wali/achievements.blade.php
- resources/views/wali/extracurriculars.blade.php
- resources/views/wali/completeness.blade.php
- resources/views/wali/preview.blade.php
- resources/views/wali/history.blade.php
- tests/Feature/Homeroom/StageNineTest.php
- docs/erapor-roadmap/STAGE-09-PROGRESS.md (file ini)

## File diubah

- routes/web.php (route wali /wali/dashboard dialihkan ke
  HomeroomController; route baru /wali/students, /wali/notes,
  /wali/achievements, /wali/extracurriculars + master,
  /wali/completeness, /wali/preview/{student}, /wali/submit,
  /wali/history/{student}; route rekap lama tidak diduplikasi)

## Catatan implementasi

- Model Student hanya mengizinkan mass-assignment nis/nisn/name,
  sehingga pada pengujian class_id siswa diisi lewat assignment
  langsung lalu save (kolom class_id ada di tabel).
- View mengikuti gaya Stage 7/8: Blade standalone dengan Tailwind
  CDN, tanpa layout bersama.
- Otorisasi dilakukan di backend pada setiap endpoint (bukan hanya
  menyembunyikan menu): wali kelas lain mendapat 403.
- Bug yang ditemukan dan diperbaiki saat pengujian: riwayat akademik
  memakai merge() Eloquent Collection pada model hasil select
  parsial (tanpa id) — merge() Eloquent mendeduplikasi berdasarkan
  kunci model dan membuang semua baris yang kuncinya null, sehingga
  daftar periode kosong. Diperbaiki dengan menggabungkan string
  kunci periode lewat koleksi dasar. Pengurutan periode juga
  diperbaiki dari callback dua argumen (tidak didukung sortBy)
  menjadi kriteria [['academic_year_id', 'desc'],
  ['semester_id', 'desc']].
- Tidak ada deploy dan tidak ada push ke GitHub (sesuai instruksi
  tahap ini).

## Yang belum dikonfirmasi

- Daftar komponen wajib kelengkapan rapor (4 komponen di atas)
  adalah keputusan sementara — sekolah belum menetapkan komponen
  wajib, ambang kehadiran minimum, atau apakah prestasi dan
  ekstrakurikuler wajib ada di rapor. Perlu konfirmasi sekolah
  sebelum dianggap final.
- Siapa yang memeriksa/menyetujui rapor yang diajukan wali (kepala
  sekolah? admin?) dan apa yang terjadi bila rapor dikembalikan —
  itu alur Stage 10 (ReportCardApproval), belum diimplementasikan.
- Apakah predikat (A/B/C/D) tampil pada rapor (juga belum
  dikonfirmasi sejak Stage 7).
- Apakah wali kelas boleh mengoreksi presensi (saat ini koreksi
  hanya lewat route guru; wali hanya-baca) — belum dikonfirmasi
  sejak Stage 8.
