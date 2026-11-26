# Panduan Pengguna — Wali Kelas

Panduan ini mengikuti antarmuka aplikasi yang sebenarnya (per 2026-11-26).

## 1. Login

Buka aplikasi, masukkan email dan password, klik Masuk. Anda masuk ke Dashboard
Wali Kelas. Anda hanya bisa mengakses kelas yang menjadi tanggung jawab Anda pada
tahun ajaran dan semester berjalan.

## 2. Dashboard kelas

Dashboard menampilkan ringkasan kelas Anda: jumlah siswa dan progres kelengkapan
rapor tiap siswa. Bila ada siswa yang belum lengkap, klik namanya untuk melihat
kekurangan datanya dan tautan ke sumbernya.

## 3. Data siswa dan rekap nilai

- **Data siswa** (`/wali/students`): daftar siswa kelas Anda.
- **Rekap nilai** (`/wali/assessments/recap`): nilai mata pelajaran yang sudah
  dikirim guru. **Read-only** — Anda tidak bisa mengubah nilai mapel.
- Bila ada nilai yang belum dikirim atau sedang direvisi, statusnya terlihat di
  rekap; hubungi guru mapel yang bersangkutan.
- **Rekap presensi** (`/wali/attendance/recap`) dan rekap akademik
  (`/wali/attendance/academic-recap`): kehadiran dan ketuntasan KKTP per siswa.
- **Riwayat akademik** (`/wali/history/{siswa}`): nilai dan presensi siswa lintas
  semester.

## 4. Catatan, prestasi, dan ekstrakurikuler

- **Catatan wali kelas** (`/wali/notes`): tulis catatan untuk rapor tiap siswa.
  Catatan ini tercetak di rapor.
- **Prestasi** (`/wali/achievements`): tambah prestasi siswa (judul, deskripsi,
  tingkat). Bisa diubah dan dihapus.
- **Ekstrakurikuler** (`/wali/extracurriculars`): catat ekskul tiap siswa
  (beserta nilai dan deskripsi bila ada). Master ekstrakurikuler dapat ditambah
  lewat menu yang sama; master yang sudah dipakai siswa tidak bisa dihapus.
  Duplikasi ekskul untuk siswa yang sama pada semester yang sama akan ditolak.

Semua perubahan tercatat dan dibatasi pada kelas Anda.

## 5. Monitoring kelengkapan rapor

Buka `/wali/completeness`: sistem memeriksa 4 komponen wajib untuk tiap siswa:

1. Nilai semua mata pelajaran sudah terkirim.
2. Catatan wali kelas sudah diisi.
3. Presensi tercatat pada semester berjalan.
4. Identitas siswa lengkap.

Bila ada yang kurang, daftar kekurangan ditampilkan beserta tautan ke halaman
sumbernya. Rapor **tidak** bisa diajukan sebelum semua komponen lengkap.

## 6. Preview dan pengajuan rapor

1. Buka **Preview** (`/wali/preview/{siswa}`) untuk melihat tampilan rapor siswa.
2. Bila sudah lengkap dan benar, klik **Ajukan** (`/wali/submit`). Pengajuan
   tercatat beserta nama Anda dan waktunya.
3. Setelah diajukan, rapor diperiksa admin. Bila admin mengembalikan dengan alasan,
   perbaiki bagian yang disebutkan lalu ajukan kembali.
4. Daftar rapor kelas Anda ada di `/wali/report-cards`; rapor yang sudah diterbitkan
   dapat diunduh sebagai PDF.

## 7. PKL dan UKK (hanya-baca)

- **PKL** (`/wali/internships` dan `/wali/internships/recap`): lihat penempatan
  dan rekap PKL siswa kelas Anda.
- **UKK** (`/wali/competency-exams`): lihat hasil UKK siswa kelas Anda.

## 8. Hal yang perlu diingat

- Anda tidak bisa mengubah nilai mata pelajaran. Bila ada nilai yang salah,
  minta guru mapel mengajukan revisi.
- Jangan mengajukan rapor sebelum memeriksa preview; pengajuan yang dikembalikan
  admin akan memperlambat penerbitan.
- Bila siswa pindah kelas atau ada perubahan penugasan wali kelas, hubungi admin;
  penugasan diubah oleh admin, bukan dari menu wali kelas.
