# STAGE 7: STUDENT ASSESSMENT SYSTEM

## Tujuan

Implementasikan sistem penilaian yang terhubung dengan siswa, guru, kelas, mata pelajaran, CP, kurikulum, tahun ajaran, dan semester.

## Fitur

1. Pengaturan komponen penilaian.
2. Pengaturan bobot.
3. Input nilai pengetahuan/teori.
4. Input nilai praktik.
5. Input nilai proyek.
6. Perhitungan nilai akhir.
7. Pengaturan KKTP.
8. Deskripsi capaian pembelajaran.
9. Rekap nilai.
10. Pengiriman nilai.
11. Pengajuan revisi.
12. Riwayat perubahan nilai.

## Antarmuka input

Buat tampilan input massal menyerupai spreadsheet:
- Baris siswa dan kolom komponen penilaian.
- Navigasi keyboard yang wajar.
- Validasi nilai pada client dan server.
- Simpan draft.
- Indikator perubahan belum tersimpan.
- Filter kelas, mapel, periode.
- Feedback saat menyimpan.
- Penanganan konflik jika data berubah bersamaan.
- Jangan mengklaim autosave jika mekanisme dan status penyimpanannya belum benar-benar diterapkan.

## Aturan perhitungan

- Gunakan PostgreSQL `NUMERIC` untuk nilai dan bobot, sesuai precision/scale yang ditetapkan.
- Rumus mengikuti konfigurasi resmi sekolah, bukan asumsi universal.
- Validasi total bobot sebelum menghitung.
- Tetapkan rentang nilai dan pembulatan berdasarkan konfigurasi/kebijakan yang disetujui.
- Simpan komponen, bobot, formula/versi konfigurasi, dan hasil yang diperlukan agar perhitungan dapat diaudit.
- Tangani nilai kosong secara eksplisit.
- KKTP harus dapat dikonfigurasi sesuai kurikulum/periode.
- Deskripsi capaian harus relevan dengan CP dan hasil penilaian.

## Workflow nilai

Status minimum:
- Draft
- Dalam Proses
- Terkirim
- Revisi Diajukan
- Revisi Disetujui
- Revisi Ditolak

Alur:
1. Guru menginput dan memeriksa nilai.
2. Guru mengirim nilai.
3. Sistem mengunci perubahan langsung sesuai status.
4. Wali kelas dapat melihat nilai terkirim.
5. Jika ada kesalahan, guru mengajukan revisi dengan alasan.
6. Revisi diproses oleh pihak yang berwenang sesuai desain.
7. Sistem menyimpan nilai sebelum/sesudah, alasan, pelaku, waktu, dan status keputusan.

## Hak akses

- Guru hanya mengelola nilai untuk penugasan sendiri.
- Wali kelas hanya melihat nilai, tidak mengubah nilai mapel.
- Admin memonitor dan menjalankan tindakan sesuai kebijakan yang disetujui.
- Semua endpoint memeriksa scope data di backend.

## Pengujian

Uji formula dengan contoh angka yang diketahui, total bobot, nilai batas, nilai kosong, pembulatan, pengiriman, penguncian, revisi disetujui/ditolak, audit trail, concurrency, dan pembatasan akses.

## Kriteria selesai

Perhitungan dapat direproduksi, workflow berjalan, dan riwayat perubahan lengkap. Jangan lanjut jika formula akademik belum dikonfirmasi.

## Batas tahap

Jangan mulai Stage 8. Laporkan hasil dan tunggu persetujuan.
