# E-Rapor SMK — Dokumentasi Teknis

Terakhir diperbarui: 2026-11-26 (Stage 14). Versi aplikasi: Laravel 13, PHP ^8.3, PostgreSQL (produksi), SQLite (pengujian).

## 1. Arsitektur

Aplikasi monolitik Laravel (MVC) dengan Blade + Tailwind CSS (CDN) di sisi tampilan,
tanpa build frontend wajib kecuali halaman utama (`welcome.blade.php` memakai `@vite`).
Logika bisnis berada di service layer (`app/Services/`); controller hanya menangani
HTTP, validasi, dan otorisasi. Otorisasi ditegakkan di backend (middleware `role:` dan
pemeriksaan cakupan di controller/service), bukan hanya dengan menyembunyikan menu.

```
Browser ──► routes/web.php ──► Middleware (auth, role) ──► Controller
        ──► Service (logika bisnis, transaksi DB) ──► Model (Eloquent)
        ──► PostgreSQL
        └─► Blade view (Tailwind CDN) / DomPDF (rapor PDF)
```

## 2. Struktur folder

```
app/
  Http/Controllers/
    Auth/LoginController.php
    DashboardController.php
    Admin/      AcademicYear, Attendance, CompetencyExam, Curriculum,
                Department, Internship, ReportCard, SchoolClass,
                Semester, Student, Subject, Teacher
    Teacher/    Assessment, Attendance, Internship
    Wali/       Homeroom, Internship, ReportCard
  Http/Middleware/EnsureRole.php
  Models/       (28 model, lihat bagian 4)
  Services/     Assessment, Attendance, CompetencyExam, Internship,
                ReportCard, ReportCardPdf, ReportCompleteness
database/
  migrations/   9 file (lihat bagian 5)
  seeders/      DatabaseSeeder (3 role + 3 akun demo, hanya untuk dev)
docs/
  DESIGN.md, TECHNICAL_DESIGN.md, TECHNICAL.md (file ini),
  PANDUAN-ADMIN.md, PANDUAN-GURU.md, PANDUAN-WALI.md,
  erapor-roadmap/ (STAGE-03 s.d. STAGE-14 + laporan progres + FINAL-REPORT.md)
resources/views/
  auth/, dashboards/, admin/, teacher/, wali/, welcome.blade.php
routes/web.php  (semua route; lihat `php artisan route:list`)
tests/Feature/  (121 tes, 556 assertions, 2026-11-26)
```

## 3. ERD (ringkas)

```
roles 1──* users ──? teachers (users.teacher_id)
departments 1──* classes, subjects
academic_years 1──* semesters, classes, curricula
semesters 1──* (scores, attendances, report_cards, ...)
teachers 1──* teaching_assignments (teacher, subject, class, ay, sem)
teachers 1──* homeroom_assignments (teacher, class, ay, sem)
classes 1──* students
subjects 1──* assessment_components, learning_outcomes
curricula 1──* learning_outcomes 1──* learning_objectives
students *──* assessment_scores (student, subject, component, teacher, class, ay, sem)
students *──* student_attendances (student, class, ay, sem, date, status H/S/I/A)
students *──* student_achievements, student_extracurriculars
students 1──* teacher_descriptions, report_cards
report_cards 1──* report_card_subjects, report_card_notes, report_card_approvals
report_cards *──1 users (issued_by)
internships 1──* internship_assessments
students 1──* internships, competency_exams
users 1──* activity_logs
system_settings (key/value JSONB)
```

## 4. Kamus data (tabel utama)

