# E-Rapor SMK — Analisis & Rancangan

## 1. Analisis Kebutuhan Sistem

### Tujuan
Sistem manajemen informasi akademik digital (E-Rapor) untuk SMK, mencakup pengelolaan data akademik, penilaian, rapor PDF, PKL/UKK, presensi, dan arsip semester.

### Peran
- **Admin**: kelola data master, pembagian tugas, pengaturan penilaian, monitoring, validasi & penerbitan rapor, audit log.
- **Guru Mata Pelajaran**: input nilai formatif/sumatif/praktik/proyek, deskripsi capaian, pengiriman nilai.
- **Wali Kelas**: rekap nilai, catatan, prestasi, ekstrakurikuler, monitoring kelengkapan rapor, pengajuan rapor, cetak.

### Fitur Utama
CRUD data master (guru, siswa, jurusan, kelas, mapel, tahun ajaran, semester, kurikulum, pengguna), pembagian tugas guru, pengaturan penilaian fleksibel, input nilai spreadsheet, workflow rapor, PKL & UKK, cetak PDF massal, arsip semester.

### Non-Fungsional
Bahasa Indonesia, RBAC backend, validasi, pagination/search/filter, responsive, audit log, transaction, secure upload.

## 2. Arsitektur Aplikasi

- **Backend**: Laravel 12, PHP 8.5, postgeSQL.
- **Frontend**: Blade + Tailwind CSS + Alpine.js.
- **Auth**: Laravel Auth + RBAC (roles, permissions, role_permissions).
- **PDF**: DomPDF, **Excel**: Laravel Excel.
- **Pattern**: MVC + Service classes + Form Requests + Policies + Resources.
- **Struktur modular per domain** (Academic, Assessment, Report, PKL).

Layers:
- Presentation: Blade components, layouts (admin/guru/wali).
- Application: Controllers, FormRequests, Policies.
- Domain: Services, Models, Enums.
- Infrastructure: Repositories (optional), Migrations, Seeders.

## 3. ERD (Ringkasan)

Entitas inti dan relasi:
- users 1—1 teachers/students
- roles M—N permissions (role_permissions)
- departments 1—M classes
- academic_years 1—M semesters
- classes M—1 departments, M—1 academic_years
- subjects M—1 departments (opsional)
- curricula 1—M learning_outcomes 1—M learning_objectives
- teaching_assignments: teacher, subject, class, academic_year, semester
- homeroom_assignments: teacher, class, academic_year, semester
- assessment_components (configurable, per subject/curriculum)
- assessment_scores: student, subject, teacher, class, academic_year, semester, component
- student_attendances: student, class, academic_year, semester, date
- student_achievements, extracurriculars, student_extracurriculars
- teacher_descriptions: student, subject, teacher, semester
- report_cards: student, class, academic_year, semester; 1—M report_card_subjects; 1—M report_card_notes; 1—M report_card_approvals
- internships, internship_assessments, competency_exams
- activity_logs

Prinsip: setiap nilai terhubung ke siswa, mapel, guru, kelas, tahun ajaran, semester, komponen. Arsip semester terjaga karena data selalu discope (academic_year_id, semester_id).

## 4. Daftar Tabel & Relasi

1. users (id, name, email, password, role_id, ...)
2. roles (id, name, slug)
3. permissions (id, name, slug)
4. role_permissions (role_id, permission_id)
5. teachers (id, user_id, nip, name, gender, phone, address, photo)
6. students (id, user_id, nis, nisn, name, gender, birth_date, address, class_id, ...)
7. departments (id, code, name)
8. classes (id, department_id, academic_year_id, name, level, homeroom_teacher_id)
9. academic_years (id, name, start_date, end_date, is_active)
10. semesters (id, academic_year_id, name, is_active)
11. subjects (id, code, name, department_id nullable)
12. curricula (id, name, academic_year_id)
13. learning_outcomes (id, curriculum_id, subject_id, code, description)
14. learning_objectives (id, learning_outcome_id, code, description)
15. teaching_assignments (id, teacher_id, subject_id, class_id, academic_year_id, semester_id, hours)
16. homeroom_assignments (id, teacher_id, class_id, academic_year_id, semester_id)
17. assessment_components (id, name, type, weight, subject_id nullable, curriculum_id nullable)
18. assessment_scores (id, student_id, subject_id, teacher_id, class_id, academic_year_id, semester_id, component_id, score, note)
19. student_attendances (id, student_id, class_id, academic_year_id, semester_id, date, status)
20. student_achievements (id, student_id, academic_year_id, semester_id, title, description)
21. extracurriculars (id, name)
22. student_extracurriculars (id, student_id, extracurricular_id, academic_year_id, semester_id, score, description)
23. teacher_descriptions (id, student_id, subject_id, teacher_id, academic_year_id, semester_id, description)
24. report_cards (id, student_id, class_id, academic_year_id, semester_id, status, issued_at, locked_at)
25. report_card_subjects (id, report_card_id, subject_id, final_score, description)
26. report_card_notes (id, report_card_id, note_type, content)
27. report_card_approvals (id, report_card_id, approver_id, status, note, approved_at)
28. internships (id, student_id, company_name, start_date, end_date, supervisor)
29. internship_assessments (id, internship_id, aspect, score, note)
30. competency_exams (id, student_id, subject_id, score, note, exam_date)
31. activity_logs (id, user_id, action, model_type, model_id, ip, created_at)

## 5. Struktur Folder (Laravel)

```
app/
  Http/Controllers/{Admin,Teacher,Homeroom,Auth}
  Http/Requests/
  Http/Middleware/
  Models/
  Policies/
  Services/
  Enums/
resources/views/{layouts,components,admin,teacher,homeroom,auth}
database/migrations, seeders, factories
routes/web.php
```

## 6. Rencana Implementasi Bertahap

- TAHAP 1: Analisis & arsitektur (selesai dokumen ini).
- TAHAP 2: Setup Laravel 12 + DB migrations.
- TAHAP 3: Auth + RBAC.
- TAHAP 4: Layout & dashboard 3 role.
- TAHAP 5: CRUD data master.
- TAHAP 6: Kurikulum & pembagian tugas.
- TAHAP 7: Input & pengolahan nilai.
- TAHAP 8: Rekap nilai & workflow wali kelas.
- TAHAP 9: Rapor & cetak PDF.
- TAHAP 10: PKL & UKK.
- TAHAP 11: Testing & optimasi.

Setiap tahap: analisis, implementasi, jalankan, cek error, uji, perbaiki, dokumentasi. Tidak lanjut sebelum fitur inti tahap sebelumnya berfungsi.

## Database

Primary database: PostgreSQL (Laravel pgsql driver). Use JSONB where justified.
