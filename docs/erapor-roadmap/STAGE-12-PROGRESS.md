# STAGE 12 — QA, KEAMANAN, DAN AUDIT — LAPORAN

Tanggal: 2026-11-26. Lingkungan pengujian: PHP 8.3.6, Laravel 13, database pengujian SQLite :memory: (`phpunit.xml`), `php artisan test`.
Hasil akhir: **121 tes lolos, 0 gagal, 556 assertions.**

## 1. Ringkasan pekerjaan

- Menulis 15 tes keamanan baru di `tests/Feature/Security/StageTwelveSecurityTest.php` (IDOR, mass assignment, XSS, SQL injection, unique/FK constraint, CSRF, rate limiting login, audit log, akses PDF tanpa login, route kelas).
- Audit seluruh model terhadap `$fillable` vs validasi controller dan kolom tabel.
- Audit seluruh route terhadap middleware `role:` dan cakupan kelas/penugasan.
- Pemeriksaan `.env.example` (tidak ada secret asli), konfigurasi session, dan perilaku login/logout.

## 2. File yang dibuat/diubah

| File | Perubahan |
|---|---|
| `tests/Feature/Security/StageTwelveSecurityTest.php` | Baru, 15 tes |
| `app/Models/Student.php` | `$fillable` dilengkapi: gender, birth_date, address, class_id, user_id |
| `app/Models/Teacher.php` | `$fillable` dilengkapi: gender, phone, address, user_id, photo |
| `app/Models/Subject.php` | `$fillable` dilengkapi: department_id |
| `app/Http/Controllers/Admin/StudentController.php` | Validasi + class_id, birth_date, address; create/edit mengirim daftar kelas |
| `app/Http/Controllers/Admin/SubjectController.php` | Validasi + department_id; create/edit mengirim daftar jurusan |
| `resources/views/admin/students/create.blade.php` | Tambah pilih kelas, tanggal lahir, alamat |
| `resources/views/admin/students/edit.blade.php` | Tambah pilih kelas, tanggal lahir, alamat |
| `resources/views/admin/teachers/create.blade.php` | Tambah telepon, alamat |
| `resources/views/admin/teachers/edit.blade.php` | Tambah telepon, alamat |
| `resources/views/admin/subjects/create.blade.php` | Tambah pilih jurusan |
| `resources/views/admin/subjects/edit.blade.php` | Tambah pilih jurusan |
| `routes/web.php` | Route resource `admin/students` dan `admin/classes` ditambahkan (keduanya sebelumnya tidak ada); login POST diberi `throttle:10,1` |

## 3. Matriks pengujian (inti)

