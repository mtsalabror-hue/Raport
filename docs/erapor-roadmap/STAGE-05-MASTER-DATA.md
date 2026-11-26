# STAGE 5: MASTER DATA MANAGEMENT

## Tujuan

Implementasikan CRUD data master sekolah dengan validasi, relasi, pencarian, filter, pagination, dan pembatasan akses.

## Modul

1. Data Guru.
2. Data Siswa.
3. Data Jurusan.
4. Data Kelas.
5. Data Mata Pelajaran.
6. Tahun Ajaran.
7. Semester.
8. Profil Sekolah.
9. Manajemen Pengguna (integrasi dengan Stage 3, bukan membuat ulang autentikasi).

## Fitur minimum setiap modul

- Daftar data dalam tabel.
- Detail data.
- Tambah data.
- Edit data.
- Hapus/nonaktifkan sesuai aturan dan relasi historis.
- Pencarian.
- Filter yang relevan.
- Pagination server-side.
- Validasi frontend dan backend.
- Pesan berhasil/gagal.
- Konfirmasi tindakan destruktif.
- Empty state dan error state.

## Data dan integritas

- Gunakan identitas seperti NIP/NUPTK dan NISN sesuai ketersediaan dan kebijakan sekolah.
- Terapkan unique constraints hanya untuk identitas yang memang wajib unik dan tidak nullable menurut desain.
- Normalisasi data, hindari duplikasi.
- Validasi foreign key.
- Jangan menghapus data yang masih direferensikan oleh catatan akademik.
- Gunakan soft delete atau status aktif/nonaktif jika sesuai desain.
- Pertahankan data historis siswa, kelas, guru, dan penugasan.
- Gunakan transaksi untuk operasi multi-tabel.

## Import/export

Jika disertakan dalam desain:
- Validasi template dan kolom file.
- Validasi setiap baris sebelum menyimpan.
- Tampilkan ringkasan baris berhasil/gagal.
- Cegah import duplikat.
- Jangan mengubah data yang sudah ada tanpa aturan eksplisit.
- Sediakan export dengan filter yang sedang digunakan.
- Batasi ukuran/jenis file dan validasi di server.

## Hak akses

Admin mengelola data master. Guru dan Wali Kelas hanya melihat data yang diperlukan untuk tugas mereka. Jangan memberi hak CRUD master data kepada role lain tanpa dasar di dokumen desain.

## Pengujian

Uji CRUD tiap modul, validasi duplikasi, foreign key, pencarian, filter, pagination, soft delete, import/export bila ada, serta pembatasan role.

## Kriteria selesai

Semua modul dalam cakupan berfungsi dengan PostgreSQL dan data konsisten. Laporkan tabel/migration yang berubah, jangan membuat ulang tabel yang sudah ada.

## Batas tahap

Jangan mulai Stage 6. Berikan laporan dan tunggu persetujuan.
