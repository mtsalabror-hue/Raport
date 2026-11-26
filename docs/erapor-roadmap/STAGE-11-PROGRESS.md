# Stage 11 Progress

Diperbarui 2026-11-26 setelah implementasi Stage 11 (Laravel 13, PHP 8.3).
Seluruh pengujian Feature + Unit: 106 lolos, 0 gagal
(`php artisan test`, 517 assertions) — 83 pengujian lama tetap hijau,
ditambah 23 pengujian baru StageElevenTest.

- Model PKL/UKK: COMPLETE (Internship: student_id, company_name,
  start_date, end_date, supervisor, grade; relasi student,
  assessments; helper status() mendatang/berjalan/selesai dari
  tanggal dan averageScore(). InternshipAssessment:
  internship_id, aspect, score, note; relasi internship.
  CompetencyExam: student_id, subject_id, score, note, exam_date;
  relasi student, subject; helper examiner() dan
  noteWithoutExaminer() untuk konvensi "Penguji: ..." di note)
- Validasi tumpang tindih penempatan: COMPLETE (InternshipService::
  findOverlap/assertNoOverlap — irisan tanggal per siswa dicegah di
  kode, bukan migrasi: irisan sebagian di awal/akhir, penempatan
  yang sepenuhnya di dalam, dan penempatan yang menelan periode
  lama semuanya ditolak dengan pesan yang menyebut penempatan yang
  bertabrakan; penempatan berurutan tanpa irisan — mulai sehari
  setelah periode lama berakhir atau berakhir sehari sebelum
  periode lama dimulai — diterima; siswa berbeda boleh memakai
  tanggal yang sama; saat ubah, penempatan diri sendiri
  dikecualikan; tanggal selesai sebelum tanggal mulai ditolak)
- Data mitra industri: COMPLETE (daftar company_name distinct dari
  penempatan yang sudah ada via InternshipService::companies(),
  tampil di halaman daftar + datalist pada form tambah penempatan;
  mitra baru tercatat otomatis saat penempatan ditambahkan — tidak
  ada tabel mitra terpisah di skema yang ada)
- CRUD penempatan siswa: COMPLETE (admin: tambah, ubah, hapus;
  hapus penempatan menghapus penilaian aspeknya lewat cascade FK;
  ActivityLog internship.created/updated/deleted; transaksi DB)
- Monitoring kegiatan: COMPLETE (/admin/internships/monitoring —
  penempatan dikelompokkan Berjalan/Aktif, Mendatang, Selesai dari
  tanggal hari ini, dengan filter siswa/perusahaan)
- Penilaian PKL per aspek: COMPLETE (admin + guru pembimbing:
  tambah/perbarui aspek — aspek yang sama pada penempatan yang
  sama hanya satu baris, pengiriman ulang memperbarui nilainya
  bukan menduplikasi; ubah/hapus per baris; ActivityLog
  internship_assessment.created/updated/deleted)
- Rekap hasil PKL: COMPLETE (per penempatan: rata-rata aspek yang
  sudah bernilai, 2 desimal; per siswa: rata-rata gabungan seluruh
  aspek bernilai + jumlah penempatan selesai/berjalan; aspek tanpa
  nilai dikeluarkan dari rata-rata, bukan dianggap 0)
- Jadwal UKK: COMPLETE (jadwal = kelompok subject_id + exam_date
  via CompetencyExamService::schedules(); daftar jadwal dengan
  jumlah peserta, jumlah yang sudah dinilai, dan rata-rata;
  halaman detail jadwal menampilkan peserta + form tambah peserta
  + input hasil per peserta)
- Peserta UKK: COMPLETE (tambah peserta ke jadwal membuat baris
  score null; satu siswa + satu mapel + satu tanggal hanya satu
  baris — pengiriman ulang memperbarui hasilnya, bukan
  menduplikasi; hapus peserta dari jadwal)
- Input hasil UKK: COMPLETE (score + note per peserta; penguji
  dicatat lewat isian "Penguji" yang disimpan sebagai baris
  "Penguji: ..." di awal kolom note dan dibaca kembali lewat
  CompetencyExam::examiner(); ActivityLog
  competency_exam.created/updated/deleted)
- Rekap hasil UKK: COMPLETE (per siswa: rata-rata per mapel +
  rata-rata gabungan; per mapel: peserta, sudah dinilai, rata-rata;
  per jadwal: peserta + rata-rata; TANPA label lulus/tidak lulus —
  ambang kelulusan belum ditetapkan sekolah, hanya skor yang
  ditampilkan)
- Nilai PKL/UKK tidak masuk rapor: COMPLETE (tidak ada pemanggilan
  AssessmentService dari modul ini dan tidak ada penulisan ke
  assessment_scores/report_card_subjects; pengujian membuktikan
  finalScore AssessmentService tetap 80 setelah ditambah penempatan
  PKL dengan aspek 100 dan 40, grade "A", dan nilai UKK 55 pada
  mapel yang sama)
- Akses admin: COMPLETE (kelola penuh PKL + UKK di bawah
  middleware role:admin; guru dan wali mendapat 403 pada route
  admin; tamu diarahkan ke /login)
