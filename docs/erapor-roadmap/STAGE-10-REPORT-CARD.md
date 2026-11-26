# STAGE 10: REPORT CARD MANAGEMENT

## Tujuan

Implementasikan workflow pemeriksaan, persetujuan, penerbitan, penyimpanan historis, dan pencetakan rapor.

## Komponen rapor

Sesuaikan dengan desain sekolah dan kebijakan yang telah disetujui:
- Identitas sekolah.
- Identitas siswa.
- Tahun ajaran dan semester.
- Mata pelajaran dan nilai akhir.
- Deskripsi capaian pembelajaran.
- Presensi.
- Catatan wali kelas.
- Prestasi.
- Ekstrakurikuler.
- Informasi lain yang ditetapkan sekolah.
- Area tanda tangan pihak berwenang.

Jangan mengarang format resmi atau kolom wajib yang belum ditetapkan.

## Workflow

1. Guru menyelesaikan dan mengirim nilai.
2. Wali kelas memeriksa nilai dan komponen lain.
3. Sistem memvalidasi kelengkapan.
4. Wali kelas melakukan preview.
5. Wali kelas mengajukan rapor.
6. Admin/pejabat berwenang memeriksa.
7. Jika perlu perbaikan, rapor dikembalikan dengan alasan.
8. Jika disetujui, rapor diterbitkan.
9. Rapor dapat dicetak atau diunduh dalam PDF.
10. Riwayat status dan tindakan disimpan.

Status minimum:
- Belum Lengkap
- Siap Diperiksa
- Diajukan
- Perlu Perbaikan
- Disetujui
- Diterbitkan

## Integritas historis

- Rapor yang diterbitkan harus mempertahankan isi pada saat penerbitan.
- Gunakan snapshot atau representasi immutable yang sesuai desain.
- Perubahan data aktif di semester berikutnya tidak boleh mengubah rapor lama.
- Koreksi setelah penerbitan harus melalui prosedur resmi.
- Simpan versi, waktu penerbitan, penerbit, pemberi persetujuan, dan riwayat koreksi.
- Gunakan transaksi untuk penerbitan dan perubahan status.
- Cegah penerbitan ganda yang tidak disengaja.

## PDF

- Format A4 dengan layout konsisten.
- Periksa page break, margin, font, tabel panjang, dan hasil cetak.
- Pastikan PDF menggunakan data snapshot yang diterbitkan.
- Pastikan file tidak dapat diakses oleh pengguna yang tidak berwenang.
- Nama file tidak boleh mengekspos data sensitif secara berlebihan.

## Pengujian

Uji validasi kelengkapan, pengajuan, persetujuan, pengembalian, penerbitan, snapshot, cetak PDF, akses file, koreksi, dan data lintas semester.

## Kriteria selesai

Workflow rapor berjalan end-to-end dan rapor yang telah diterbitkan stabil secara historis.

## Batas tahap

Jangan mulai Stage 11. Laporkan hasil dan tunggu persetujuan.
