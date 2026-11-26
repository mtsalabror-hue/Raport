# E-Rapor SMK: Implementation Roadmap

Folder ini berisi instruksi implementasi Stage 3 sampai Stage 14 untuk aplikasi E-Rapor SMK.

## Konteks proyek yang wajib dipertahankan

- Backend: Laravel 12
- Frontend: Laravel Blade, Tailwind CSS, Alpine.js
- Database utama: PostgreSQL
- Authentication dan authorization: Laravel Authentication + RBAC
- Grafik: Chart.js
- Ikon: Lucide Icons
- Export Excel: Laravel Excel
- PDF: DomPDF atau solusi kompatibel
- Tiga role: Admin, Guru Mata Pelajaran, dan Wali Kelas

Tahap 1 telah menghasilkan `docs/DESIGN.md` dan `docs/TECHNICAL_DESIGN.md`.
Tahap 2 telah menjalankan migrations dan seeders pada PostgreSQL. Tiga akun demo telah dibuat. Model `SystemSetting` menggunakan array cast untuk kolom JSONB. Pertahankan hasil tersebut.

## Daftar tahap

| Stage | File | Fokus |
|---|---|---|
| 3 | `STAGE-03-AUTH-RBAC.md` | Authentication dan RBAC |
| 4 | `STAGE-04-DASHBOARD.md` | Layout dan dashboard |
| 5 | `STAGE-05-MASTER-DATA.md` | CRUD data master |
| 6 | `STAGE-06-CURRICULUM.md` | Kurikulum dan penugasan |
| 7 | `STAGE-07-ASSESSMENT.md` | Sistem penilaian |
| 8 | `STAGE-08-ATTENDANCE.md` | Presensi dan rekapitulasi |
| 9 | `STAGE-09-HOMEROOM.md` | Workflow wali kelas |
| 10 | `STAGE-10-REPORT-CARD.md` | Manajemen rapor |
| 11 | `STAGE-11-PKL-UKK.md` | PKL dan UKK |
| 12 | `STAGE-12-TESTING-SECURITY.md` | QA, keamanan, dan audit |
| 13 | `STAGE-13-DEPLOYMENT.md` | Deployment |
| 14 | `STAGE-14-MAINTENANCE.md` | Pemeliharaan dan dokumentasi |

## Cara menggunakan

1. Letakkan folder ini di dalam repository proyek, sehingga lokasinya menjadi `docs/erapor-roadmap/`.
2. Baca file tahap yang akan dikerjakan.
3. Berikan kepada Antigravity hanya instruksi untuk tahap yang sedang disetujui.
4. Minta Antigravity memeriksa kondisi repository sebelum mengubah kode.
5. Setelah selesai, periksa laporan dan hasil pengujian.
6. Lanjutkan ke tahap berikutnya hanya setelah menyetujui hasil tahap berjalan.

Contoh permintaan:

> Baca `docs/erapor-roadmap/STAGE-03-AUTH-RBAC.md` dan kerjakan Stage 3 sesuai instruksi. Periksa repository terlebih dahulu, jangan mengulang pekerjaan yang sudah ada, jalankan pengujian, berikan laporan lengkap, lalu berhenti. Jangan mulai Stage 4 tanpa persetujuan saya.

## Aturan global yang berlaku untuk semua tahap

- Kerjakan hanya tahap yang secara eksplisit diminta.
- Jangan menjalankan seluruh roadmap sekaligus.
- Jangan menghapus atau menimpa pekerjaan yang sudah berfungsi tanpa alasan teknis dan persetujuan.
- Pertahankan Laravel 12 dan PostgreSQL.
- Jangan mengganti database ke MySQL atau SQLite untuk implementasi utama.
- Jangan menaruh kredensial rahasia di source code atau dokumentasi.
- Jangan menjalankan `php artisan migrate:fresh` pada database yang berisi data penting atau produksi.
- Gunakan migrations sebagai sumber kebenaran struktur database.
- Gunakan transaksi database untuk proses multi-langkah yang harus atomik.
- Terapkan otorisasi di backend, bukan hanya menyembunyikan menu.
- Jangan menggunakan dummy/static data sebagai pengganti integrasi database. Seeder hanya untuk data development/demo.
- Jangan mengklaim fitur selesai sebelum diuji.
- Jika ada konflik antara instruksi tahap ini dan `docs/DESIGN.md` atau `docs/TECHNICAL_DESIGN.md`, jelaskan konflik dan minta keputusan. Jangan diam-diam mengubah aturan bisnis.
- Jangan membuat asumsi tentang kebijakan akademik sekolah, formula nilai, format rapor, atau persetujuan pejabat jika belum ditetapkan. Buat konfigurasi atau tandai sebagai keputusan yang memerlukan konfirmasi.

## Format laporan wajib setelah setiap tahap

1. Ringkasan pekerjaan.
2. File yang dibuat/diubah.
3. Perubahan database (jika ada).
4. Fitur yang selesai.
5. Hak akses dan validasi yang diterapkan.
6. Perintah yang dijalankan.
7. Hasil pengujian, termasuk jumlah lulus/gagal.
8. Bug atau risiko yang tersisa.
9. Langkah manual yang masih diperlukan.
10. Pernyataan bahwa tahap berikutnya belum dimulai.

## Kriteria selesai

Sebuah tahap hanya dianggap selesai apabila implementasi sesuai dokumen desain, seluruh fungsi dalam cakupan tahap berjalan, pengujian relevan lulus, tidak ada masalah kritis yang belum diselesaikan, dan laporan disampaikan. Jika ada bagian terblokir, tandai belum selesai dan jelaskan penyebabnya.
