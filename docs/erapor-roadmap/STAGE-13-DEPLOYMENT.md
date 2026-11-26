# STAGE 13: PRODUCTION DEPLOYMENT

## Tujuan

Persiapkan dan deploy aplikasi E-Rapor ke lingkungan produksi secara aman.

## Pemeriksaan sebelum deployment

- Pastikan hasil Stage 12 telah disetujui.
- Pastikan versi kode dan dependency tercatat.
- Pastikan konfigurasi PostgreSQL produksi tersedia.
- Pastikan backup dan prosedur restore diuji.
- Pastikan domain dan sertifikat HTTPS tersedia.
- Pastikan file `.env` produksi tidak masuk version control.
- Pastikan tidak ada akun/password demo aktif di produksi.

## Konfigurasi aplikasi

- `APP_ENV=production`
- `APP_DEBUG=false`
- Gunakan `APP_KEY` yang aman dan konsisten.
- Atur URL aplikasi.
- Atur koneksi PostgreSQL.
- Atur mail jika notifikasi email digunakan.
- Atur queue/scheduler hanya jika diperlukan.
- Atur storage privat dan publik secara benar.
- Jalankan build asset frontend untuk produksi.
- Jalankan optimasi Laravel sesuai dokumentasi versi yang digunakan.

## Database

- Gunakan database dan role khusus aplikasi dengan hak minimum yang diperlukan.
- Jangan gunakan superuser PostgreSQL untuk koneksi aplikasi.
- Jangan menaruh password dalam source code.
- Jalankan migration yang pending setelah backup dan pemeriksaan.
- Jangan pernah menjalankan `migrate:fresh` pada produksi.
- Gunakan prosedur deployment yang memungkinkan rollback.
- Batasi akses jaringan ke PostgreSQL.

## Infrastruktur

Sesuaikan dengan hosting/server yang dipilih:
- Web server dan PHP runtime yang kompatibel dengan Laravel 12.
- PostgreSQL versi yang didukung.
- HTTPS.
- Firewall.
- Penyimpanan file.
- Log dan monitoring.
- Queue worker/scheduler bila digunakan.

Jangan mengasumsikan provider atau sistem operasi server. Minta detail jika belum ditentukan.

## Smoke test setelah deploy

- Homepage/login.
- Login tiap role.
- Dashboard tiap role.
- Koneksi database.
- Baca/tulis data uji yang aman.
- Hak akses.
- Input akademik sesuai prosedur staging.
- PDF dan file storage.
- Notifikasi jika digunakan.
- Log error.

## Dokumentasi

Buat langkah deployment, konfigurasi, migrasi, rollback, backup, restore, dan troubleshooting.

## Kriteria selesai

Aplikasi berhasil berjalan di lingkungan produksi, HTTPS aktif, koneksi PostgreSQL aman, smoke test lulus, dan prosedur pemulihan tersedia.

## Batas tahap

Jangan mulai Stage 14. Laporkan hasil dan tunggu persetujuan.
