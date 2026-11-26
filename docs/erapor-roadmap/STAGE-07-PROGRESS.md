# Stage 7 Progress

Diperbarui 2026-11-26 setelah implementasi Stage 7 (Laravel 13, PHP 8.3).
Seluruh pengujian Feature + Unit: 37 lolos, 0 gagal
(`php artisan test`, 76 assertions).

- Weight validation: COMPLETE (AssessmentWeightTest 2/2, plus bobot per cakupan mapel/kurikulum di StageSevenTest)
- Component configuration: COMPLETE (CRUD komponen + bobot per mapel/kurikulum + KKTP per komponen, validasi total bobot per cakupan)
- Automatic calculations: COMPLETE (AssessmentService::finalScore, contoh draf 80,50 dan 80,00 teruji, nilai kosong dikeluarkan dari penyebut, total bobot harus 100 sebelum menghitung)
- Score recapitulation: COMPLETE (rekap dengan filter + nilai akhir + status KKTP, rekap hanya-baca untuk wali kelas)
- Submission/approval: COMPLETE (submit hanya nilai draft milik guru yang login, revisi tidak dapat disetujui pengajunya sendiri)
- Score locking: COMPLETE (terkirim dan dikunci tidak dapat diubah langsung, endpoint lock terpisah)
- Revision workflow: COMPLETE (pengajuan revisi, setuju/tolak, status perlu_revisi, riwayat revisi)
- Achievement descriptions: COMPLETE (deskripsi otomatis dari nilai akhir dan KKTP, dapat ditulis manual)
- Comprehensive tests: COMPLETE (StageSevenTest 14 pengujian: formula, bobot, kirim, kunci, revisi, bulk + deteksi konflik, audit log, akses wali, KKTP/deskripsi, riwayat)

## Catatan implementasi

- Rumus nilai akhir memakai konfigurasi komponen (bobot per komponen),
  bukan angka yang di-hardcode. Nilai default KKTP 75 (kolom kktp di
  assessment_components). Angka usulan pada DRAF-KEPUTUSAN-FORMULA-STAGE-07.md
  (formatif 30 / sumatif 40 / praktik 20 / proyek 10) belum dikonfirmasi
  sekolah; komponen harus dibuat dengan bobot tersebut oleh admin/guru
  sebelum perhitungan dijalankan.
- composer.lock diperbarui agar dapat diinstal pada PHP 8.3 (minimum yang
  dinyatakan composer.json): paket symfony 8.1 diturunkan ke 7.4.
  laravel/framework tetap v13.44.1.
- Perbaikan di luar Stage 7 yang ditemukan saat pengujian: model
  TeacherDescription dan Curriculum belum ada (controller sudah
  mereferensikannya), import LoginController dan AcademicYearController
  hilang di routes/web.php. Keduanya diperbaiki.
- Audit trail ditulis ke activity_logs pada pembuatan, perubahan,
  pengiriman, penguncian nilai, dan setiap keputusan revisi.
- Deteksi konflik input massal memakai loaded_at: jika ada nilai pada
  cakupan yang sama berubah setelah halaman dimuat, penyimpanan ditolak
  dan pengguna diminta memuat ulang.

## Yang belum dikonfirmasi

- Formula dan bobot resmi sekolah (menunggu jawaban atas draf keputusan).
- Siapa yang menyetujui revisi (saat ini: guru lain mana pun; bukan
  pengaju). Perlu diputuskan apakah wali kelas, kepala sekolah, atau
  admin yang berwenang.
- Apakah predikat (A/B/C/D) tampil pada rapor.
