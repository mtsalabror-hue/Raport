# Stage 8 Progress

Diperbarui 2026-11-26 setelah implementasi Stage 8 (Laravel 13, PHP 8.3).
Seluruh pengujian Feature + Unit: 49 lolos, 0 gagal
(`php artisan test`, 117 assertions) — 37 pengujian lama tetap hijau,
ditambah 12 pengujian baru StageEightTest.

- Input presensi massal: COMPLETE (satu baris per siswa per kelas +
  tanggal, status H/S/I/A + catatan, simpan dalam satu transaksi,
  siswa di luar kelas ditolak)
- Pencegahan duplikasi: COMPLETE (validasi pra-simpan + aturan
  `distinct` per payload + unique (student_id, date) di database;
  pelanggaran unique ditangkap sebagai pengaman terakhir, transaksi
  dibatalkan utuh sehingga tidak ada catatan yang tersimpan sebagian)
- Koreksi presensi + audit trail: COMPLETE (endpoint koreksi per
  catatan, ActivityLog `attendance.corrected` menyimpan status lama
  dan baru; pembuatan mencatat `attendance.created`)
- Riwayat presensi: COMPLETE (filter kelas, rentang tanggal, AY,
  semester, status; paginasi 20)
- Rekap per siswa: COMPLETE (H/S/I/A, total, persentase hadir 2
  desimal; siswa tanpa catatan tetap muncul dengan angka 0)
- Rekap per kelas: COMPLETE (total H/S/I/A + persentase hadir kelas)
- Rekap per semester: COMPLETE (total per kelas dalam satu periode
  AY + semester, dipakai halaman monitoring admin)
- Rekap akademik: COMPLETE (nilai akhir per siswa/mapel/kelas via
  AssessmentService::recap + finalScore, status ketuntasan KKTP,
  ringkasan tuntas/belum tuntas/belum dinilai, filter AY/semester)
- Monitoring nilai belum dikirim: COMPLETE (admin melihat nilai
  berstatus draft, dikelompokkan per guru/mapel/kelas/periode dengan
  jumlah nilai dan jumlah siswa)
- Batas kelas/penugasan: COMPLETE (guru: teaching_assignments atau
  homeroom_assignments, pola soft-check seperti assertAssignment
  Stage 7 — bila belum ada penugasan sama sekali untuk kelas itu,
  diizinkan; wali: hanya kelas pada homeroom_assignments miliknya,
  kelas lain 403; wali hanya melihat nilai terkirim/dikunci)
- Konsistensi semester: COMPLETE (semua rekap memakai AY + semester
  eksplisit; pengujian membuktikan rekap semester 1 tidak memuat
  catatan semester 2 dan sebaliknya)
- Comprehensive tests: COMPLETE (StageEightTest 12 pengujian:
  pencatatan + audit log, duplikasi ditolak validasi dan unique,
  koreksi + audit trail, rekap angka dan persentase, filter tanggal,
  guru lain 403, wali kelas lain 403, konsistensi semester,
  rekap akademik + KKTP, rekap akademik wali hanya nilai terkirim,
  monitoring draft admin)

## Keputusan desain

- Duplikasi presensi = satu catatan per siswa per tanggal (tidak ada
  kolom sesi pada tabel). Diwujudkan lewat SATU migrasi baru
  `2026_01_06_000000_add_unique_to_student_attendances.php` yang
  menambah unique (student_id, date), ditambah validasi pra-simpan
  dan aturan `distinct` di controller.
- Tidak ada kolom pencatat pada tabel student_attendances (skema
  sudah ada); pencatat tercatat lewat ActivityLog (user_id + metadata
  student_id, class_id, date, status).
- Agregasi rekap memakai satu query GROUP BY per rekap
  (AttendanceService), tanpa N+1. Siswa tanpa catatan tetap muncul
  pada rekap per siswa dengan angka 0 dan persentase null.
- Model HomeroomAssignment dibuat baru (tabel sudah ada di migrasi
  inti, modelnya belum ada) untuk keperluan otorisasi wali kelas.
- Tidak ada migrasi baru lain; tidak ada perubahan pada file Stage 7
  kecuali pemakaian AssessmentService yang sudah ada.

## File dibuat

- database/migrations/2026_01_06_000000_add_unique_to_student_attendances.php
- app/Models/StudentAttendance.php
- app/Models/HomeroomAssignment.php
- app/Services/AttendanceService.php
- app/Http/Controllers/Teacher/AttendanceController.php
- app/Http/Controllers/Admin/AttendanceController.php
- resources/views/teacher/attendance/index.blade.php
- resources/views/teacher/attendance/history.blade.php
- resources/views/teacher/attendance/recap.blade.php
- resources/views/teacher/attendance/academic-recap.blade.php
- resources/views/admin/attendance/monitoring.blade.php
- tests/Feature/Attendance/StageEightTest.php
- docs/erapor-roadmap/STAGE-08-PROGRESS.md (file ini)

## File diubah

- routes/web.php (route presensi guru /guru/attendance*,
  rekap wali /wali/attendance/*, monitoring admin
  /admin/attendance/monitoring; import controller baru)

## Catatan implementasi

- Model Student hanya mengizinkan mass-assignment nis/nisn/name,
  sehingga pada pengujian class_id siswa diisi lewat assignment
  langsung lalu save (kolom class_id ada di tabel).
- Cast `date` pada model StudentAttendance menyimpan nilai sebagai
  'Y-m-d H:i:s' pada kolom date di SQLite; pengujian memakai
  whereDate untuk membandingkan tanggal agar tidak bergantung pada
  format penyimpanan driver.
- View mengikuti gaya Stage 7: Blade standalone dengan Tailwind CDN,
  tanpa layout bersama. View rekap presensi dan rekap akademik
  dipakai bersama guru dan wali lewat flag `readOnly` (wali melihat
  daftar kelas yang dibatasi ke homeroom_assignments miliknya).
- Otorisasi dilakukan di backend pada setiap endpoint (bukan hanya
  menyembunyikan menu): guru lain dan wali kelas lain mendapat 403.

## Yang belum dikonfirmasi

- Status atau sesi presensi tambahan (misalnya presensi per jam
  pelajaran) — skema saat ini satu catatan per siswa per tanggal;
  penambahan sesi butuh keputusan sekolah dan perubahan desain.
- Apakah wali kelas boleh mengoreksi presensi (saat ini koreksi hanya
  lewat route guru; wali hanya-baca).
- Apakah admin boleh mengoreksi presensi atau hanya memonitor
  (saat ini admin hanya monitoring).
- Batas persentase kehadiran minimum untuk rapor (misalnya 90%) —
  belum ditetapkan sekolah, jadi rekap hanya menampilkan angka.