| # | Test case | Prasyarat | Hasil yang diharapkan | Hasil aktual | Status |
|---|---|---|---|---|---|
| 1 | Login/logout dan session | Akun 3 role | Login benar masuk dashboard; logout menghapus session | Sesuai | Lolos |
| 2 | Hak akses ketiga role | Akun admin/guru/wali | Route role lain 403; tamu diarahkan ke /login | Sesuai | Lolos |
| 3 | CRUD data master | Admin login | Departemen, guru, mapel, tahun ajaran, semester, kurikulum, kelas tersimpan | Sesuai (setelah perbaikan #B1) | Lolos |
| 4 | Kurikulum dan CP | Admin + kurikulum | CRUD kurikulum berjalan | Sesuai | Lolos |
| 5 | Penugasan guru dan wali kelas | Teaching/homeroom assignment | Guru hanya penugasannya; wali hanya kelasnya | Sesuai | Lolos |
| 6 | Input dan perhitungan nilai | Komponen bobot total 100 | Nilai akhir = rata-rata tertimbang, kosong dikecualikan | Sesuai | Lolos |
| 7 | Pengiriman dan revisi nilai | Nilai draft | Submit mengunci; revisi butuh persetujuan guru lain | Sesuai | Lolos |
| 8 | Presensi dan rekap | Kelas + tanggal | Satu catatan per siswa per tanggal; rekap akurat | Sesuai | Lolos |
| 9 | Catatan wali, prestasi, ekstrakurikuler | Wali login | CRUD dalam kelasnya; duplikasi ekskul ditolak | Sesuai | Lolos |
| 10 | Pengajuan dan persetujuan rapor | Rapor lengkap | Wali ajukan, admin setujui/kembalikan, terbitkan | Sesuai | Lolos |
| 11 | Snapshot dan PDF rapor | Rapor diterbitkan | Nilai live berubah tidak mengubah snapshot/PDF | Sesuai | Lolos |
| 12 | PKL dan UKK | Admin/guru/wali | Tumpang tindih ditolak; nilai PKL/UKK tidak masuk rapor | Sesuai | Lolos |
| 13 | Mass assignment nilai | Guru login | `status`/`teacher_id` dari request diabaikan | Sesuai (draft + teacher resolver) | Lolos |
| 14 | Mass assignment siswa/guru | Admin login | gender, kelas, telepon, alamat tersimpan | Sesuai (setelah perbaikan #B1) | Lolos |
| 15 | IDOR guru lain | Penugasan milik guru B | Guru A menyimpan nilai penugasan guru B → 403, tidak tersimpan | Sesuai | Lolos |
| 16 | IDOR wali kelas lain | Wali kelas X TKJ 1 | Preview siswa X TKJ 2 → 403 | Sesuai | Lolos |
| 17 | IDOR lintas role | Guru login | POST ke route wali → 403 | Sesuai | Lolos |
| 18 | Akses PDF tanpa login | Tamu | GET PDF rapor → redirect /login | Sesuai | Lolos |
| 19 | XSS | Nama siswa berisi `<script>` | Output ter-escape (`&lt;script&gt;`) | Sesuai | Lolos |
| 20 | SQL injection | Filter `subject_id=1 OR 1=1` | Validasi integer menolak; tidak ada 500/kebocoran | Sesuai | Lolos |
| 21 | Unique constraint nilai | Satu nilai sudah ada | Duplikat → QueryException (DB) | Sesuai | Lolos |
| 22 | Foreign key | ID tidak ada | Insert → QueryException (DB) | Sesuai | Lolos |
| 23 | CSRF | Grup `web` | Middleware anti-CSRF aktif di grup web | Sesuai (PreventRequestForgery) | Lolos |
| 24 | Rate limiting login | 11 percobaan gagal | Percobaan ke-11 → 429 | Sesuai (throttle:10,1) | Lolos |
| 25 | Audit log | Submit nilai | `activity_logs` berisi score.submitted + user_id, tanpa password | Sesuai | Lolos |

## 4. Temuan dan perbaikan

| ID | Severity | Temuan | Perbaikan |
|---|---|---|---|
| B1 | **High** | Mass-assignment gap: `Student::$fillable` hanya nis/nisn/name, padahal form dan controller mengirim gender (selalu kembali ke default L) dan tidak ada cara memberi kelas ke siswa; `Teacher::$fillable` hanya nip/name padahal controller memvalidasi gender/phone/address (semuanya hilang diam-diam); `Subject::$fillable` tanpa department_id. | `$fillable` dilengkapi di ketiga model; validasi dan form dilengkapi (kelas, tanggal lahir, alamat, telepon, jurusan). |
| B2 | **High** | Route resource `admin/students` dan `admin/classes` tidak pernah didaftarkan; `StudentController` dan `SchoolClassController` ada tapi tidak terjangkau (POST /admin/students → 404). Admin tidak bisa mengelola siswa dan kelas lewat UI. | Kedua route resource ditambahkan ke grup admin; tes regresi ditambahkan. |
| B2b | **High** | `SchoolClass` tidak punya relasi Eloquent, padahal `SchoolClassController@index` memakai `with(['department','academicYear'])` → halaman Data Kelas error 500. | Relasi `department`, `academicYear`, `homeroomTeacher`, `students` ditambahkan ke model. |
| B3 | **High** | Login tanpa rate limiting (brute force). | `throttle:10,1` pada POST /login (10 percobaan per menit per IP). |
| B4 | Medium | Akses guru PKL berbasis pencocokan nama di kolom `supervisor` (dari Stage 11): nama salah eja membuat penempatan tidak terlihat. | Belum diperbaiki (butuh FK pembimbing + migrasi baru). Dicatat sebagai risiko di laporan akhir; perlu keputusan sekolah. |
| B5 | Medium | Konvensi penguji UKK disimpan sebagai baris "Penguji: ..." di awal kolom `note` (tidak ada kolom penguji di skema). | Belum diubah; dicatat sebagai utang teknis di laporan akhir. |

## 5. Yang sudah diperiksa dan dinyatakan baik

- **Authentication:** login regenerasi session (anti session fixation), logout invalidate session + regenerasi token CSRF, password di-hash (`bcrypt`).
- **Authorization:** semua grup route memakai middleware `role:`; cakupan guru/wali diperiksa di controller (penugasan, homeroom), bukan hanya di menu.
- **CSRF:** middleware anti-CSRF aktif di grup `web` (semua form memakai `@csrf`).
- **XSS:** semua view memakai `{{ }}` (escaped); diuji pada nama siswa.
- **SQL injection:** semua query lewat Eloquent/query builder berparameter; filter divalidasi tipe integer.
- **Mass assignment:** semua model memakai `$fillable` eksplisit; tidak ada `$guarded = []`.
- **Secret:** `.env.example` hanya berisi placeholder; `.env` tidak masuk version control (ada di `.gitignore` bawaan Laravel); tidak ada kredensial di kode/dokumen.
- **Session:** cookie `http_only`, `same_site=lax`, lifetime 120 menit.
- **Audit log:** aksi penting tercatat (`score.created/submitted/locked`, `attendance.created/corrected`, `report_card.*`, `revision.*`, `assessment.*`, `internship.*`, `competency_exam.*`) dengan `user_id` dan waktu; metadata tidak berisi password/token.
- **Database:** unique constraint (nilai, presensi per tanggal, rapor per semester, ekskul), foreign key restrict/cascade sesuai desain; transaksi dipakai untuk submit massal, terbit rapor, dan operasi multi-tabel.

## 6. Yang TIDAK bisa diuji di lingkungan ini (langkah manual)

- **PostgreSQL:** pengujian berjalan di SQLite. Perilaku khusus PostgreSQL (kolom `jsonb` + index GIN pada `activity_logs.metadata` dan `system_settings.value`, perilaku migrasi di PostgreSQL) harus diuji di staging PostgreSQL sebelum produksi.
- **UI perangkat:** tampilan desktop pernah diperiksa lewat browser; tampilan tablet/smartphone (lebar ~390px) belum diperiksa secara visual. Semua view memakai class Tailwind responsif, tetapi pemeriksaan visual tetap diperlukan.
- **Backup/restore:** tidak ada database produksi; prosedur terdokumentasi di Stage 13 dan harus diuji di staging.
- **Upload file:** tidak ada fitur upload file di aplikasi (foto guru tidak diunggah lewat UI); tidak ada validasi upload yang perlu diuji.

## 7. Pernyataan

Tidak ada bug Critical/High yang terbuka setelah perbaikan B1–B3 (termasuk B2/B2b). Sisa temuan (B4, B5) berstatus Medium dan terdokumentasi sebagai risiko/utang teknis dengan alasan dan batasannya di laporan akhir. Stage 13 belum dimulai.