| Tabel | Kolom kunci | Keterangan |
|---|---|---|
| roles | id, name, slug (admin/guru/wali_kelas/kepala_sekolah/waka_kurikulum), description | Role pengguna |
| users | id, name, email unique, password (hash), role_id, teacher_id nullable, is_active | Akun login; teacher_id menghubungkan guru/wali ke data guru |
| departments | id, code unique, name, description | Jurusan / kompetensi keahlian |
| academic_years | id, name, start_date, end_date, is_active | Tahun ajaran |
| semesters | id, academic_year_id, name, is_active | Semester |
| teachers | id, user_id nullable unique, nip unique, name, gender L/P, phone, address, photo | Data guru |
| classes | id, department_id, academic_year_id, name, level, homeroom_teacher_id | Kelas |
| students | id, user_id nullable unique, nis unique, nisn unique, name, gender, birth_date, address, class_id | Data siswa |
| subjects | id, code unique, name, department_id nullable, is_active | Mata pelajaran |
| curricula | id, name, academic_year_id, description | Kurikulum |
| learning_outcomes | id, curriculum_id, subject_id, code, description | Capaian pembelajaran (CP) |
| learning_objectives | id, learning_outcome_id, code, description | Tujuan pembelajaran (TP) |
| teaching_assignments | id, teacher_id, subject_id, class_id, academic_year_id, semester_id, hours | Penugasan guru mengajar |
| homeroom_assignments | id, teacher_id, class_id, academic_year_id, semester_id; unique(class, ay, sem) | Penugasan wali kelas |
| assessment_components | id, name, type (formatif/sumatif/praktik/proyek), weight, subject_id, curriculum_id, kktp (default 75), is_active | Komponen penilaian; bobot per (mapel, kurikulum), total harus 100 |
| assessment_scores | id, student_id, subject_id, teacher_id, class_id, academic_year_id, semester_id, component_id, score nullable, note, status (draft/siap_kirim/terkirim/dikunci/perlu_revisi); unique(student, subject, component, ay, sem) | Nilai per komponen |
| score_revisions | id, assessment_score_id, requested_by, reason, status, decided_by, decided_at, old_score, new_score | Alur revisi nilai |
| teacher_descriptions | id, student_id, subject_id, teacher_id, academic_year_id, semester_id, description; unique(student, subject, ay, sem) | Deskripsi capaian guru mapel |
| student_attendances | id, student_id, class_id, academic_year_id, semester_id, date, status (H/S/I/A), note; unique(student_id, date) | Presensi |
| student_achievements | id, student_id, academic_year_id, semester_id, title, description, level | Prestasi siswa |
| extracurriculars | id, name unique, description | Master ekstrakurikuler |
| student_extracurriculars | id, student_id, extracurricular_id, academic_year_id, semester_id, score, description; unique(student, ekskul, ay, sem) | Ekskul siswa |
| report_cards | id, student_id, class_id, academic_year_id, semester_id, status (draft/diajukan/divalidasi/diterbitkan/dikunci), issued_at, locked_at, issued_by; unique(student, ay, sem) | Rapor |
| report_card_subjects | id, report_card_id cascade, subject_id, final_score, description; unique(rapor, mapel) | Snapshot nilai akhir + deskripsi saat terbit |
| report_card_notes | id, report_card_id cascade, note_type (wali_kelas/kepala_sekolah), content | Catatan rapor |
| report_card_approvals | id, report_card_id cascade, approver_id, status (pending/approved/rejected), note, approved_at | Persetujuan rapor |
| internships | id, student_id, company_name, start_date, end_date, supervisor, grade | Penempatan PKL |
| internship_assessments | id, internship_id cascade, aspect, score, note | Penilaian PKL per aspek |
| competency_exams | id, student_id, subject_id, score, note, exam_date | Hasil UKK (kolom note diawali "Penguji: ..." bila ada penguji) |
| activity_logs | id, user_id nullable, action, model_type, model_id, ip_address, user_agent, metadata JSONB | Audit log (tanpa password/token) |
| system_settings | id, key unique, value JSONB, description | Pengaturan sistem (mis. nama sekolah, KKTP default) |
| Profil sekolah + cetak (Fase A, tanpa tabel baru) | system_settings | Kunci: school_name, school_npsn, school_address, school_city, school_phone, school_logo (path relatif di disk public), principal_name, principal_nip, print_city, print_date, print_watermark. Dilayani SchoolProfileService; logo diunggah ke storage/app/public/school/. |

Catatan PostgreSQL: kolom `metadata` (activity_logs) dan `value` (system_settings)
bertipe JSONB dengan index GIN. Di SQLite pengujian, JSONB diperlakukan sebagai JSON.

## 5. Daftar migrations

| File | Isi |
|---|---|
| 0001_01_01_000000_create_users_table.php | users, password_reset_tokens, sessions (bawaan Laravel) |
| 0001_01_01_000001_create_cache_table.php | cache, cache_locks (bawaan Laravel) |
| 0001_01_01_000002_create_jobs_table.php | jobs, job_batches, failed_jobs (bawaan Laravel; tidak dipakai) |
| 2026_01_01_000000_create_core_tables.php | Semua tabel inti (lihat bagian 4) |
| 2026_01_02_000000_add_metadata_to_activity_logs.php | Kolom metadata JSONB + index GIN |
| 2026_01_03_000000_create_system_settings_table.php | system_settings |
| 2026_01_04_000000_create_score_revisions_table.php | score_revisions |
| 2026_01_05_000000_add_teacher_id_to_users_table.php | users.teacher_id |
| 2026_01_06_000000_add_unique_to_student_attendances.php | unique(student_id, date) |

