# Stage 10 Progress

Diperbarui 2026-11-26 setelah implementasi Stage 10 (Laravel 13, PHP 8.3).
Seluruh pengujian Feature + Unit: 83 lolos, 0 gagal
(`php artisan test`, 363 assertions) — 64 pengujian lama tetap hijau,
ditambah 19 pengujian baru StageTenTest.

- Model rapor: COMPLETE (ReportCardSubject: report_card_id,
  subject_id, final_score, description; ReportCardApproval:
  report_card_id, approver_id, status pending/approved/rejected,
  note, approved_at; relasi baru di ReportCard: reportCardSubjects,
  approvals, latestApproval; relasi subjects belongsToMany lama
  dipertahankan apa adanya)
- Pemetaan status dokumen: COMPLETE (ReportCard::documentLabel():
  draft = Belum Lengkap, atau Siap Diperiksa bila semua komponen
  lengkap; diajukan = Diajukan; divalidasi = Disetujui;
  diterbitkan = Diterbitkan; dikunci = Terkunci. Enum tabel tidak
  diubah. "Perlu Perbaikan" diwakili approval rejected + note alasan
  dan rapor kembali ke draft)
- Pemeriksaan admin: COMPLETE (daftar rapor dengan filter kelas /
  tahun ajaran / semester / status + hitungan per status; detail
  pemeriksaan menampilkan komponen live, hasil validasi kelengkapan
  dari ReportCompletenessService, snapshot bila sudah diterbitkan,
  presensi, prestasi, ekstrakurikuler, catatan wali, riwayat
  persetujuan, dan riwayat status)
- Persetujuan: COMPLETE (admin saja, route di bawah middleware
  role:admin; hanya dari status diajukan; kelengkapan diperiksa
  ulang sebelum menyetujui — rapor yang menjadi tidak lengkap
  setelah pengajuan ditolak; approval approved + approved_at +
  approver = admin login; rapor diajukan -> divalidasi; ActivityLog
  report_card.approved)
- Pengembalian: COMPLETE (alasan wajib — tanpa alasan ditolak
  validasi; approval rejected + note alasan; rapor kembali ke
  draft; ActivityLog report_card.rejected; detail menampilkan
  "Dikembalikan (Perlu Perbaikan)" beserta alasan)
- Penerbitan + snapshot: COMPLETE (hanya dari divalidasi; dalam
  transaksi; nilai akhir dari AssessmentService + deskripsi dari
  teacher_descriptions disalin ke report_card_subjects; catatan
  sudah tersimpan di report_card_notes dan dipakai sebagai snapshot
  catatan; issued_at + issued_by terisi; rapor -> diterbitkan;
  ActivityLog report_card.published; terbit ganda ditolak — rapor
  yang sudah diterbitkan/dikunci tidak dapat diterbitkan ulang,
  issued_at tidak berubah, hanya satu log published)
- Penguncian: COMPLETE (diterbitkan -> dikunci; locked_at terisi;
  ActivityLog report_card.locked; kunci kedua ditolak)
- Snapshot tidak berubah: COMPLETE (nilai live diubah 80 -> 95 dan
  deskripsi live diubah setelah terbit: baris report_card_subjects
  tetap 80 + deskripsi lama, dan data PDF tetap memakai snapshot;
  teruji lewat ReportCardService::pdfData dan isi PDF)
- PDF: COMPLETE (barryvdh/laravel-dompdf ^3.1, terpasang tanpa
  mengubah batasan php/laravel di composer.json; A4 portrait;
  template HTML khusus DomPDF — tabel dan CSS sederhana, tanpa
  flexbox/grid; identitas siswa, tahun ajaran, semester, tabel
  mapel + nilai akhir + deskripsi dari snapshot, presensi,
  catatan wali, catatan kepala sekolah (bila ada), prestasi,
  ekstrakurikuler, area tanda tangan Wali Kelas, Kepala Sekolah,
  Orang Tua/Wali; nama file rapor-{nis}-{semester}.pdf, misal
  rapor-001-ganjil.pdf; hanya untuk rapor diterbitkan/dikunci)
- Akses PDF: COMPLETE (admin semua rapor; wali hanya rapor kelas
  walinya — dicek homeroom_assignments per periode, kelas lain 403;
  guru 403 di kedua route PDF — PDF dibatasi ke admin + wali
  kelasnya, sesuai keputusan desain; tamu diarahkan ke /login)
