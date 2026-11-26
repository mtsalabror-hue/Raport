# Panduan Pengguna — Admin

Panduan ini mengikuti antarmuka aplikasi yang sebenarnya (per 2026-11-26).

## 1. Login

Buka aplikasi, masukkan email dan password, klik Masuk. Setelah login, Anda masuk ke
Dashboard Admin. Akun admin dibuat oleh pengelola sistem; tidak ada pendaftaran mandiri.

## 2. Dashboard Admin

Dashboard menampilkan ringkasan: jumlah guru, siswa, kelas, dan ujian (angka diambil
dari database). Dari dashboard, gunakan menu untuk membuka modul di bawah.

## 3. Data master

Semua ada di bawah `/admin/<modul>` dengan tombol Tambah, Edit, dan Hapus, plus
pencarian dan halaman (10 baris per halaman).

- **Jurusan** (`/admin/departments`): kode, nama, deskripsi.
- **Guru** (`/admin/teachers`): NIP, nama, jenis kelamin, telepon, alamat.
- **Siswa** (`/admin/students`): NIS, NISN, nama, jenis kelamin, kelas, tanggal
  lahir, alamat.
- **Kelas** (`/admin/classes`): jurusan, tahun ajaran, nama, level (X/XI/XII).
- **Mata Pelajaran** (`/admin/subjects`): kode, nama, jurusan.
- **Tahun Ajaran** (`/admin/academic_years`): nama, tanggal mulai/selesai, aktif.
- **Semester** (`/admin/semesters`): tahun ajaran, nama, aktif.
- **Kurikulum** (`/admin/curricula`): nama, tahun ajaran, deskripsi.

Catatan: menghapus data yang masih dipakai (mis. guru yang masih punya penugasan)
akan ditolak oleh database. Ubah penugasannya dulu.

## 4. Akun pengguna

Buka `/admin/users` untuk mengelola akun login:

- **Tambah pengguna**: nama, email, password (minimal 8 karakter), role (Admin /
  Guru Mata Pelajaran / Wali Kelas), dan data guru yang dihubungkan (wajib diisi
  untuk akun guru dan wali kelas — tanpa ini guru/wali tidak bisa menginput nilai
  atau mengakses kelasnya).
- **Edit pengguna**: ubah nama, email, role, data guru, status aktif; kosongkan
  password bila tidak ingin mengubahnya.
- **Nonaktifkan** akun dengan menghilangkan centang Aktif (pengguna tidak bisa login).
- Admin tidak bisa menghapus akunnya sendiri.

Akun demo dari seeder (`admin@smk.test`, `guru@smk.test`, `wali@smk.test`,
password `password`) hanya untuk pengembangan — hapus atau ganti passwordnya
sebelum produksi.

## 5. Penugasan guru dan wali kelas

- **Penugasan guru** (`/admin/teaching-assignments`): pilih guru, mata pelajaran,
  kelas, tahun ajaran, semester, dan jam. Satu guru tidak boleh punya dua
  penugasan yang sama. Guru hanya bisa menginput nilai untuk penugasannya.
- **Wali kelas** (`/admin/homeroom-assignments`): pilih guru, kelas, tahun ajaran,
  dan semester. Satu kelas hanya punya satu wali kelas per tahun ajaran dan
  semester. Wali kelas hanya bisa mengakses kelas tanggung jawabnya.

Tanpa penugasan, guru tidak bisa menginput nilai dan wali kelas tidak bisa membuka
dashboard kelasnya. Buat penugasan setiap awal tahun ajaran/semester baru.

## 6. Monitoring presensi dan nilai

- **Monitoring presensi** (`/admin/attendance/monitoring`): rekap kehadiran per
  kelas/semester.
- **Monitoring nilai belum dikirim**: lihat guru/mapel/kelas yang nilai draft-nya
  belum dikirim, lalu tindak lanjuti ke guru yang bersangkutan.

## 7. Rapor: pemeriksaan, persetujuan, penerbitan

Buka `/admin/report-cards` untuk daftar rapor per kelas dan statusnya.

1. Pilih rapor, periksa isinya (nilai, deskripsi, presensi, catatan wali).
2. **Setujui** bila sudah benar, atau **Kembalikan** dengan alasan bila perlu perbaikan
   (rapor kembali ke wali kelas sebagai draft).
3. **Terbitkan** rapor yang sudah disetujui. Saat diterbitkan, isi rapor disalin
   sebagai snapshot dan tidak berubah lagi meski data nilai berubah.
4. **Kunci** rapor yang sudah diterbitkan bila sudah final.
5. **Unduh PDF** rapor (A4, nama file `rapor-{nis}-{semester}.pdf`).

Koreksi setelah rapor diterbitkan tidak bisa dilakukan langsung dari aplikasi;
ikuti prosedur resmi sekolah.

## 8. PKL dan UKK

- **PKL** (`/admin/internships`): tambah penempatan siswa (nama perusahaan, tanggal
  mulai/selesai, pembimbing), lihat monitoring (berjalan/mendatang/selesai), beri
  penilaian per aspek, lihat rekap. Penempatan yang tanggalnya tumpang tindih untuk
  siswa yang sama akan ditolak.
- **UKK** (`/admin/competency-exams`): jadwal ujian (mapel + tanggal), peserta,
  penguji (ditulis di awal kolom catatan sebagai "Penguji: ..."), input hasil,
  rekap per siswa/mapel/jadwal. Tidak ada label lulus/tidak lulus (ambang belum
  ditetapkan sekolah).

Nilai PKL/UKK **tidak** masuk ke rapor secara otomatis.

## 9. Hal yang perlu diingat

- Jangan membagikan akun admin. Setiap perubahan penting tercatat di audit log
  beserta nama pengguna dan waktunya.
- Sebelum tahun ajaran baru, buat tahun ajaran dan semester baru, lalu buat
  penugasan guru dan wali kelas yang baru. Data semester lama tetap tersimpan
  dan tidak tercampur.
- Bila ada data yang hilang atau salah, jangan mengubah langsung di database;
  gunakan menu aplikasi agar audit log tetap lengkap.
