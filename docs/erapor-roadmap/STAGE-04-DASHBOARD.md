# STAGE 4: APPLICATION LAYOUT AND DASHBOARDS

## Tujuan

Bangun kerangka UI aplikasi dan dashboard terpisah untuk Admin, Guru Mata Pelajaran, dan Wali Kelas.

## Panduan visual

Gunakan desain modern SaaS Education Dashboard:
- Royal Blue `#2563EB`
- Navy `#172554`
- White `#FFFFFF`
- Light Gray `#F8FAFC`
- Border Gray `#E2E8F0`
- Text Dark `#1E293B`
- Font Inter atau Plus Jakarta Sans
- Lucide Icons

Pertahankan desain Stitch atau referensi UI yang telah disetujui jika tersedia di repository. Jangan mengarang ulang desain yang sudah ada tanpa alasan.

## Layout utama

- Sidebar kiri dengan logo/nama sekolah.
- Navigasi berdasarkan role dan permission.
- Sidebar dapat collapse bila sesuai desain.
- Top navbar berisi judul/breadcrumb, notifikasi, profil, dan logout.
- Area konten dengan jarak, hierarki tipografi, dan komponen konsisten.
- Responsive untuk desktop, tablet, dan smartphone.
- Sediakan state loading, empty, error, dan success.
- Semua tombol, tautan, filter, dan menu harus memiliki fungsi nyata.

## Dashboard Admin

Tampilkan data yang dihitung dari PostgreSQL, jika datanya tersedia:
- Total siswa aktif.
- Total guru aktif.
- Total kelas.
- Total mata pelajaran.
- Tahun ajaran/semester aktif.
- Progres pengiriman nilai.
- Progres kelengkapan rapor.
- Ringkasan statistik akademik.
- Aktivitas terbaru dari audit/activity log.

## Dashboard Guru

Tampilkan:
- Penugasan dan kelas yang diampu.
- Jumlah siswa pada kelas terkait.
- Jadwal mengajar jika modul jadwal tersedia.
- Progres input nilai.
- Nilai draft/belum dikirim.
- Status pengiriman dan revisi.

## Dashboard Wali Kelas

Tampilkan:
- Kelas tanggung jawab dan jumlah siswa.
- Ringkasan presensi jika tersedia.
- Kelengkapan nilai.
- Kelengkapan rapor.
- Status pengajuan rapor.
- Catatan atau notifikasi yang relevan.

## Ketentuan teknis

- Jangan menggunakan angka hardcoded sebagai statistik.
- Gunakan query yang dibatasi berdasarkan role dan ruang lingkup.
- Hindari N+1 query, gunakan eager loading dan agregasi yang sesuai.
- Jangan menampilkan modul yang belum tersedia seolah-olah sudah berfungsi. Tautan dapat diberi status belum tersedia hanya jika dijelaskan secara eksplisit.
- Gunakan komponen Blade reusable untuk tabel, form, badge status, alert, modal, dan pagination bila sesuai.
- Pastikan kontras, label form, fokus keyboard, dan navigasi dasar dapat digunakan.

## Pengujian

Uji rendering dashboard untuk ketiga role, data kosong, data tersedia, pembatasan data, navigasi, responsive layout, dan tautan/tombol.

## Kriteria selesai

Ketiga dashboard menampilkan data sesuai role, tidak membocorkan data lintas kelas/penugasan, dan layout konsisten serta responsif.

## Batas tahap

Jangan mulai Stage 5. Laporkan hasil dan tunggu persetujuan.
