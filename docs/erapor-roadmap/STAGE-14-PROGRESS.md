# STAGE 14 — MAINTENANCE AND FINAL DOCUMENTATION — LAPORAN

Tanggal: 2026-11-26.

## 1. Dokumentasi teknis

- `docs/TECHNICAL.md` (baru): arsitektur, struktur folder, ERD, kamus data 28 tabel,
  daftar 9 migrations, matriks role dan permission, alur penilaian, alur revisi nilai,
  alur rapor (termasuk pemetaan status dokumen ke enum tabel), konfigurasi dan
  layanan eksternal, ringkasan keamanan.
- `docs/erapor-roadmap/STAGE-13-DEPLOYMENT-GUIDE.md` (dari Stage 13): panduan
  deployment, backup/restore, rollback, smoke test, troubleshooting.

## 2. Panduan pengguna (mengikuti antarmuka aktual)

- `docs/PANDUAN-ADMIN.md` — data master, akun pengguna, penugasan guru/wali kelas,
  monitoring, rapor, PKL/UKK.
- `docs/PANDUAN-GURU.md` — komponen penilaian, input nilai, deskripsi, kirim/kunci,
  revisi, presensi, PKL/UKK terbatas.
- `docs/PANDUAN-WALI.md` — dashboard kelas, rekap nilai/presensi (read-only),
  catatan/prestasi/ekskul, kelengkapan, preview/pengajuan, PKL/UKK (hanya-baca).

## 3. Perbaikan yang ditemukan saat pemeriksaan akhir (Stage 14)

Pemeriksaan akhir menemukan dua celah fungsional dari tahap sebelumnya (Stage 5/6)
yang membuat aplikasi tidak bisa dipakai penuh tanpa akses database langsung.
Keduanya diperbaiki pada tahap ini:

| ID | Temuan | Perbaikan |
|---|---|---|
| F1 | Tidak ada UI untuk penugasan guru dan wali kelas (teaching_assignments, homeroom_assignments). Tanpa penugasan, guru tidak bisa input nilai dan wali tidak bisa membuka kelasnya. | `Admin/TeachingAssignmentController` + `Admin/HomeroomAssignmentController` beserta view dan route resource; validasi duplikasi (satu penugasan sama tidak boleh ganda; satu kelas satu wali per periode). 4 tes baru. |
| F2 | Tidak ada UI untuk akun pengguna (users). Hanya seeder yang membuat 3 akun demo; admin tidak bisa membuat akun guru/wali, menghubungkan akun ke data guru, atau reset password. | `Admin/UserController` beserta view dan route resource; password di-hash, konfirmasi password, nonaktifkan akun, larangan hapus akun sendiri. 3 tes baru. |

File baru: 3 controller, 9 view, 1 file tes (`tests/Feature/Admin/AssignmentAndUserTest.php`, 7 tes).

## 4. Backup dan restore

- Prosedur terdokumentasi di `STAGE-13-DEPLOYMENT-GUIDE.md` bagian 5 (pg_dump/psql,
  jadwal yang disarankan, retensi, uji restore berkala).
- RPO/RTO belum disepakati dengan sekolah — harus ditetapkan sebelum produksi.
- Tidak ada backup yang pernah dibuat/diuji (belum ada database produksi).

## 5. Monitoring dan pemeliharaan

- Pantau `storage/logs/laravel.log` dan log PostgreSQL.
- Tinjau `activity_logs` secara berkala untuk aktivitas yang tidak biasa.
- Tinjau akun pengguna dan hak akses setiap awal semester.
- Periksa kapasitas penyimpanan dan performa query secara berkala.
- Terapkan pembaruan keamanan (composer update) secara terencana di staging dulu.
- Dokumentasikan setiap bug dan perubahan di `docs/erapor-roadmap/`.

## 6. Pemeriksaan akhir

| Pemeriksaan | Hasil |
|---|---|
| Seluruh menu yang ditampilkan memiliki fungsi | Ya (setelah perbaikan F1/F2; semua route admin/guru/wali terdaftar dan teruji) |
| Seluruh data operasional berasal dari PostgreSQL | Ya (produksi); pengujian memakai SQLite |
| Tiga role bekerja sesuai kewenangannya | Ya (121+7 tes; middleware role + pemeriksaan cakupan di controller) |
| Riwayat akademik lintas semester terjaga | Ya (semua query memakai ay+semester eksplisit; riwayat wali lintas semester) |
| Nilai yang terkirim mengikuti workflow revisi | Ya (submit → kunci → revisi dengan persetujuan guru lain) |
| Rapor terbit tersimpan sebagai rekaman historis | Ya (snapshot di report_card_subjects + PDF dari snapshot) |
| Tidak ada akses lintas kelas/penugasan | Ya (diuji: IDOR guru lain, wali kelas lain, lintas role) |
| Dokumentasi sesuai fitur yang benar-benar tersedia | Ya (panduan mengikuti route dan view aktual) |

## 7. Laporan akhir

Lihat `docs/erapor-roadmap/FINAL-REPORT.md`.

Stage 14 adalah tahap terakhir. Tidak ada perubahan besar tambahan tanpa persetujuan pengguna.
