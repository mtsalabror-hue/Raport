# Panduan Pengguna — Guru Mata Pelajaran

Panduan ini mengikuti antarmuka aplikasi yang sebenarnya (per 2026-11-26).

## 1. Login

Buka aplikasi, masukkan email dan password, klik Masuk. Anda masuk ke Dashboard Guru.
Akun guru dibuat oleh admin dan dihubungkan ke data guru Anda.

## 2. Dashboard Guru

Dashboard menampilkan ringkasan penugasan Anda (mapel dan kelas yang Anda ampu).
Anda hanya bisa mengakses mapel dan kelas pada penugasan Anda.

## 3. Komponen penilaian

Buka `/guru/components` untuk membuat komponen penilaian per mata pelajaran dan
kurikulum:

- Nama komponen, tipe (Formatif, Sumatif, Praktik, Proyek), bobot (%), KKTP.
- **Total bobot aktif per mata pelajaran dan kurikulum harus 100.** Bila belum 100,
  nilai akhir tidak bisa dihitung.
- KKTP default 75; dapat diubah per komponen.

## 4. Input nilai

Buka `/guru/assessments`:

1. Pilih mata pelajaran, kelas, tahun ajaran, dan semester.
2. Isi nilai tiap siswa per komponen (0–100). Kosongkan bila belum dinilai;
   komponen kosong tidak dihitung sebagai 0.
3. Simpan sebagai draft. Nilai draft hanya terlihat oleh Anda.
4. Input massal tersedia lewat tombol bulk (isi banyak siswa sekaligus). Bila ada
   guru lain yang mengubah nilai yang sama saat Anda mengisi, sistem akan
   memberitahu adanya konflik.

Nilai akhir tiap siswa dihitung otomatis: rata-rata tertimbang dari komponen yang
sudah bernilai. Lihat di `/guru/assessments/calculate` dan `/guru/assessments/recap`.

## 5. Deskripsi capaian

Buka `/guru/descriptions`: tulis deskripsi capaian pembelajaran per siswa per mata
pelajaran. Bila dikosongkan, sistem mengisi otomatis "Mencapai KKTP" atau
"Belum mencapai KKTP" berdasarkan nilai akhir.

## 6. Kirim dan kunci nilai

1. Setelah semua nilai lengkap, klik **Kirim** (`/guru/assessments/submit`).
   Hanya nilai draft milik Anda yang berubah menjadi terkirim.
2. Nilai terkirim dapat **dikunci** agar tidak berubah.
3. Bila ada kesalahan pada nilai yang sudah terkirim/dikunci, ajukan **revisi**
   dengan alasan. Revisi harus disetujui guru lain (bukan Anda sendiri). Bila
   disetujui, nilai baru diterapkan; bila ditolak, nilai lama tetap berlaku.
4. Riwayat nilai dan revisi dapat dilihat di `/guru/assessments/history`.

## 7. Presensi

Buka `/guru/attendance`:

- Pilih kelas dan tanggal, isi status tiap siswa: H (Hadir), S (Sakit), I (Izin),
  A (Tanpa keterangan), beserta catatan bila perlu.
- Satu siswa hanya punya satu catatan per tanggal; mengisi ulang tanggal yang sama
  akan ditolak.
- **Koreksi** presensi yang sudah tersimpan: ubah lewat tombol koreksi; perubahan
  tercatat di audit log.
- Riwayat dan rekap tersedia di `/guru/attendance/history` dan
  `/guru/attendance/recap`. Rekap akademik (nilai + ketuntasan KKTP) ada di
  `/guru/attendance/academic-recap`.

## 8. PKL dan UKK (terbatas)

- **PKL** (`/guru/internships`): Anda hanya melihat penempatan yang pembimbingnya
  bernama sama dengan nama Anda. Anda dapat memberi penilaian per aspek pada
  penempatan bimbingan Anda.
- **UKK** (`/guru/competency-exams`): hanya-baca untuk mata pelajaran yang Anda ampu.
  Input hasil UKK dilakukan oleh admin.

## 9. Hal yang perlu diingat

- Anda tidak bisa mengubah nilai guru lain, nilai di luar penugasan Anda, atau
  rapor (itu wewenang wali kelas dan admin).
- Jangan mengirim nilai sebelum semua komponen lengkap; nilai yang sudah terkirim
  hanya bisa diubah lewat revisi yang disetujui guru lain.
- Bila total bobot belum 100, periksa kembali komponen Anda sebelum bertanya ke admin.
