# STAGE 8: ATTENDANCE AND ACADEMIC RECAPITULATION

## Tujuan

Implementasikan pencatatan presensi dan rekap akademik berdasarkan periode akademik yang benar.

## Presensi

Status yang didukung:
- Hadir
- Sakit
- Izin
- Tanpa keterangan

Bangun:
- Input presensi siswa.
- Riwayat presensi.
- Rekap per siswa.
- Rekap per kelas.
- Rekap per semester.
- Filter tanggal/periode.
- Koreksi presensi dengan hak akses dan audit trail.

Jika sekolah membutuhkan status atau sesi tambahan, jangan menambahkannya tanpa mengacu pada desain/kebijakan yang disetujui.

## Aturan presensi

- Catatan terhubung dengan siswa, kelas, tanggal, pencatat, dan konteks periode.
- Cegah duplikasi berdasarkan kombinasi yang ditetapkan desain, misalnya siswa, tanggal, dan sesi.
- Perubahan catatan yang sudah tersimpan harus dapat ditelusuri.
- Batasi guru sesuai penugasan dan wali kelas sesuai kelas tanggung jawab.
- Admin dapat memonitor sesuai kewenangan.

## Rekapitulasi akademik

- Rekap nilai per mata pelajaran.
- Rekap nilai per siswa.
- Rekap nilai per kelas.
- Rekap ketuntasan berdasarkan KKTP yang berlaku.
- Rekap presensi.
- Monitoring nilai belum dikirim.
- Filter tahun ajaran dan semester.

## Ketentuan teknis

- Gunakan query agregasi yang tepat dan hindari N+1.
- Pastikan semua rekap memakai periode akademik yang eksplisit.
- Jangan mencampur data lintas semester.
- Tangani perubahan data sumber agar rekap tetap konsisten.
- Simpan snapshot hanya jika dibutuhkan oleh aturan historis dan desain rapor.

## Pengujian

Uji pencatatan, duplikasi, koreksi, rekap angka, filter tanggal, batas kelas/penugasan, dan konsistensi semester.

## Kriteria selesai

Presensi dan rekap menghasilkan data akurat, dapat ditelusuri, dan terisolasi berdasarkan hak akses.

## Batas tahap

Jangan mulai Stage 9. Laporkan hasil dan tunggu persetujuan.
