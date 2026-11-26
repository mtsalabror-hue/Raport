# STAGE 13 — PANDUAN DEPLOYMENT PRODUKSI (PERSIAPAN)

> Status: **persiapan dokumen saja. Aplikasi BELUM di-deploy** atas permintaan pengguna
> ("untuk deploy jangan"). Dokumen ini adalah panduan yang harus diikuti saat deployment
> benar-benar dijalankan nanti. Jangan menjalankan langkah deployment tanpa persetujuan
> pengguna dan tanpa menyelesaikan ceklist di bawah.

## 1. Ringkasan aplikasi

| Item | Nilai |
|---|---|
| Framework | Laravel 13 (`laravel/framework ^13.17`) |
| PHP | ^8.3 (diuji di PHP 8.3.6) |
| Database produksi | PostgreSQL (wajib; pengujian lokal memakai SQLite) |
| PDF | barryvdh/laravel-dompdf ^3.1 |
| Frontend | Blade + Tailwind CDN (45 view); hanya `welcome.blade.php` memakai `@vite` |
| Queue | Tidak dipakai (tidak ada job/dispatch di kode) |
| Mail | Tidak dipakai (tidak ada pengiriman email di kode) |
| Upload file | Tidak ada fitur upload di UI |
| Storage | Tidak ada file yang disimpan aplikasi |
| Migrations | 9 file (lihat `database/migrations/`) |
| Session | Database (default `config/session.php`, lifetime 120 menit) |
| Cache | Database (tabel cache bawaan Laravel) |

## 2. Ceklist sebelum deployment

- [ ] Hasil Stage 12 disetujui (tidak ada bug Critical/High terbuka).
- [ ] Versi kode tercatat: commit terakhir + `composer.lock` yang sama dengan yang diuji.
- [ ] PostgreSQL produksi tersedia, dengan database dan role khusus aplikasi (hak minimum, BUKAN superuser).
- [ ] Domain dan sertifikat HTTPS tersedia.
- [ ] Backup dan prosedur restore sudah diuji di staging.
- [ ] `.env` produksi disiapkan (lihat bagian 3); file `.env` TIDAK masuk version control.
- [ ] Tidak ada akun/password demo yang aktif di produksi (akun demo hanya di seeder lokal).
- [ ] Pengujian staging di PostgreSQL lolos (khususnya kolom `jsonb` + index GIN dan migrasi).

## 3. Konfigurasi `.env` produksi

```env
APP_NAME="E-Rapor SMK IT AL-ABROR"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<domain-sekolah>
APP_KEY=<kunci-yang-dibuat-dengan-php-artisan-key-generate>   # jangan pakai kunci .env lokal

DB_CONNECTION=pgsql
DB_HOST=<host-postgresql>
DB_PORT=5432
DB_DATABASE=<nama-database>
DB_USERNAME=<role-aplikasi>
DB_PASSWORD=<password-role>        # jangan taruh di source code

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database           # tidak dipakai aplikasi, tetap aman disetel
```

Catatan:
- `APP_DEBUG=false` wajib. Debug true di produksi membocorkan stack trace dan konfigurasi.
- `APP_KEY` harus dibuat baru untuk produksi (`php artisan key:generate --show`, lalu salin ke `.env`); jangan menyalin kunci dari `.env` lokal.
- Password database hanya ada di `.env` server, tidak pernah di repository.

## 4. Langkah deployment

1. Tarik kode dari repository ke server (branch yang sudah diuji).
2. `composer install --no-dev --optimize-autoloader`
3. Salin/isi `.env` produksi (bagian 3). Jangan commit file ini.
4. `php artisan key:generate` (hanya bila APP_KEY belum diisi).
5. **Backup database dulu** (bagian 5), lalu:
6. `php artisan migrate --force` — jalankan migrasi yang pending saja.
   **JANGAN PERNAH** menjalankan `migrate:fresh` atau `migrate:refresh` di produksi.
