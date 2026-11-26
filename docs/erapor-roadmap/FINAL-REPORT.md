# LAPORAN AKHIR — E-Rapor SMK (Stage 3–14)

Tanggal: 2026-11-26. Repository: https://github.com/mtsalabror-hue/Raport (branch `main`).
Versi aplikasi: Laravel 13, PHP ^8.3, PostgreSQL (produksi), SQLite (pengujian).
Pengujian akhir: **128 tes lolos, 0 gagal, 578 assertions** (`php artisan test`).

## 1. Daftar fitur selesai

### Stage 3 — Auth & RBAC
Login/logout, 3 role (Admin, Guru Mata Pelajaran, Wali Kelas), middleware `role:`,
session regenerate saat login, invalidate saat logout, rate limit login 10/menit.

### Stage 4 — Dashboard
Dashboard per role dengan ringkasan data dari database.

### Stage 5 — Data master
CRUD jurusan, guru, siswa, kelas, mata pelajaran, tahun ajaran, semester,
kurikulum; pencarian dan pagination 10–15 baris.

### Stage 6 — Kurikulum dan penugasan
Kurikulum, capaian pembelajaran (CP), tujuan pembelajaran (TP), penugasan guru
mengajar, penugasan wali kelas, manajemen akun pengguna (baru di Stage 14, lihat
bagian 3).

### Stage 7 — Penilaian
Komponen penilaian (formatif/sumatif/praktik/proyek, bobot, KKTP), input nilai
tunggal dan massal dengan deteksi konflik, nilai akhir tertimbang (komponen kosong
dikecualikan), deskripsi capaian, submit, kunci, revisi dengan persetujuan guru
lain, riwayat, audit log. 37 tes.

### Stage 8 — Presensi dan rekapitulasi
Input presensi massal per kelas/tanggal (H/S/I/A), satu catatan per siswa per
tanggal (unique constraint + validasi), koreksi dengan audit trail, riwayat,
rekap per siswa/kelas/semester, rekap akademik (nilai, ketuntasan KKTP),
monitoring nilai belum dikirim. 12 tes.

### Stage 9 — Workflow wali kelas
Dashboard kelas, data siswa, rekap nilai/presensi (read-only), catatan wali,
prestasi, ekstrakurikuler (+ master), monitoring kelengkapan (4 komponen wajib
sementara + tautan kekurangan), preview rapor, pengajuan rapor, riwayat akademik
lintas semester. 15 tes.

### Stage 10 — Manajemen rapor
Pemeriksaan, persetujuan/pengembalian (alasan wajib), penerbitan dengan snapshot
immutable (nilai akhir + deskripsi), kunci rapor, PDF A4 dari snapshot (DomPDF),
riwayat status, cegah terbit ganda, koreksi pasca-terbit ditolak. 19 tes.

### Stage 11 — PKL dan UKK
PKL: penempatan, mitra industri, periode, pembimbing, monitoring, penilaian per
aspek, rekap; tumpang tindih ditolak. UKK: jadwal, peserta, penguji, input hasil,
rekap (tanpa label lulus/tidak lulus). Nilai PKL/UKK tidak masuk rapor. 23 tes.

### Stage 12 — QA, keamanan, audit
15 tes keamanan (IDOR, mass assignment, XSS, SQL injection, unique/FK, CSRF,
rate limiting, audit log); matriks QA 25 kasus di `STAGE-12-PROGRESS.md`.
Perbaikan: mass-assignment gap (Student/Teacher/Subject), route admin/students
dan admin/classes yang hilang, relasi SchoolClass yang hilang, rate limiting login.

### Stage 13 — Persiapan deployment (TANPA DEPLOY)
Panduan deployment lengkap di `STAGE-13-DEPLOYMENT-GUIDE.md`. **Aplikasi belum
di-deploy** atas permintaan pengguna.

### Stage 14 — Dokumentasi akhir
`docs/TECHNICAL.md`, `docs/PANDUAN-ADMIN.md`, `docs/PANDUAN-GURU.md`,
`docs/PANDUAN-WALI.md`, `STAGE-14-PROGRESS.md`, dan laporan ini. Ditambah
perbaikan F1/F2 (penugasan dan akun pengguna, lihat bagian 3).

## 2. Daftar fitur yang belum tersedia

- **Cetak rapor massal per kelas** (dokumen desain menyebutkannya; baru tersedia
  per siswa).
- **Export Excel** (dokumen desain menyebutkannya; belum diimplementasikan).
- **Notifikasi** (email/pesan; tidak ada pengiriman email di aplikasi).
- **Import nilai dari file** (input massal lewat form tersedia; import file belum).
- **Manajemen peran/permission granular** (role tetap: admin/guru/wali_kelas).
- **Foto guru/siswa lewat UI** (kolom `photo` ada, tetapi tidak ada upload).
- **Arsip semester otomatis** (data terisolasi per semester, tetapi tidak ada menu
  arsip khusus).