- Akses guru pembimbing: COMPLETE (daftar + detail hanya
  penempatan yang kolom supervisor-nya sama dengan nama guru login
  — perbandingan tanpa memperhatikan huruf besar/kecil; guru lain
  mendapat 403 saat membuka detail maupun mengirim penilaian;
  guru dapat menilai (tambah/ubah/hapus aspek) bimbingannya
  sendiri; UKK hanya-baca untuk mapel yang diampu guru itu lewat
  teaching_assignments — mapel lain tidak tampil)
- Akses wali kelas: COMPLETE (hanya-baca PKL + UKK siswa kelas
  walinya via homeroom_assignments, periode mana pun; filter
  kelas lain atau siswa kelas lain ditolak 403; siswa kelas lain
  tidak tampil di daftar maupun rekap)
- Comprehensive tests: COMPLETE (StageElevenTest 23 pengujian:
  halaman admin PKL, tambah penempatan + log, ubah/hapus penempatan
  + cascade aspek, tumpang tindih sebagian, tumpang tindih penuh,
  berurutan tanpa irisan diterima, tanggal sama siswa berbeda
  diterima, ubah yang menimbulkan irisan ditolak, tanggal selesai
  sebelum mulai ditolak, service tumpang tindih + status periode,
  penilaian per aspek + rekap rata-rata 85, aspek sama diperbarui
  bukan diduplikasi, ubah/hapus penilaian aspek, halaman UKK,
  tambah peserta + input hasil + konvensi penguji, peserta ganda
  tidak diduplikasi, rekap UKK per siswa/mapel/jadwal, hapus UKK,
  guru hanya bimbingannya sendiri (lihat + nilai, 403 untuk guru
  lain), UKK guru hanya-baca mapel yang diampu, wali hanya kelas
  sendiri (lihat + rekap, 403 untuk kelas/siswa lain), PKL/UKK
  tidak mengubah nilai akhir rapor, tamu redirect login)

## Keputusan desain

