# STAGE 3: AUTHENTICATION AND ROLE-BASED ACCESS CONTROL

## Tujuan

Implementasikan autentikasi dan otorisasi yang aman untuk tiga role: Admin, Guru Mata Pelajaran, dan Wali Kelas. Periksa implementasi yang sudah tersedia sebelum membuat komponen baru.

## Pekerjaan

1. Audit tabel `users`, roles, permissions, pivot, model, middleware, dan konfigurasi autentikasi yang sudah ada.
2. Pertahankan sistem yang sudah berfungsi. Hindari membuat tabel atau mekanisme RBAC duplikat.
3. Implementasikan login dan logout.
4. Terapkan manajemen akun pengguna sesuai kewenangan Admin.
5. Hubungkan akun Guru dengan data guru dan akun Wali Kelas dengan penugasan wali kelas yang berlaku.
6. Implementasikan middleware, policies, gates, atau mekanisme Laravel yang paling sesuai.
7. Implementasikan halaman profil dan perubahan password.
8. Terapkan validasi kredensial, session management, proteksi CSRF, dan rate limiting untuk login.
9. Pastikan pengguna yang belum login tidak dapat mengakses halaman internal.
10. Pastikan logout menginvalidasi session dan meregenerasi token CSRF.

## Hak akses

### Admin
- Dapat mengelola akun dan role sesuai kebijakan sistem.
- Dapat mengakses modul administrasi sekolah.
- Tidak boleh memperoleh akses teknis yang tidak diperlukan hanya karena role Admin, kecuali memang ditetapkan dalam desain.

### Guru Mata Pelajaran
- Hanya dapat melihat kelas, siswa, mata pelajaran, dan penugasan yang terkait dengannya.
- Hanya dapat mengelola nilai dan presensi dalam ruang lingkup penugasannya.
- Tidak dapat mengubah nilai milik guru lain.
- Tidak dapat mengakses fungsi administrasi pengguna.

### Wali Kelas
- Hanya dapat mengakses siswa dan data kelas yang menjadi tanggung jawabnya pada tahun ajaran terkait.
- Dapat melihat nilai mata pelajaran.
- Tidak dapat mengubah nilai mata pelajaran.
- Dapat mengelola komponen administrasi wali kelas sesuai tahap berikutnya.

## Keamanan wajib

- Semua pemeriksaan kewenangan harus dilakukan di server.
- Jangan mengandalkan kondisi menu atau tombol di frontend.
- Cegah IDOR dengan memeriksa kepemilikan/ruang lingkup data pada setiap request.
- Gunakan mass-assignment protection dan validasi request.
- Jangan mengungkap apakah username atau password tertentu valid melalui pesan error yang terlalu spesifik.
- Jangan menyimpan password plaintext.
- Jangan menampilkan atau mencatat password/token pada log.

## Pengujian

Buat atau lengkapi feature tests untuk:
- Login berhasil dan gagal.
- Logout.
- Akses guest ke halaman terlindungi.
- Admin mengakses halaman yang diizinkan.
- Guru mencoba mengakses kelas/penugasan guru lain.
- Wali kelas mencoba mengakses kelas lain.
- Wali kelas mencoba mengubah nilai mapel.
- Pengguna mencoba mengakses URL langsung atau mengubah ID pada request.
- Perubahan password dan validasi session.

Gunakan akun demo dari seeder hanya untuk lingkungan development/testing. Jangan bergantung pada password demo di produksi.

## Kriteria selesai

Seluruh jalur autentikasi dan otorisasi berjalan, tes akses positif dan negatif lulus, serta tidak ada celah akses lintas role yang diketahui.

## Batas tahap

Jangan mulai Stage 4. Berikan laporan sesuai format `README.md` dan tunggu persetujuan.