- Status rapor wali: COMPLETE (daftar status rapor kelas wali
  sendiri di /wali/report-cards dengan filter kelas / periode /
  status + tautan preview dan unduh PDF bila sudah diterbitkan)
- Riwayat status: COMPLETE (ActivityLog per perubahan status +
  riwayat persetujuan di detail dan di halaman riwayat khusus
  /admin/report-cards/{id}/history)
- Koreksi setelah terbit: COMPLETE (tambah/ubah/hapus catatan wali
  pada rapor diterbitkan/dikunci ditolak 403 dan percobaan dicatat
  di ActivityLog report_card.correction_rejected; isi catatan
  tidak berubah)
- Data lintas semester: COMPLETE (semua pembacaan dibatasi AY +
  semester rapor itu; snapshot semester 1 nilai 80 tidak tercampur
  nilai semester 2 nilai 90; rapor semester 2 tetap draft dan
  snapshot-nya kosong sampai diterbitkan sendiri)
- Comprehensive tests: COMPLETE (StageTenTest 19 pengujian:
  halaman admin index/show/history, setujui butuh status diajukan,
  setujui ditolak bila tidak lengkap setelah pengajuan, setujui
  berhasil + approver + log, wali dan guru tidak bisa menyetujui,
  kembalikan butuh alasan + kembali draft + approval rejected,
  terbit butuh status divalidasi, terbit ditolak bila tidak lengkap
  setelah persetujuan, terbit membuat snapshot + issued_at/by,
  terbit ganda ditolak, kunci hanya dari diterbitkan + kunci kedua
  ditolak, PDF memakai snapshot bukan data live + nama file benar,
  PDF ditolak sebelum terbit, PDF wali hanya kelas sendiri, guru
  tidak bisa unduh PDF, tamu redirect login, koreksi setelah terbit
  ditolak + dicatat, data lintas semester tidak tercampur, pemetaan
  label dokumen)

## Keputusan desain

- Pemeriksa/penyetuju adalah admin (role admin). Wali hanya
  mengajukan dan melihat status; wali tidak boleh menyetujui rapor
  kelasnya sendiri — ditegakkan middleware role:admin pada seluruh
  route /admin/report-cards, teruji 403 untuk wali dan guru.
- Snapshot penerbitan = nilai akhir + deskripsi per mapel yang
  disalin ke report_card_subjects, dan catatan yang sudah tersimpan
  di report_card_notes. PDF memakai data snapshot tersebut.
  Presensi, prestasi, dan ekstrakurikuler pada PDF dibaca live
  tetapi selalu dibatasi periode (AY + semester) rapor itu, karena
  tidak ada tabel snapshot untuk ketiganya di skema yang ada;
  perubahan data live ketiganya setelah terbit masih akan tercermin
  di PDF bila diunduh ulang — keterbatasan ini dicatat, bukan
  disembunyikan (lihat "Yang belum dikonfirmasi").
- Koreksi setelah terbit ditolak tanpa prosedur resmi: guard
  ReportCardService::assertEditable dipakai pada endpoint yang
  mengubah report_card_notes (tambah/ubah/hapus catatan wali di
  HomeroomController). Perubahan nilai/deskripsi live oleh guru
  TIDAK diblokir tambahan, karena (a) tidak menyentuh snapshot
  maupun PDF rapor yang sudah terbit, dan (b) nilai yang sudah
  terkirim/dikunci sudah ditolak endpoint guru lewat alur revisi
  Stage 7. Percobaan koreksi catatan dicatat
  report_card.correction_rejected lalu 403.
- PDF dibatasi ke admin + wali kelasnya (guru 403), sesuai
  instruksi tahap ini bila penugasan guru diragukan.
- Nama sekolah pada kop PDF dibaca dari SystemSetting key
  school_name (value['value']) bila ada; bila belum disetel,
  tampil "E-Rapor SMK" — nama sekolah tidak dikarang.
- Nama penandatangan: nama wali kelas diambil dari
  homeroom_assignments periode itu bila ada; Kepala Sekolah dan
  Orang Tua/Wali dibiarkan kosong untuk diisi tangan, karena
  penandatangan resmi belum dikonfirmasi.
- Tidak ada migrasi baru; tidak ada perubahan pada file Stage 7/8
  kecuali pemakaian AssessmentService, AttendanceService, dan
  ReportCompletenessService yang sudah ada. Satu-satunya perubahan
  pada file Stage 9 adalah HomeroomController: injeksi
  ReportCardService dan guard assertEditable pada tiga endpoint
  catatan (store/update/destroy note) — tanpa mengubah perilaku
  lain. Tidak ada perubahan batasan php/laravel di composer.json.

