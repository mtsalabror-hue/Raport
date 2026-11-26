# STAGE 14: MAINTENANCE AND FINAL DOCUMENTATION

## Tujuan

Menyelesaikan dokumentasi operasional dan prosedur pemeliharaan aplikasi E-Rapor SMK.

## Dokumentasi teknis

Perbarui dan lengkapi:
1. Arsitektur aplikasi.
2. Struktur folder.
3. ERD terbaru.
4. Kamus data dan relasi PostgreSQL.
5. Daftar migrations.
6. Daftar role dan permission.
7. Alur penilaian.
8. Alur revisi nilai.
9. Alur rapor.
10. Konfigurasi integrasi dan layanan eksternal jika ada.

## Panduan pengguna

Buat panduan terpisah:
- Admin.
- Guru Mata Pelajaran.
- Wali Kelas.

Panduan harus mengikuti antarmuka aktual, bukan fitur yang direncanakan tetapi belum dibuat.

## Backup dan restore

- Tentukan jadwal backup sesuai kebutuhan operasional sekolah.
- Dokumentasikan backup PostgreSQL.
- Simpan backup di lokasi terpisah dan terlindungi.
- Enkripsi backup jika sesuai kebijakan.
- Tentukan retensi backup.
- Uji restore secara berkala di lingkungan non-produksi.
- Dokumentasikan Recovery Point Objective (RPO) dan Recovery Time Objective (RTO) setelah disepakati.
- Jangan menyimpan kredensial backup di repository.

## Monitoring dan pemeliharaan

- Pantau error aplikasi dan database.
- Pantau kapasitas penyimpanan.
- Pantau performa query.
- Tinjau log keamanan dan audit.
- Terapkan pembaruan keamanan terencana.
- Dokumentasikan bug dan perubahan.
- Lakukan pemeriksaan integritas backup.
- Tinjau akun pengguna dan hak akses secara berkala.

## Pemeriksaan akhir

Pastikan:
- Seluruh menu yang ditampilkan memiliki fungsi.
- Seluruh data operasional berasal dari PostgreSQL.
- Tiga role bekerja sesuai kewenangannya.
- Riwayat akademik lintas semester terjaga.
- Nilai yang terkirim mengikuti workflow revisi.
- Rapor terbit tersimpan sebagai rekaman historis.
- Tidak ada akses lintas kelas/penugasan.
- Dokumentasi sesuai fitur yang benar-benar tersedia.

## Laporan akhir

Sediakan:
- Daftar fitur selesai.
- Daftar fitur yang belum tersedia.
- Hasil QA dan security.
- Status deployment.
- Status backup/restore.
- Risiko tersisa.
- Rekomendasi operasional.
- Pernyataan kesiapan produksi dengan alasan dan batasannya.

Jangan menyatakan sistem siap produksi jika masih ada masalah kritis atau prosedur backup/restore belum diuji.

## Batas tahap

Stage 14 adalah tahap terakhir. Sampaikan laporan final dan dokumentasi, jangan melakukan perubahan besar tambahan tanpa persetujuan.