## 6. Role dan permission

| Fitur | Admin | Guru Mapel | Wali Kelas | Kepala Sekolah | Waka Kurikulum |
|---|---|---|---|---|---|
| Data master (jurusan, guru, siswa, kelas, mapel, tahun ajaran, semester, kurikulum) | CRUD penuh | — | — | — | — |
| Profil Sekolah & Pengaturan Cetak | Kelola | — | — | — | — |
| Monitoring status nilai + kelengkapan rapor | Ya | — | — | Ya | Ya |
| Kunci rapor yang sudah diterbitkan | Ya | — | — | Ya | — |
| Penugasan guru / wali kelas | Kelola | — | — |
| Komponen penilaian | — | CRUD (mapel ampuannya) | — |
| Input nilai | — | Ya, hanya penugasannya | — (read-only rekap) |
| Submit / kunci nilai | — | Ya | — |
| Revisi nilai | — | Minta + setujui (bukan peminta) | — |
| Presensi | Monitor semua | Input + koreksi (penugasannya) | Rekap kelasnya |
| Catatan wali, prestasi, ekskul | — | — | CRUD kelasnya |
| Kelengkapan + preview + pengajuan rapor | — | — | Ya, kelasnya |
| Setujui / kembalikan / terbitkan / kunci rapor | Ya | — | — (pengajuan saja) |
| PDF rapor | Ya | — (403) | Ya, kelasnya |
| PKL | CRUD penuh + penilaian | Nilai penempatan yang dibimbing | Hanya-baca kelasnya |
| UKK | CRUD penuh | Hanya-baca mapel ampuannya | Hanya-baca kelasnya |
| Monitoring nilai belum dikirim | Ya | — | — |

Semua batas di atas ditegakkan di backend. Menu yang tidak berhak tidak ditampilkan,
tetapi pemeriksaan sesungguhnya ada di middleware dan controller.

## 7. Alur penilaian

1. Admin membuat tahun ajaran, semester, kurikulum, mapel, kelas, guru, siswa,
   lalu penugasan guru (teaching_assignments).
2. Guru membuat komponen penilaian per mapel/kurikulum (tipe formatif/sumatif/
   praktik/proyek, bobot, KKTP). Total bobot aktif per (mapel, kurikulum) harus 100.
3. Guru menginput nilai per siswa per komponen (draft). Input massal tersedia
   (`POST /guru/assessments/bulk`) dengan deteksi konflik (`loaded_at`).
4. Nilai akhir dihitung `AssessmentService::finalScore`:
   `Σ(nilai × bobot) ÷ Σ(bobot komponen yang bernilai)`; komponen kosong dikecualikan
   dari penyebut (bukan 0); pembulatan 2 desimal; KKTP default 75 (dapat diatur per
   komponen atau via SystemSetting).
