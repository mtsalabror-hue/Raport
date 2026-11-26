# STAGE 12: QUALITY ASSURANCE, SECURITY, AND AUDIT

## Tujuan

Lakukan pengujian menyeluruh terhadap seluruh aplikasi sebelum persiapan produksi.

## Functional testing

Uji minimal:
- Login/logout dan session.
- Hak akses ketiga role.
- CRUD data master.
- Kurikulum dan CP.
- Penugasan guru dan wali kelas.
- Input dan perhitungan nilai.
- Pengiriman dan revisi nilai.
- Presensi dan rekap.
- Catatan wali kelas, prestasi, ekstrakurikuler.
- Pengajuan dan persetujuan rapor.
- Snapshot dan PDF rapor.
- PKL dan UKK.
- Notifikasi dan audit log jika diimplementasikan.

## Database testing

- Foreign key dan referential integrity.
- Unique constraints.
- Indexes.
- JSONB dan GIN index jika digunakan.
- Transaksi dan rollback.
- Konsistensi data historis.
- Perilaku migration pada PostgreSQL.
- Seeder hanya pada environment yang sesuai.
- Backup/restore jika lingkungan memungkinkan.

## Security testing

Periksa:
- Authentication dan authorization.
- CSRF.
- XSS.
- SQL injection.
- IDOR.
- Mass assignment.
- Validasi upload.
- Akses langsung ke file privat.
- Session fixation dan session handling.
- Rate limiting.
- Kebocoran data melalui error/log.
- Penggunaan secret dan konfigurasi environment.

Jangan melakukan pengujian destruktif pada database produksi. Gunakan database testing/staging yang terisolasi.

## UI/UX testing

Uji:
- Desktop, tablet, dan smartphone.
- Navigasi dan semua tombol.
- Validasi form.
- Loading, empty, success, dan error state.
- Aksesibilitas dasar, label, fokus keyboard, dan kontras.
- Tabel besar dan pagination.
- Cetak PDF.

## Audit log

Pastikan aktivitas penting tercatat dengan identitas pelaku, waktu, jenis tindakan, objek terkait, dan metadata yang aman. Jangan memasukkan password, token, atau rahasia ke metadata.

## Laporan QA

Buat matriks:
- Test case.
- Prasyarat.
- Langkah uji.
- Hasil yang diharapkan.
- Hasil aktual.
- Status pass/fail.
- Bukti/log bila relevan.
- Severity dan tindakan perbaikan.

Prioritas bug:
- Critical: kebocoran data, akses tanpa izin, kehilangan/korupsi data.
- High: proses akademik inti gagal atau salah hitung.
- Medium: fitur penting terganggu tetapi ada workaround.
- Low: masalah kosmetik/minor.

Perbaiki dan jalankan ulang tes setelah perubahan.

## Kriteria selesai

Tidak ada bug Critical/High yang terbuka. Seluruh pengujian inti lulus atau memiliki pengecualian yang disetujui dan terdokumentasi.

## Batas tahap

Jangan mulai Stage 13. Laporkan hasil dan tunggu persetujuan.
