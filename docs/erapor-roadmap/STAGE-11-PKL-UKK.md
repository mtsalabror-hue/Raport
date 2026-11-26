# STAGE 11: INTERNSHIP (PKL) AND VOCATIONAL COMPETENCY TEST (UKK)

## Tujuan

Implementasikan modul PKL dan UKK yang terhubung dengan data siswa, jurusan/kompetensi keahlian, kelas, guru, dan periode akademik.

## Modul PKL

- Data tempat/mitra industri.
- Data penempatan siswa.
- Periode PKL.
- Guru pembimbing.
- Pembimbing industri.
- Monitoring kegiatan.
- Catatan dan evaluasi.
- Penilaian PKL.
- Rekap hasil PKL.

## Modul UKK

- Data kompetensi keahlian.
- Jadwal ujian.
- Data peserta.
- Data penguji.
- Skema/komponen penilaian.
- Input hasil penilaian.
- Status atau hasil kompetensi.
- Rekapitulasi hasil.

## Ketentuan bisnis

- Hubungkan setiap kegiatan dengan siswa dan periode yang benar.
- Cegah penempatan siswa yang tumpang tindih jika aturan sekolah melarangnya.
- Batasi akses berdasarkan peran dan penugasan.
- Simpan riwayat perubahan penempatan dan penilaian.
- Sediakan konfigurasi komponen penilaian, rubrik, dan skala.
- Jangan mengasumsikan formula, standar kompetensi, atau format sertifikat yang belum diberikan sekolah.
- Tentukan apakah nilai PKL/UKK masuk ke rapor melalui aturan eksplisit, jangan menghubungkannya otomatis tanpa keputusan desain.

## Integrasi

- Data PKL/UKK dapat ditampilkan pada area akademik/rapor jika disetujui dalam desain.
- Jaga konsistensi periode dan identitas siswa.
- Gunakan transaksi untuk operasi yang melibatkan beberapa tabel.

## Pengujian

Uji CRUD, penempatan, konflik periode, pembimbing, penilaian, rekap, akses role, dan integrasi dengan rapor jika berlaku.

## Kriteria selesai

Modul PKL dan UKK berjalan sesuai konfigurasi sekolah dan tidak merusak data akademik utama.

## Batas tahap

Jangan mulai Stage 12. Laporkan hasil dan tunggu persetujuan.