## 3. Perbaikan tambahan di Stage 14

| ID | Temuan | Perbaikan |
|---|---|---|
| F1 | Tidak ada UI penugasan guru/wali kelas (Stage 6) | Controller + view + route untuk teaching-assignments dan homeroom-assignments |
| F2 | Tidak ada UI akun pengguna (Stage 3/5) | Controller + view + route untuk users (buat, edit, nonaktifkan, reset password, hubungkan ke data guru) |

## 4. Hasil QA dan keamanan

- 128 tes Feature lolos, 0 gagal, 578 assertions (Auth, master data, penilaian,
  presensi, wali kelas, rapor, PKL/UKK, keamanan, penugasan, akun pengguna).
- Matriks QA lengkap: `STAGE-12-PROGRESS.md` bagian 3 (25 kasus).
- Tidak ada bug Critical/High yang terbuka.
- Sisa temuan Medium (utang teknis): akses guru PKL berbasis pencocokan nama
  supervisor (bukan FK pembimbing); penguji UKK disimpan sebagai teks di awal
  kolom `note`; daftar 4 komponen wajib kelengkapan rapor masih keputusan sementara.

## 5. Status deployment

**BELUM DI-DEPLOY.** Atas permintaan pengguna, Stage 13 hanya menyiapkan panduan.
Yang masih diperlukan sebelum deploy: persetujuan pengguna, detail hosting/server,
domain + HTTPS, kredensial PostgreSQL produksi, pengujian staging di PostgreSQL,
uji restore backup, keputusan RPO/RTO.

## 6. Status backup/restore

- Prosedur terdokumentasi (`STAGE-13-DEPLOYMENT-GUIDE.md` bagian 5).
- Belum ada backup yang dibuat atau diuji (belum ada database produksi).
- RPO/RTO belum disepakati dengan sekolah.

## 7. Risiko tersisa

1. **Keputusan sekolah yang belum dikonfirmasi** (paling penting): bobot komponen
   resmi (draft: formatif 30 / sumatif 40 / praktik 20 / proyek 10, KKTP 75),
   siapa yang menyetujui revisi nilai dan rapor, apakah predikat A/B/C/D tampil
   di rapor, apakah nilai PKL/UKK masuk rapor dan formulanya, ambang kelulusan
   UKK, daftar baku aspek dan rubrik PKL, nama Kepala Sekolah penandatangan + NIP,
   komponen wajib kelengkapan rapor, ambang kehadiran minimum.
2. **Pengujian hanya di SQLite.** Perilaku khusus PostgreSQL (JSONB + index GIN,
   migrasi) harus diuji di staging sebelum produksi.
3. **Tampilan ponsel belum diperiksa secara visual** (hanya class Tailwind responsif).
4. **Akses guru PKL berbasis nama** (lihat bagian 4) — rapuh bila nama salah eja.
5. **Akun demo seeder** (`admin@smk.test` / `guru@smk.test` / `wali@smk.test`,
   password `password`) harus dihapus/diganti sebelum produksi.
6. **Tidak ada backup yang teruji** sampai staging disiapkan.

## 8. Rekomendasi operasional

1. Minta konfirmasi sekolah atas daftar di risiko #1 sebelum semester berjalan.
2. Siapkan staging PostgreSQL; jalankan migrasi dan seluruh smoke test di sana.
3. Buat akun admin produksi yang baru; hapus akun demo.
4. Buat tahun ajaran/semester baru, data master, akun pengguna, lalu penugasan
   guru dan wali kelas sebelum guru mulai menginput nilai.
5. Jadwalkan backup harian dan uji restore setiap bulan.
6. Tinjau audit log dan akun pengguna setiap awal semester.

## 9. Pernyataan kesiapan produksi

**Sistem BELUM dinyatakan siap produksi.** Alasannya:

- Deployment belum dilakukan dan belum ada pengujian staging di PostgreSQL.
- Backup/restore belum pernah diuji.
- Keputusan sekolah (bobot, predikat, penandatangan, ambang UKK, komponen wajib)
  masih terbuka, dan beberapa di antaranya memengaruhi isi rapor yang diterbitkan.
- Akun demo masih ada di seeder.

Yang sudah siap: seluruh fitur Stage 3–14 terimplementasi dan teruji (128 tes
lolos), tidak ada bug Critical/High terbuka, dokumentasi teknis dan panduan
pengguna lengkap, dan panduan deployment + rollback tersedia. Setelah staging
PostgreSQL lolos, backup teruji, akun demo dihapus, dan keputusan sekolah
dikonfirmasi, sistem dapat dinyatakan siap produksi dengan batasan di atas.