7. `npm install && npm run build` — diperlukan agar halaman utama (`welcome.blade.php`) tampil dengan styling Vite. Halaman aplikasi memakai Tailwind CDN sehingga tetap berfungsi tanpa build, tetapi landing page akan tampil tanpa style.
8. Optimasi Laravel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
9. Atur kepemilikan/izin direktori `storage/` dan `bootstrap/cache/` agar bisa ditulis web server.
10. Pastikan web server mengarah ke direktori `public/` (bukan root proyek).
11. Pastikan HTTPS aktif dan redirect HTTP → HTTPS.
12. Jalankan smoke test (bagian 6).

## 5. Backup dan restore

Backup PostgreSQL (jadwalkan harian, simpan di lokasi terpisah dari server aplikasi):

```bash
pg_dump -U <role-aplikasi> -h <host> <nama-database> > erapor-$(date +%Y%m%d).sql
```

Restore (hanya di staging atau saat pemulihan darurat, setelah backup kondisi saat ini):

```bash
psql -U <role-aplikasi> -h <host> <nama-database> < erapor-YYYYMMDD.sql
```

- Retensi yang disarankan: harian 7 hari, mingguan 4 minggu, bulanan 3 bulan (sesuaikan kebijakan sekolah).
- Uji restore secara berkala di lingkungan non-produksi; backup yang belum pernah diuji restore-nya dianggap belum ada.
- Jangan menyimpan kredensial backup di repository.
- RPO/RTO: tetapkan bersama sekolah (belum disepakati).

## 6. Smoke test setelah deploy

- [ ] Halaman utama/login terbuka lewat HTTPS.
- [ ] Login tiap role (admin, guru, wali kelas) berhasil dan masuk dashboard yang benar.
- [ ] Dashboard tiap role menampilkan data.
- [ ] Koneksi database: buat dan baca satu data uji yang aman (lalu hapus).
- [ ] Hak akses: guru tidak bisa membuka route admin/wali dan sebaliknya (403).
- [ ] Input nilai guru → submit → tampil di rekap wali.
- [ ] Unduh PDF rapor yang sudah diterbitkan.
- [ ] Log error kosong dari error baru (`storage/logs/laravel.log`).

## 7. Rollback

- **Kode:** kembalikan ke commit sebelumnya (`git checkout <commit-sebelumnya>`), lalu ulangi langkah 2, 8, dan 9 dari bagian 4. Jangan menjalankan migrasi rollback otomatis tanpa memeriksa migrasi mana yang berubah.
- **Database:** bila migrasi terakhir bermasalah dan belum ada data baru yang penting, `php artisan migrate:rollback --step=1 --force` setelah backup. Bila sudah ada data baru, pulihkan dari backup (bagian 5) dan catat data yang hilang.
- **Konfigurasi:** simpan salinan `.env` produksi yang terakhir berfungsi di lokasi aman di luar repository.

## 8. Troubleshooting

| Gejala | Kemungkinan penyebab | Tindakan |
|---|---|---|
| 500 setelah deploy | `APP_KEY` kosong / cache lama | Isi APP_KEY, jalankan `php artisan config:clear` lalu `config:cache` |
| Migrasi gagal | Koneksi PostgreSQL salah / hak role kurang | Periksa `DB_*` di `.env`; pastikan role punya CREATE/ALTER |
| Halaman utama tanpa style | `npm run build` belum dijalankan | Jalankan build; periksa `public/build/` |
| Login gagal terus | Session/cache belum termigrasi | Pastikan tabel `sessions` dan `cache` sudah termigrasi |
| PDF gagal dibuat | Font/izin direktori storage | Periksa `storage/logs/laravel.log`; pastikan `storage/` bisa ditulis |
| 419 saat submit form | Sesi kedaluwarsa / APP_URL salah | Periksa `APP_URL` dan `SESSION_DOMAIN` |

## 9. Infrastruktur

- Web server + PHP 8.3 yang kompatibel dengan Laravel 13.
- PostgreSQL versi yang didukung Laravel 13.
- HTTPS wajib; firewall membatasi akses ke PostgreSQL (hanya dari server aplikasi).
- Log dan monitoring: pantau `storage/logs/laravel.log` dan log PostgreSQL.
- Tidak perlu queue worker/scheduler (tidak ada queue/scheduler di aplikasi).
