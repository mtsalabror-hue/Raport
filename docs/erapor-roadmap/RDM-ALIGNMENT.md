# Penyelarasan E-Rapor dengan RDM (Rapor Digital Madrasah)

Terakhir diperbarui: 2026-11-26. Aplikasi tetap bernama **Rapor** (bukan RDM).
Sumber acuan: Panduan Penggunaan Aplikasi RDM (Kemenag RI, 2024) dan tutorial RDM.
Pengerjaan: 6 fase (A–F), selesai 2026-11-26, TIDAK di-deploy.

## Ringkasan

| Fase | Isi | Commit | Test setelah fase |
|---|---|---|---|
| A | Profil Sekolah + Pengaturan Cetak + peran Kepala Sekolah & Waka Kurikulum | `a44e9931` | 143 lolos |
| B | Sikap Sosial & Spiritual | `aa3282b0` | 152 lolos |
| C | P5 / Kokurikuler | `23c7a932` | 160 lolos |
| D | Kenaikan Kelas & Kelulusan | `9d85c3e4` | 168 lolos |
| E | Backup & Restore + Salin Mengajar + Impor CSV | `66aec7c0` | 182 lolos |
| F | Cetak Sampul/Identitas/Leger/Transkrip + Pembina Ekstra + Profil Guru + panel dashboard admin | `b83c841e` | 192 lolos |

Total akhir: **192 test lolos, 0 gagal, 967 assertions**.

## Fase A — Profil Sekolah + Pengaturan Cetak + peran baru

- Profil sekolah disimpan sebagai perluasan `SystemSetting` (key/value JSON, tanpa tabel
  baru): `school_name`, `school_npsn`, `school_address`, `school_city`, `school_phone`,
  `school_logo`, `principal_name`, `principal_nip`; kunci cetak: `print_city`,
  `print_date`, `print_watermark`. Service: `app/Services/SchoolProfileService.php`.
- Halaman admin: `GET/PUT /admin/school-profile` (termasuk upload logo, maks 2 MB,
  tersimpan di `storage/app/public/school/`) dan `GET/PUT /admin/print-settings`
  (kota & tanggal penerbitan, nama & NIP kepala sekolah, watermark on/off).
- PDF rapor memakai data profil (logo di kop, NPSN + alamat, tanda tangan Kepala
  Sekolah berisi nama + NIP, baris kota/tanggal penerbitan, watermark nama sekolah).
- Peran baru: `kepala_sekolah` (`GET /kepala_sekolah/dashboard` + kunci rapor terbit
  via `POST /kepala_sekolah/report-cards/{reportCard}/lock`) dan `waka_kurikulum`
  (`GET /waka_kurikulum/dashboard`, pemantauan saja). Dashboard memakai
  `MonitoringService` (status nilai per guru–mapel, kelengkapan rapor per kelas).
- Akun demo: `kepala@smk.test` / `waka@smk.test`, password `password`.

## Fase B — Sikap Sosial & Spiritual

- Tabel baru `student_attitudes` (migration `2026_01_07`): student, academic_year,
  semester, jenis (sosial/spiritual), predikat (Sangat Baik/Baik/Cukup/Kurang),
  deskripsi. Model `StudentAttitude` dengan relasi student, academicYear, semester.
- Wali mengisi bulk per kelas (`GET/POST /wali/attitudes`, hanya kelasnya sendiri,
  diblokir bila rapor sudah diterbitkan/dikunci).
- Tampil di preview rapor wali dan PDF rapor (dibaca live per periode, bukan snapshot).
- Admin: rekap sikap per kelas (`GET /admin/attitudes/recap`).
- Sikap TIDAK menjadi komponen kelengkapan rapor (tidak memblokir pengajuan).

## Fase C — P5 / Kokurikuler

- Tabel baru `p5_projects` (tema, dimensi Profil Pelajar Pancasila, academic_year,
  semester, class, coordinator) dan `p5_scores` (project, student, capaian, deskripsi).
- Admin CRUD proyek P5 (`/admin/p5-projects`, termasuk menetapkan koordinator).
- Koordinator: `GET /guru/p5` ("P5 Saya"), isi capaian bulk per proyek.
- Tampil di preview rapor wali dan PDF rapor (live per periode). P5 TIDAK menjadi
  komponen kelengkapan rapor.

## Fase D — Kenaikan Kelas & Kelulusan

- Tabel baru `promotion_decisions` (migration `2026_01_10`): student, class,
  academic_year, semester, status (naik/tidak_naik), decided_by.
- Kolom baru `students.is_alumni` + `students.alumni_year` (migration `2026_01_11`).
- Wali menetapkan naik/tidak naik (`GET/POST /wali/promotions`), hanya semester Genap.
- Operator: `GET /admin/promotions` (Langkah 1: luluskan XII ke Alumni; Langkah 2:
  naikkan kelas via pemetaan asal → tujuan), `GET /admin/alumni` (read-only).
- Aturan urutan ditegakkan 2 lapis (UI + backend): naikkan ditolak bila masih ada siswa
  XII berkeputusan naik yang belum diluluskan. Semua perubahan dalam satu transaksi;
  pemetaan berantai (X→XI, XI→XII) disusun dulu agar siswa tidak pindah dua kali.
- Status tampil di preview rapor wali dan PDF rapor. Keputusan bukan komponen
  kelengkapan rapor.

## Fase E — Backup & Restore + Salin Mengajar + Impor CSV