- Nilai PKL/UKK TIDAK otomatis masuk ke rapor: belum ada keputusan
  desain eksplisit dari sekolah (sesuai STAGE-11-PKL-UKK.md —
  "tentukan apakah nilai PKL/UKK masuk ke rapor melalui aturan
  eksplisit, jangan menghubungkannya otomatis"). Modul ini tidak
  menyentuh AssessmentService maupun report_card_subjects; nilai
  akhir rapor diuji tetap sama sebelum dan sesudah data PKL/UKK
  ditambahkan.
- Tumpang tindih penempatan dicegah di kode
  (InternshipService::assertNoOverlap, dipakai pada tambah dan
  ubah), bukan migrasi: tidak ada migrasi baru pada tahap ini.
  Aturannya: dua penempatan siswa yang sama bertabrakan bila
  start_baru <= end_lama DAN end_baru >= start_lama.
- Nilai UKK per komponen: satu baris competency_exams per
  siswa+mapel per exam_date. Bila satu mapel diuji lewat beberapa
  komponen, komponen dibedakan lewat baris "Komponen: ..." di kolom
  note (keputusan Stage 11, konsisten di seluruh modul; tidak ada
  tabel skema/komponen UKK di skema yang ada). Tidak ada formula
  gabungan komponen yang diasumsikan.
- Nilai PKL per aspek lewat internship_assessments dengan aspect
  bebas (contoh: kedisiplinan, keterampilan, sikap, kerjasama,
  tanggung jawab — ditawarkan lewat datalist, bukan daftar baku,
  karena sekolah belum menetapkan daftar aspek).
- Tidak ada ambang kelulusan UKK: sekolah belum menetapkan ambang,
  jadi rekap hanya menampilkan skor (rata-rata) tanpa label
  lulus/tidak lulus. Halaman rekap diuji tidak memuat kata
  "Lulus"/"Tidak Lulus".
- Pembimbing PKL: kolom supervisor adalah string nama guru (bukan
  FK). Aturan akses guru yang dipakai: guru hanya boleh melihat
  dan menilai penempatan yang supervisor-nya sama dengan nama guru
  login (tanpa memperhatikan huruf besar/kecil). Penempatan tanpa
  supervisor atau dengan supervisor guru lain tidak terlihat oleh
  guru mana pun; admin yang mengelolanya. Pembimbing industri
  tidak punya kolom terpisah — bila perlu dicatat, cantumkan di
  supervisor (mis. "Nama Guru / PT X") atau di penilaian; belum
  ada format baku dari sekolah.
- Penguji UKK: tidak ada kolom penguji di competency_exams.
  Konvensi yang dipakai: baris "Penguji: <nama>" di awal kolom
  note (dibangun CompetencyExamService::buildNote, dibaca
  CompetencyExam::examiner()). Keterbatasan ini dicatat di sini,
  bukan disembunyikan.
- Periode PKL/UKK: internships tidak punya kolom tahun
  ajaran/semester — periode PKL ditentukan dari start_date/
  end_date, dan status monitoring dihitung dari tanggal hari ini.
  competency_exams hanya punya exam_date. Karena itu filter wali
  kelas memakai homeroom_assignments periode mana pun, dan filter
  guru untuk UKK memakai teaching_assignments periode mana pun.
- Satu aspek satu baris per penempatan, dan satu peserta satu baris
  per jadwal UKK: ditegakkan di kode (bukan unique constraint,
  karena tidak ada migrasi baru) — pengiriman ulang memperbarui
  baris yang ada.
- Tidak ada migrasi baru; tidak ada perubahan pada file Stage
  7/8/9/10 kecuali pemakaian AssessmentService yang sudah ada
  (hanya di pengujian, untuk membuktikan nilai akhir tidak
  berubah). Tidak ada perubahan batasan php/laravel di
  composer.json.

## File dibuat

- app/Models/Internship.php
- app/Models/InternshipAssessment.php
- app/Models/CompetencyExam.php
- app/Services/InternshipService.php
- app/Services/CompetencyExamService.php
- app/Http/Controllers/Admin/InternshipController.php
- app/Http/Controllers/Admin/CompetencyExamController.php
- app/Http/Controllers/Teacher/InternshipController.php
- app/Http/Controllers/Wali/InternshipController.php
- resources/views/admin/internships/index.blade.php
- resources/views/admin/internships/edit.blade.php
- resources/views/admin/internships/monitoring.blade.php
- resources/views/admin/internships/assessments.blade.php
- resources/views/admin/internships/recap.blade.php
- resources/views/admin/competency_exams/index.blade.php
- resources/views/admin/competency_exams/schedule.blade.php
- resources/views/admin/competency_exams/recap.blade.php
- resources/views/guru/internships/index.blade.php
- resources/views/guru/internships/show.blade.php
- resources/views/guru/competency_exams/index.blade.php
- resources/views/wali/internships/index.blade.php
- resources/views/wali/internships/recap.blade.php
- resources/views/wali/competency_exams/index.blade.php
- tests/Feature/Internship/StageElevenTest.php
- docs/erapor-roadmap/STAGE-11-PROGRESS.md (file ini)

## File diubah

- routes/web.php (route admin /admin/internships... dan
  /admin/competency-exams...; route guru /guru/internships... dan
  /guru/competency-exams; route wali /wali/internships... dan
  /wali/competency-exams; route tahap sebelumnya tidak diduplikasi)

## Catatan implementasi

- View mengikuti gaya Stage 7/8/9/10: Blade standalone dengan
  Tailwind CDN, tanpa layout bersama.
- Otorisasi dilakukan di backend pada setiap endpoint (bukan hanya
  menyembunyikan menu): guru lain dan wali kelas lain mendapat
  403, tamu diarahkan ke /login.
- Bug yang ditemukan dan diperbaiki saat pengujian: (1) field
  nullable (score/note) yang tidak dikirim form menyebabkan
  "Undefined array key" di controller — semua akses ke
  $d['score']/$d['note'] yang nullable diganti `$d[...] ?? null`
  di keempat controller. (2) Internship::status() menolak string
  tanggal — signaturenya dilonggarkan menjadi
  string|DateTimeInterface|null. (3) assertDatabaseHas dengan
  tanggal 'Y-m-d' tidak cocok karena SQLite menyimpan kolom date
  sebagai datetime — pengujian memakai perbandingan lewat model
  (toDateString()).
- Tidak ada deploy dan tidak ada push ke GitHub (sesuai instruksi
  tahap ini).

## Yang belum dikonfirmasi

- Apakah nilai PKL/UKK masuk ke rapor, dan bila ya dengan bobot/
  formula apa (saat ini TIDAK masuk — keputusan eksplisit masih
  menunggu sekolah).
- Ambang kelulusan UKK (saat ini rekap hanya menampilkan skor,
  tanpa label lulus/tidak lulus).
- Daftar baku aspek penilaian PKL dan rubrik/skala penilaiannya
  (saat ini aspect bebas; contoh kedisiplinan/keterampilan/sikap).
- Skema/komponen penilaian UKK per mapel dan standar kompetensi
  yang dipakai (saat ini satu baris per siswa+mapel per tanggal;
  komponen tambahan dibedakan lewat baris "Komponen: ..." di note).
- Format sertifikat UKK (belum ada — modul ini belum membuat
  sertifikat).
- Apakah perlu kolom penguji tersendiri di competency_exams,
  kolom pembimbing industri terpisah di internships, dan kolom
  tahun ajaran/semester di internships (saat ini ketiganya tidak
  ada di skema; penguji dicatat lewat konvensi "Penguji: ..." di
  note, periode PKL dari start_date/end_date).
- Apakah guru pembimbing ditentukan lewat pencocokan nama di kolom
  supervisor sudah cukup, atau perlu FK pembimbing resmi
  (saat ini pencocokan nama tanpa memperhatikan huruf besar/kecil;
  nama yang salah eja membuat penempatan tidak terlihat oleh guru
  yang bersangkutan).
- Apakah wali kelas boleh menilai PKL/UKK (saat ini wali hanya
  melihat; penilaian hanya admin + guru pembimbing) dan apakah
  guru mapel boleh menginput hasil UKK (saat ini hanya admin;
  guru hanya melihat hasil mapel yang diampunya).
