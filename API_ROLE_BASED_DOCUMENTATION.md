# 🔐 DAISY API - Role-Based Access Documentation

**DEPILAR Accreditation Information System (DAISY)**  
**Version:** 1.0  
**Last Updated:** January 24, 2026

---

## 📋 Table of Contents

1. [Role Overview](#role-overview)
2. [Super Admin](#super-admin)
3. [Asesi (DE - Desk Evaluator)](#asesi-de)
4. [Asesor](#asesor)
5. [Validator](#validator)
6. [Verifikator](#verifikator)
7. [Admin Universitas (PT)](#admin-universitas)
8. [Admin Prodi (PS/UPPS/PT)](#admin-prodi)
9. [Authentication Endpoints](#authentication-endpoints)

---

## 🎭 Role Overview

### Available Roles in DAISY System:

| Role | Name | Alias | Description |
|------|------|-------|-------------|
| `super_admin` | Super Admin | Super Admin | Full system access |
| `asesi` | Asesi | DE (Desk Evaluator) | LAMDEPILAR staff - Review & evaluate accreditation assessments |
| `asesor` | Asesor | Asesor | Conduct assessment of accreditation documents |
| `validator` | Validator | Validator | Validate assessor evaluation results |
| `verifikator` | Verifikator | Verifikator | Verify documents and data |
| `admin_univ` | Admin Universitas | PT | University administrator |
| `admin_prodi` | Admin Prodi | PS/UPPS/PT | Study program administrator |
| `default` | Default User | Default | Limited access |

### Test Credentials:
```
Email: superadmin@daisy.lamdepilar.or.id
Password: =Secret1234

Email: asesor1@daisy.lamdepilar.or.id
Password: =Secret1234

Email: validator1@daisy.lamdepilar.or.id
Password: =Secret1234
```

---

## 🔴 Super Admin

### Access Level: **FULL SYSTEM ACCESS**

### Available Endpoints:

#### 1. **Dashboard**
```http
GET /dashboard
```
- **Purpose:** Super admin dashboard with system-wide statistics
- **Response:** Dashboard view with all system metrics

#### 2. **User Management** ⭐ Admin Only
```http
GET    /users                    # List all users
POST   /users                    # Create new user
GET    /users/{id}               # View user detail
PUT    /users/{id}               # Update user
DELETE /users/{id}               # Delete user

GET    /users/export             # Export users to Excel
GET    /users/template           # Download import template
POST   /users/import             # Import users from Excel
```

#### 3. **Master Data Management** ⭐ Admin Only
```http
# Universities
GET    /universities             # List universities
POST   /universities             # Create university
PUT    /universities/{id}        # Update university
DELETE /universities/{id}        # Delete university

# Study Programs
GET    /study-programs           # List study programs
POST   /study-programs           # Create study program
PUT    /study-programs/{id}      # Update study program
DELETE /study-programs/{id}      # Delete study program

# Indikator Penilaian
GET    /indikator-penilaian      # List indicators
POST   /indikator-penilaian      # Create indicator
PUT    /indikator-penilaian/{id} # Update indicator
DELETE /indikator-penilaian/{id} # Delete indicator

# Bobot Penilaian
GET    /bobot-penilaian          # List scoring weights
POST   /bobot-penilaian          # Create weight
PUT    /bobot-penilaian/{id}     # Update weight
DELETE /bobot-penilaian/{id}     # Delete weight
POST   /bobot-penilaian/{id}/toggle  # Toggle active status
GET    /bobot-penilaian/hitung/{asesmenId}/{categoryId}  # Calculate score
```

#### 4. **Kriteria & Elemen Management** ⭐ Admin Only
```http
# Kriteria
GET    /kriteria                 # List criteria
POST   /kriteria                 # Create criterion
PUT    /kriteria/{id}            # Update criterion
DELETE /kriteria/{id}            # Delete criterion

# Elemen Standar
GET    /elemen-standar           # List standard elements
POST   /elemen-standar           # Create element
PUT    /elemen-standar/{id}      # Update element
DELETE /elemen-standar/{id}      # Delete element

# Indikator
GET    /indikator                # List indicators
POST   /indikator                # Create indicator
PUT    /indikator/{id}           # Update indicator
DELETE /indikator/{id}           # Delete indicator
```

#### 5. **Asesmen Management**
```http
GET    /asesmen                  # List all assessments
POST   /asesmen                  # Create assessment
GET    /asesmen/{id}             # View assessment detail
PUT    /asesmen/{id}             # Update assessment
DELETE /asesmen/{id}             # Delete assessment

# User Assignment
POST   /asesmen/{id}/assign-user       # Assign user to assessment
POST   /asesmen/{id}/bulk-assign       # Bulk assign users
POST   /asesmen/{id}/update-role       # Update user role
DELETE /asesmen/{id}/remove-user/{userId}  # Remove user
GET    /asesmen/search-users           # Search users for assignment
```

#### 6. **Pengajuan Akreditasi (DE)**
```http
GET /de/pengajuan                # List accreditation submissions
```

#### 7. **Laporan (Reports)**
```http
GET /laporan                     # Reports dashboard
GET /laporan/statistik           # Statistical reports
GET /laporan/kinerja             # Performance reports
GET /laporan/export              # Export reports
```

#### 8. **All Public & Authenticated Endpoints**
Super Admin has access to ALL endpoints available to other roles.

---

## 🟠 Asesi (DE - Desk Evaluator)

### Access Level: **LAMDEPILAR STAFF - FULL DE OPERATIONS**

### Available Endpoints:

#### 1. **Dashboard**
```http
GET /dashboard
```
- **Purpose:** DE dashboard with accreditation reminders & statistics
- **Features:**
  - Upcoming expiring accreditations (7 months ahead)
  - Submission statistics by status
  - Recent activities

#### 2. **Pengingat Masa Akreditasi (Pemetaan)**
```http
GET /de/pemetaan                 # Accreditation mapping index
GET /de/pemetaan/{id}            # View mapping detail
```

#### 3. **Pengajuan Management**
```http
GET /de/pengajuan                # List submissions
GET /de/pengajuan/{id}           # View submission detail
```

#### 4. **Validasi Dokumen (LED/LKPS)**
```http
GET /de/validasi-dokumen         # List document validations
GET /de/validasi-dokumen/{id}    # View validation detail
```

#### 5. **Penugasan AK (Assessment Adequacy)**
```http
GET /de/penugasan-ak             # List AK assignments
GET /de/penugasan-ak/{id}        # View AK assignment detail
```

#### 6. **Validasi AK**
```http
GET /de/validasi-ak              # List AK validations
GET /de/validasi-ak/{id}         # View AK validation detail
```

#### 7. **Pelaksanaan AL (Field Assessment)**
```http
GET /de/pelaksanaan-al           # List AL executions
GET /de/pelaksanaan-al/{id}      # View AL execution detail
```

#### 8. **Pelaporan**
```http
# Pelaporan Validasi Dokumen
GET  /de/pelaporan/validasi-dokumen       # Index
GET  /de/pelaporan/validasi-dokumen/{id}  # Detail
POST /de/pelaporan/validasi-dokumen/{id}/upload    # Upload report
POST /de/pelaporan/validasi-dokumen/{id}/finalize  # Finalize report

# Pelaporan Validasi AK
GET  /de/pelaporan/validasi-ak            # Index
GET  /de/pelaporan/validasi-ak/{id}       # Detail
POST /de/pelaporan/validasi-ak/{id}/upload      # Upload report
POST /de/pelaporan/validasi-ak/{id}/finalize    # Finalize report

# Pelaporan AL
GET  /de/pelaporan/al                     # Index
GET  /de/pelaporan/al/{id}                # Detail
POST /de/pelaporan/al/{id}/upload         # Upload report
POST /de/pelaporan/al/{id}/finalize       # Finalize report
```

#### 9. **Hasil Akreditasi**
```http
GET  /hasil-akreditasi/asesmen/{id}       # View accreditation result
POST /hasil-akreditasi/asesmen/{id}/hitung-ak     # Calculate AK score
POST /hasil-akreditasi/asesmen/{id}/finalize-ak   # Finalize AK
POST /hasil-akreditasi/asesmen/{id}/hitung-al     # Calculate AL score
POST /hasil-akreditasi/asesmen/{id}/finalize-al   # Finalize AL
POST /hasil-akreditasi/asesmen/{id}/publish       # Publish result to prodi
GET  /hasil-akreditasi/asesmen/{id}/download/{format}  # Download report (pdf/excel)

# Penyampaian Hasil (Step 14)
GET  /hasil-akreditasi/{id}/form          # Form hasil akreditasi
POST /hasil-akreditasi/{id}/submit        # Submit hasil akreditasi
```

#### 10. **Pemetaan Akreditasi**
```http
GET /pemetaan                    # Mapping index
GET /pemetaan/timeline/ajax      # Timeline data (AJAX)
GET /pemetaan/calendar/ajax      # Calendar data (AJAX)
GET /pemetaan/table/ajax         # Table data (AJAX)
GET /pemetaan/export             # Export mapping data
GET /pemetaan/{id}               # View mapping detail
```

---

## 🟡 Asesor

### Access Level: **ASSESSMENT EVALUATION - AK & AL**

### Available Endpoints:

#### 1. **Dashboard**
```http
GET /dashboard
```
- **Purpose:** Asesor dashboard
- **Features:**
  - Active assignments
  - Pending offers
  - Recent assessment activities

#### 2. **Penawaran (Offers)**
```http
GET  /penawaran/baru             # New offers
GET  /penawaran/riwayat          # Offer history
GET  /penawaran/{id}             # View offer detail
POST /penawaran/{id}/terima      # Accept offer
POST /penawaran/{id}/tolak       # Reject offer
```

#### 3. **Penugasan (Assignments)**
```http
GET /penugasan/aktif             # Active assignments
GET /penugasan/selesai           # Completed assignments
GET /penugasan/riwayat           # Assignment history
GET /penugasan/{id}              # View assignment detail
POST /penugasan/{id}/update-status  # Update status
```

#### 4. **Proses AK (Assessment Adequacy)**
```http
# Berkas AK
GET  /ak/berkas                  # List AK documents
GET  /ak/berkas/{id}             # View AK document detail
POST /ak/berkas/{id}/nilai       # Save assessment score

# Download & Upload
GET  /ak/berkas/{id}/template    # Download template Excel
GET  /ak/berkas/{id}/export      # Download hasil penilaian (Excel)
POST /ak/berkas/{id}/import      # Upload Excel penilaian
GET  /ak/import-status/{id}      # Check import status

# Split (Jika ada perbedaan penilaian)
GET  /ak/split                   # List split assessments
GET  /ak/split/{id}              # View split detail
POST /ak/split/{id}/rekonsiliasi # Submit reconciliation

# Validasi
GET /ak/validasi                 # List validations
GET /ak/validasi/{id}            # View validation detail
```

#### 5. **Proses AL (Field Assessment)**
```http
# Berkas AL
GET  /al/berkas                  # List AL documents
GET  /al/berkas/{id}             # View AL document detail
POST /al/berkas/{id}/nilai       # Save assessment score

# Download & Upload
GET  /al/berkas/{id}/template    # Download template Excel
GET  /al/berkas/{id}/export      # Download hasil penilaian (Excel)
POST /al/berkas/{id}/import      # Upload Excel penilaian
GET  /al/import-status/{id}      # Check import status

# Jadwal AL
GET  /al/jadwal                  # List schedules
GET  /al/jadwal/{id}             # View schedule detail
POST /al/jadwal/{id}/konfirmasi  # Confirm schedule

# Dokumen AL
GET /al/dokumen                  # List AL documents
GET /al/dokumen/{id}/download    # Download document

# Laporan AL
GET  /al/laporan                 # List reports
GET  /al/laporan/{id}            # View report detail
POST /al/laporan                 # Submit report
GET  /al/berkas/{id}/laporan-pdf # Download AL report (PDF)
```

#### 6. **Dokumen Pedoman**
```http
GET /pedoman                     # Pedoman index
GET /pedoman/{kategori}          # View by category
GET /pedoman/{kategori}/{id}/download  # Download pedoman
```

#### 7. **Dokumen Administrasi**
```http
# Panduan
GET /dokumen/panduan             # List guides
GET /dokumen/panduan/{id}/download  # Download guide

# Instrumen
GET /dokumen/instrumen           # List instruments
GET /dokumen/instrumen/{id}/download  # Download instrument

# Template
GET /dokumen/template            # List templates
GET /dokumen/template/{id}/download  # Download template

# Surat
GET /dokumen/surat               # List letters
GET /dokumen/surat/{id}/download # Download letter
```

---

## 🟢 Validator

### Access Level: **VALIDATION OF ASSESSOR RESULTS**

### Available Endpoints:

#### 1. **Dashboard**
```http
GET /dashboard
```
- **Purpose:** Validator dashboard
- **Features:**
  - Pending validations
  - Completed validations
  - Statistics

#### 2. **Penawaran (Offers)**
```http
GET  /penawaran/baru             # New offers
GET  /penawaran/riwayat          # Offer history
GET  /penawaran/{id}             # View offer detail
POST /penawaran/{id}/terima      # Accept offer
POST /penawaran/{id}/tolak       # Reject offer
```

#### 3. **Penugasan (Assignments)**
```http
GET /penugasan/aktif             # Active assignments
GET /penugasan/selesai           # Completed assignments
GET /penugasan/riwayat           # Assignment history
GET /penugasan/{id}              # View assignment detail
```

#### 4. **Validasi LED/LKPS (Dokumen)**
```http
GET  /validator/dokumen          # List document validations
GET  /validator/dokumen/{assignment}  # View validation detail
POST /validator/dokumen/{assignment}/submit  # Submit validation

# Borang Validation
GET  /validator/dokumen/{assignment}/review  # Review borang
POST /validator/dokumen/{assignment}/approve # Approve borang
POST /validator/dokumen/{assignment}/reject  # Reject borang

# Download Excel
GET  /validator/dokumen/{assignment}/export  # Download review Excel
POST /validator/dokumen/{assignment}/import  # Upload review Excel
```

#### 5. **Validasi AK**
```http
GET  /validasi                   # List AK validations
GET  /validasi/asesor/{idAsesmen}/ak  # Validate specific asesor (AK)

# Validation Actions
POST /validasi/{idAsesmen}/asesor/{idAsesor}/validate  # Validate asesor score
POST /validasi/{idAsesmen}/asesor/{idAsesor}/approve   # Approve all scores
POST /validasi/{idAsesmen}/finalize  # Finalize validation

# Download Excel
GET  /validasi/{idAsesmen}/export    # Export comparison Excel
```

#### 6. **Validasi AL**
```http
GET /validasi/asesor/{idAsesmen}/al  # Validate specific asesor (AL)
```

#### 7. **Pelaporan (Jika ditugaskan)**
```http
# Pelaporan Validasi AK
GET  /pelaporan/validasi-ak           # Index
GET  /pelaporan/validasi-ak/{id}      # Detail
POST /pelaporan/validasi-ak/{id}/upload    # Upload report
POST /pelaporan/validasi-ak/{id}/finalize  # Finalize report
```

---

## 🔵 Verifikator

### Access Level: **DOCUMENT & DATA VERIFICATION**

### Available Endpoints:

#### 1. **Dashboard**
```http
GET /dashboard
```
- **Purpose:** Verifikator dashboard (same as Validator)

#### 2. **Penawaran (Offers)**
```http
GET  /penawaran/baru             # New offers
GET  /penawaran/riwayat          # Offer history
GET  /penawaran/{id}             # View offer detail
POST /penawaran/{id}/terima      # Accept offer
POST /penawaran/{id}/tolak       # Reject offer
```

#### 3. **Penugasan (Assignments)**
```http
GET /penugasan/aktif             # Active assignments
GET /penugasan/selesai           # Completed assignments
GET /penugasan/riwayat           # Assignment history
GET /penugasan/{id}              # View assignment detail
```

**Note:** Verifikator has similar access to Validator endpoints for document verification tasks.

---

## 🟣 Admin Universitas (PT)

### Access Level: **UNIVERSITY-LEVEL MANAGEMENT**

### Available Endpoints:

#### 1. **Dashboard**
```http
GET /dashboard
```
- **Purpose:** University admin dashboard
- **Features:**
  - Study programs under the university
  - Accreditation status overview
  - Submission statistics

#### 2. **Pemetaan Akreditasi**
```http
GET /pemetaan                    # Mapping index (university scope)
GET /pemetaan/timeline/ajax      # Timeline data
GET /pemetaan/calendar/ajax      # Calendar data
GET /pemetaan/table/ajax         # Table data
GET /pemetaan/export             # Export mapping
GET /pemetaan/{id}               # View detail
```

#### 3. **Profile & Settings**
```http
# Profile
GET  /profile                    # View profile
PUT  /profile                    # Update profile
POST /profile/avatar             # Update avatar
GET  /profile/password           # Password form
PUT  /profile/password           # Update password

# Settings
GET  /settings                   # View settings
PUT  /settings                   # Update settings
POST /settings/notification      # Update notification preferences
```

---

## 🟤 Admin Prodi (PS/UPPS/PT)

### Access Level: **STUDY PROGRAM MANAGEMENT**

### Available Endpoints:

#### 1. **Dashboard**
```http
GET /dashboard
```
- **Purpose:** Study program admin dashboard
- **Features:**
  - Program accreditation status
  - Upcoming deadlines
  - Submission tracking

#### 2. **Pengajuan Akreditasi**
```http
GET  /prodi/pengajuan            # List submissions
POST /prodi/pengajuan            # Create new submission
GET  /prodi/pengajuan/{id}       # View submission detail
PUT  /prodi/pengajuan/{id}       # Update submission
```

#### 3. **Pemetaan Akreditasi**
```http
GET /pemetaan                    # Mapping index (prodi scope)
GET /pemetaan/{id}               # View detail
GET /pemetaan/export             # Export data
```

#### 4. **Upload Dokumen**
```http
POST /prodi/pengajuan/{id}/upload-led     # Upload LED document
POST /prodi/pengajuan/{id}/upload-lkps    # Upload LKPS document
POST /prodi/pengajuan/{id}/upload-payment # Upload payment proof
```

#### 5. **Tracking Status**
```http
GET /prodi/tracking/{id}         # Track submission status
```

---

## 🔐 Authentication Endpoints

### Public Endpoints (No Auth Required):

#### 1. **Login**
```http
POST /login
```
**Request Body:**
```json
{
  "email": "asesor1@daisy.lamdepilar.or.id",
  "password": "=Secret1234"
}
```

#### 2. **Register**
```http
POST /register
```
**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### 3. **Logout**
```http
POST /logout
```

#### 4. **Forgot Password**
```http
POST /forgot-password
```
**Request Body:**
```json
{
  "email": "user@example.com"
}
```

#### 5. **Reset Password**
```http
POST /reset-password
```
**Request Body:**
```json
{
  "token": "reset_token",
  "email": "user@example.com",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

#### 6. **Change Password (First Time)**
```http
GET  /change-password-first      # Show form
POST /change-password-first      # Submit new password
POST /change-password-skip       # Skip change password
```

---

## 🔄 Common Endpoints (All Authenticated Users)

### Available to ALL logged-in users:

#### 1. **Dashboard**
```http
GET /dashboard
```
- Redirects to role-specific dashboard

#### 2. **Profile Management**
```http
GET  /profile                    # View profile
PUT  /profile                    # Update profile
POST /profile/avatar             # Update avatar
GET  /profile/password           # Password form
PUT  /profile/password           # Update password
```

#### 3. **Settings**
```http
GET  /settings                   # View settings
PUT  /settings                   # Update settings
POST /settings/notification      # Update notifications
```

#### 4. **Panduan (Guides)**
```http
GET /panduan                     # Guide index
GET /panduan/{slug}              # View specific guide
```

#### 5. **Bantuan (Help/Support)**
```http
GET  /bantuan                    # Help index
POST /bantuan/tiket              # Create support ticket
GET  /bantuan/tiket/{id}         # View ticket
POST /bantuan/tiket/{id}/reply   # Reply to ticket
```

#### 6. **Aktivitas (Activities)**
```http
GET /aktivitas                   # Activity log
GET /aktivitas/{id}              # View activity detail
```

#### 7. **Tugas (Tasks)**
```http
GET  /tugas                      # Task list
GET  /tugas/{id}                 # View task detail
POST /tugas/{id}/complete        # Mark task complete
```

#### 8. **Notifikasi (Notifications)**
```http
GET  /notifications              # Notification list
POST /notifications/{id}/read    # Mark as read
POST /notifications/read-all     # Mark all as read
```

---

## 📊 Response Formats

### Success Response:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // response data
  }
}
```

### Error Response:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Pagination Response:
```json
{
  "current_page": 1,
  "data": [...],
  "first_page_url": "http://...",
  "from": 1,
  "last_page": 10,
  "last_page_url": "http://...",
  "next_page_url": "http://...",
  "path": "http://...",
  "per_page": 15,
  "prev_page_url": null,
  "to": 15,
  "total": 150
}
```

---

## 🔒 Authorization Headers

All authenticated requests must include:

```http
Cookie: laravel_session=<session_token>
X-CSRF-TOKEN: <csrf_token>
```

Or for API requests:
```http
Authorization: Bearer <access_token>
```

---

## ⚠️ Important Notes

1. **Middleware Protection:**
   - `auth` - Requires authentication
   - `verified` - Requires email verification
   - `admin` - Super Admin only
   - `role:xxx` - Specific role(s) required

2. **CSRF Protection:**
   - All POST/PUT/DELETE requests require CSRF token
   - Get CSRF token from meta tag: `<meta name="csrf-token" content="...">`

3. **Role Switching:**
   - Users with multiple roles can switch active role
   - Use `POST /switch-role` with `role` parameter

4. **File Uploads:**
   - Max file size: 10MB for Excel, 5MB for PDF
   - Allowed formats: `.xlsx`, `.xls`, `.pdf`

5. **Queue Processing:**
   - Excel imports are processed in background
   - Check status with `/import-status/{id}` endpoint

---

## 📞 Support

For API support or questions:
- Email: support@lamdepilar.or.id
- Documentation: https://daisy.lamdepilar.or.id/panduan

---

**© 2026 LAMDEPILAR - DAISY System**