- Backup: `GET admin/backup/download` — satu file JSON
  `backup-rapor-YYYY-MM-DD-HHmm.json` berisi 35 tabel aplikasi + penanda format
  (`app: e-rapor-smk`, `version: 1`, `exported_at`). Tanpa cache/jobs/sessions/
  password_reset_tokens. Hash password ikut tercadangkan, tidak pernah ditampilkan di UI.
- Restore: `POST admin/backup/restore` — validasi format, impor dalam satu transaksi
  (FK dimatikan, kosongkan lalu isi ulang per tabel), ditolak bila tidak ada akun admin
  aktif dalam cadangan.
- Salin Mengajar: `GET/POST admin/teaching-assignments/copy` — salin penugasan dari
  satu semester ke semester lain (lintas tahun ajaran diizinkan), duplikat dilewati,
  ringkasan jumlah tersalin/dilewati.
- Impor CSV (tanpa dependensi baru): template + unggah siswa (nis, nisn, nama, gender,
  kelas, tanggal_lahir, alamat) dan guru (nip, nama, gender, telepon, alamat); laporan
  per baris gagal + alasan. Halaman `GET admin/import`.
- Semua hanya untuk admin (403 untuk peran lain).

## Fase F — Cetak tambahan + Pembina Ekstra + Profil Guru + dashboard admin

- Cetak tambahan (DomPDF): Sampul (`wali/report-cards/{reportCard}/cover`),
  Identitas (`.../identity`), Transkrip (`.../transcript`, hanya bila rapor sudah
  terbit/dikunci, dari snapshot lintas semester).
- Leger kelas: `GET admin/ledger` (HTML + tombol cetak + unduh PDF) dan
  `GET admin/ledger/pdf` (PDF landscape). Sumber data: bila SEMUA siswa kelas sudah
  punya rapor diterbitkan/dikunci dipakai snapshot `report_card_subjects`; bila belum,
  dipakai nilai akhir live (`AssessmentService::finalScore`). Label sumber ditampilkan.
- Pembina Ekstrakurikuler: kolom baru `extracurriculars.supervisor_id` (migration
  `2026_01_12`); admin menetapkan pembina (`/admin/extracurriculars`); pembina menilai
  lewat `/guru/ekstra-binaan` (tabel `student_extracurriculars` yang sama dengan
  pengisian wali — yang terakhir menyimpan menang, tercatat di `activity_logs`).
- Profil Guru: `/guru/profil` dan `/wali/profil` — ubah nama, telepon, alamat; ganti
  password (password lama wajib benar).
- Dashboard admin: panel "Status Pengiriman Nilai" (agregasi `MonitoringService` Fase A:
  draft vs terkirim vs dikunci per mapel) dan "Aktivitas Guru Terakhir" (10 aktivitas
  terbaru dari `activity_logs`).

## Penyesuaian untuk SMK (berbeda dari RDM)

| RDM (Madrasah) | Aplikasi ini (SMK) |
|---|---|
| Kepala Madrasah | Kepala Sekolah |
| P5RA (Profil Pelajar Pancasila dan Rahmatan lil Alamin) | P5 (Projek Penguatan Profil Pelajar Pancasila) |
| KKM (Kurikulum 2013) | KKTP (Kurikulum Merdeka, tetap dipakai) |
| Kirim nilai ke server pusat Kemenag | Tidak dibuat (khusus Kemenag) |
| Sistem Paket / SKS (MA) | Paket saja |
| NSM (Nomor Statistik Madrasah) | Tidak dipakai; hanya NPSN |
| Kelas 1–6 / 7–9 / 10–12 | Kelas X, XI, XII |
| — | PKL + UKK tetap ada (khas SMK, tidak ada di RDM) |
| Sinkron EMIS | Tidak dibuat (tidak ada EMIS di SMK) |

## Yang sengaja TIDAK dibuat

- Kirim nilai ke server pusat (khusus Kemenag).
- Impor Excel (`.xlsx`) — impor memakai CSV tanpa dependensi baru.
- Foto profil guru — kolom `photo` sudah ada di tabel teachers, tapi upload + tampil
  foto belum dipakai di halaman mana pun.
- Rute "Ekstra Binaan" untuk peran `wali_kelas` — wali yang juga pembina tetap bisa
  mengisi nilai ekskul lewat halaman Ekstrakurikuler wali yang sudah ada.
- Aksi "batalkan kelulusan/naik kelas" — belum ada ketentuan sekolah untuk itu.
- Rekap P5 per kelas untuk admin — admin sudah bisa melihat daftar proyek + koordinator
  dan nilai P5 per siswa di detail rapor.
- Sikap, P5, dan keputusan kenaikan sebagai komponen kelengkapan rapor yang memblokir
  pengajuan — ketiganya hanya tampil di preview/PDF; pengajuan tetap berjalan tanpanya.
- Peran Staf Tata Usaha terpisah — dicakup oleh admin.

## Pertanyaan terbuka (butuh keputusan user/sekolah)

- Nama resmi Kepala Sekolah + NIP untuk tanda tangan PDF rapor — saat ini kosong;
  admin dapat mengisinya di Profil Sekolah / Pengaturan Cetak.
- Logo sekolah belum diunggah — sidebar memakai ikon bawaan; admin dapat mengunggahnya
  di Profil Sekolah.
- Apakah sikap, P5, dan keputusan kenaikan nantinya harus menjadi syarat kelengkapan
  rapor (memblokir pengajuan)? Saat ini tidak.
- Apakah foto profil guru perlu ditampilkan (mis. di dashboard guru dan daftar guru)?