5. Guru submit (`terkirim`) — hanya draft milik guru itu yang berubah.
6. Nilai terkirim dapat dikunci (`dikunci`) atau diajukan revisi.
7. Deskripsi capaian ditulis guru per siswa per mapel (otomatis "Mencapai/Belum
   mencapai KKTP" bila kosong).

## 8. Alur revisi nilai

1. Guru meminta revisi atas nilai `terkirim`/`dikunci` (alasan wajib) → status
   `perlu_revisi`, baris `score_revisions` dibuat.
2. Guru lain (bukan peminta) menyetujui/menolak. Menyetujui menerapkan nilai baru
   dan mengembalikan status `terkirim`; menolak mengembalikan status `terkirim`
   tanpa mengubah nilai.
3. Semua langkah tercatat di `activity_logs` dan `score_revisions`.

## 9. Alur rapor

Status dokumen (pemetaan ke enum tabel `report_cards.status`):

| Status dokumen | Enum tabel | Arti |
|---|---|---|
| Belum Lengkap | draft | Komponen wajib belum lengkap |
| Siap Diperiksa | draft (lengkap) | Semua komponen lengkap, menunggu pengajuan |
| Diajukan | diajukan | Wali kelas mengajukan |
| Perlu Perbaikan | draft + approval rejected | Admin mengembalikan dengan alasan |
| Disetujui | divalidasi | Admin menyetujui |
| Diterbitkan | diterbitkan | Rapor diterbitkan + snapshot disimpan |
| Terkunci | dikunci | Rapor terkunci setelah terbit |

Alur:
1. Guru menyelesaikan dan mengirim nilai.
2. Wali kelas memeriksa kelengkapan (`ReportCompletenessService`: 4 komponen wajib
   sementara — nilai terkirim semua mapel, catatan wali, presensi tercatat, identitas
   siswa lengkap), mengisi catatan/prestasi/ekskul, preview, lalu mengajukan.
3. Admin memeriksa, menyetujui (approval) atau mengembalikan dengan alasan.
4. Admin menerbitkan: snapshot nilai akhir + deskripsi disalin ke
   `report_card_subjects`, `issued_at`/`issued_by` dicatat, dalam satu transaksi.
   Penerbitan ganda dicegah.
5. PDF dibuat dari snapshot (`ReportCardPdfService`, DomPDF, A4, nama file
   `rapor-{nis}-{semester}.pdf`), hanya dapat diunduh admin dan wali kelasnya.
6. Rapor diterbitkan dapat dikunci (`locked_at`). Koreksi setelah terbit ditolak
   (403 + log `correction_rejected`); koreksi resmi harus melalui prosedur yang
   disepakati sekolah (belum ditetapkan).
7. Perubahan data live setelah terbit tidak mengubah snapshot maupun PDF.

## 10. Konfigurasi dan layanan eksternal

- Tidak ada integrasi eksternal (tidak ada mail, queue, storage pihak ketiga, atau API).
- Pengaturan sistem tersimpan di `system_settings` (mis. `school_name`, KKTP default).
- Semua data operasional berasal dari PostgreSQL (produksi).

## 11. Fase F — Cetak tambahan, pembina ekstra, profil guru, dashboard admin (RDM)

- **Cetak tambahan (DomPDF):** sampul (`sampul-{nis}-{semester}.pdf`), identitas
  (`identitas-{nis}-{semester}.pdf`), transkrip (`transkrip-{nis}.pdf`) untuk wali
  kelasnya; leger kelas (HTML + PDF landscape) untuk admin. Sampul/identitas
  tersedia untuk semua status rapor; transkrip hanya untuk rapor yang sudah
  diterbitkan/dikunci karena memakai snapshot `report_card_subjects`.
- **Leger — pilihan sumber data (LedgerService):** satu aturan per kelas +
  periode. Bila SEMUA siswa kelas itu sudah punya rapor diterbitkan/dikunci
  pada periode tersebut, dipakai snapshot (`report_card_subjects`); bila
  belum, dipakai nilai akhir live (`AssessmentService::finalScore`, sel yang
  gagal dihitung tampil '-'). Label sumber ditampilkan di halaman dan PDF.
- **Pembina ekstrakurikuler:** `extracurriculars.supervisor_id` (nullable, FK
  teachers nullOnDelete). Admin menetapkan pembina di `/admin/extracurriculars`;
  pembina menilai binaannya lewat `/guru/ekstra-binaan` pada tabel
  `student_extracurriculars` yang sama dengan pengisian wali (last-writer-wins,
  setiap penyimpanan dicatat di `activity_logs`).
- **Profil guru:** `/guru/profil` dan `/wali/profil` — ubah nama, telepon,
  alamat (tabel `teachers` + nama user terkait) dan ganti password (password
  lama wajib benar). Foto sengaja tidak dibuat: kolom `photo` ada, tetapi
  belum dipakai di halaman mana pun.
- **Dashboard admin:** panel "Status Pengiriman Nilai" (agregasi per mapel
  dari `MonitoringService::scoreStatus` Fase A — draft vs terkirim vs
  dikunci pada periode aktif) dan "Aktivitas Guru Terakhir" (10 baris
  terbaru `activity_logs`). Keduanya memakai data asli.

## 12. Keamanan (ringkas; detail di STAGE-12-PROGRESS.md)

- Login: session regenerate (anti fixation), logout invalidate + regenerasi token,
  rate limit 10 percobaan/menit (`throttle:10,1`).
- CSRF aktif di grup `web`; semua form memakai `@csrf`.
- XSS: semua output Blade memakai `{{ }}` (escaped).
- SQL injection: Eloquent/query builder berparameter; filter divalidasi.
- Mass assignment: semua model memakai `$fillable` eksplisit.
- IDOR: cakupan guru/wali diperiksa di controller (penugasan, homeroom).
- Audit log: `activity_logs` mencatat pelaku, waktu, aksi, objek, metadata (tanpa secret).
- Secret: `.env` tidak masuk version control; `.env.example` hanya placeholder.