## File dibuat

- app/Models/ReportCardSubject.php
- app/Models/ReportCardApproval.php
- app/Services/ReportCardService.php
- app/Services/ReportCardPdfService.php
- app/Http/Controllers/Admin/ReportCardController.php
- app/Http/Controllers/Wali/ReportCardController.php
- resources/views/admin/report_cards/index.blade.php
- resources/views/admin/report_cards/show.blade.php
- resources/views/admin/report_cards/history.blade.php
- resources/views/admin/report_cards/pdf.blade.php
- resources/views/wali/report_cards/index.blade.php
- tests/Feature/ReportCard/StageTenTest.php
- docs/erapor-roadmap/STAGE-10-PROGRESS.md (file ini)

## File diubah

- app/Models/ReportCard.php (relasi reportCardSubjects, approvals,
  latestApproval; documentLabel(); helper isValidated, isPublished,
  isLocked)
- app/Http/Controllers/Wali/HomeroomController.php (injeksi
  ReportCardService; guard koreksi pasca-terbit pada storeNote,
  updateNote, destroyNote)
- routes/web.php (route admin /admin/report-cards... dan route wali
  /wali/report-cards + pdf; route Stage 9 tidak diduplikasi)
- composer.json + composer.lock (barryvdh/laravel-dompdf ^3.1
  ditambahkan; batasan php ^8.3 dan laravel/framework ^13.17 tidak
  berubah)

## Catatan implementasi

- Package PDF: `composer require barryvdh/laravel-dompdf`
  berhasil resolve di Laravel 13 (versi ^3.1) — tidak perlu
  fallback ke dompdf/dompdf langsung.
- View mengikuti gaya Stage 7/8/9: Blade standalone dengan
  Tailwind CDN, tanpa layout bersama. Template PDF memakai CSS
  sederhana dan tabel (DomPDF tidak mendukung flexbox/grid modern).
- Otorisasi dilakukan di backend pada setiap endpoint (bukan hanya
  menyembunyikan menu): wali kelas lain dan guru mendapat 403,
  tamu diarahkan ke /login.
- Bug yang ditemukan dan diperbaiki saat pengujian: (1) test
  mengharapkan label "Belum Lengkap" pada detail rapor draft yang
  lengkap — label yang benar adalah "Siap Diperiksa" (draft +
  lengkap); test diperbaiki, bukan kode. (2) Test lintas semester
  memakai tanggal presensi yang sama untuk dua semester sehingga
  melanggar unique (student_id, date) pada student_attendances;
  helper seedComplete diberi parameter tanggal.
- Tidak ada deploy dan tidak ada push ke GitHub (sesuai instruksi
  tahap ini). Salinan lokal di ~/workspace/RAPOR bukan repository
  git (tidak ada .git), jadi tidak ada commit yang dibuat.

## Yang belum dikonfirmasi

- Daftar komponen wajib kelengkapan rapor (4 komponen dari Stage 9)
  masih keputusan sementara — sekolah belum menetapkan komponen
  wajib, ambang kehadiran minimum, atau apakah prestasi dan
  ekstrakurikuler wajib ada di rapor.
- Siapa penandatangan resmi rapor (nama Kepala Sekolah) dan apakah
  ada kolom NIP pada area tanda tangan — saat ini Kepala Sekolah
  dan Orang Tua/Wali dibiarkan kosong untuk diisi tangan.
- Nama/kop sekolah resmi untuk PDF (saat ini dibaca dari
  SystemSetting school_name; bila belum disetel tampil "E-Rapor
  SMK").
- Apakah predikat (A/B/C/D) tampil pada rapor (belum dikonfirmasi
  sejak Stage 7).
- Apakah presensi, prestasi, dan ekstrakurikuler perlu ikut
  di-snapshot secara immutable saat penerbitan (saat ini hanya
  nilai + deskripsi + catatan yang di-snapshot; ketiganya dibaca
  live per periode) dan bagaimana prosedur resmi koreksi setelah
  terbit (saat ini semua koreksi ditolak 403 + dicatat; belum ada
  alur revisi resmi pasca-terbit).
- Apakah guru yang mengampu mapel di kelas tersebut boleh mengunduh
  PDF rapor (saat ini dibatasi ke admin + wali kelasnya).
