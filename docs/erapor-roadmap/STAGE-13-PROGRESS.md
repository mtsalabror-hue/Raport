# STAGE 13 — DEPLOYMENT — LAPORAN PERSIAPAN

Tanggal: 2026-11-26.

## Status: TIDAK DI-DEPLOY

Atas permintaan eksplisit pengguna ("untuk deploy jangan"), **tidak ada deployment yang
dilakukan** pada tahap ini. Yang dikerjakan adalah persiapan deployment:

1. Panduan deployment lengkap: `docs/erapor-roadmap/STAGE-13-DEPLOYMENT-GUIDE.md`
   (ceklist pra-deployment, konfigurasi `.env` produksi, langkah deployment, backup/restore,
   rollback, smoke test, troubleshooting).
2. Verifikasi fakta aplikasi yang memengaruhi deployment (tanpa mengubah kode):
   - PHP ^8.3, Laravel ^13.17, PostgreSQL untuk produksi.
   - Tidak ada queue, mail, atau upload file di aplikasi (tidak perlu worker/scheduler).
   - Hanya `welcome.blade.php` memakai `@vite`; 45 view memakai Tailwind CDN.
   - 9 migrations; session dan cache memakai database.

## Yang masih diperlukan sebelum deployment benar-benar dijalankan

- Persetujuan pengguna untuk deploy.
- Detail hosting/server (provider, OS, akses) — belum ditentukan pengguna.
- Domain dan sertifikat HTTPS.
- Kredensial PostgreSQL produksi (database + role khusus aplikasi).
- Pengujian staging di PostgreSQL (kolom `jsonb` + index GIN, perilaku migrasi).
- Uji restore backup di lingkungan non-produksi.
- Keputusan RPO/RTO bersama sekolah.

Stage 14 belum dimulai.
