# 🔌 DAISY Laravel API Endpoints Documentation

**Application:** DAISY - Sistem Akreditasi
**Framework:** Laravel 11.x  
**Authentication:** Session-based (Laravel default web guard)  
**CSRF Protection:** Enabled (except for login/register)  
**Generated:** January 22, 2026

---

## 📋 Table of Contents

1. [Authentication Overview](#authentication-overview)
2. [Public Endpoints (No Auth)](#public-endpoints-no-auth)
3. [Testing Endpoints (No Auth)](#testing-endpoints-no-auth)
4. [Authenticated Endpoints](#authenticated-endpoints)
5. [Admin-Only Endpoints](#admin-only-endpoints)
6. [Response Formats](#response-formats)
7. [Error Handling](#error-handling)

---

## 🔐 Authentication Overview

### Authentication Mechanism
- **Type:** Session-based authentication (Laravel web guard)
- **Driver:** Database sessions
- **Session Lifetime:** 120 minutes
- **CSRF Protection:** Required for all POST/PUT/DELETE requests (except login/register)
- **Cookie Name:** `laravel_session`

### Authentication Flow
1. POST `/login` - Get session cookie
2. Include session cookie in subsequent requests
3. Include CSRF token in headers: `X-CSRF-TOKEN`
4. POST `/logout` - Destroy session

### Required Headers for Authenticated Requests
```http
Cookie: laravel_session=<session_value>
X-CSRF-TOKEN: <csrf_token>
Content-Type: application/json (for JSON requests)
Accept: application/json (optional, for JSON responses)
```

---

## 🌍 Public Endpoints (No Auth)

### Home Page
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/` |
| **Controller** | Anonymous Function |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Display home page |
| **Request Body** | None |
| **Response** | HTML view |

---

### Authentication Endpoints

#### 1. Show Login Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/login` |
| **Controller** | `AuthController@showLogin` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Display login form |
| **Request Body** | None |
| **Response** | HTML view |

#### 2. Login (Authenticate)
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/login` |
| **Controller** | `AuthController@login` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No (Explicitly excluded) |
| **Purpose** | Authenticate user and create session |

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "captcha": "optional_in_production"
}
```

**Validation Rules:**
- `email`: required, valid email format
- `password`: required
- `captcha`: required in production environment

**Success Response (302):**
```
Redirect to /dashboard (or intended URL)
Set-Cookie: laravel_session=...
```

**Error Response (422):**
```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["Email atau password salah."]
  }
}
```

#### 3. Show Registration Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/register` |
| **Controller** | `AuthController@showRegister` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Display registration form |

#### 4. Register New User
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/register` |
| **Controller** | `AuthController@register` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No (Explicitly excluded) |
| **Purpose** | Create new user account |

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Validation Rules:**
- `name`: required, minimum 3 characters
- `email`: required, valid email, unique in users table
- `password`: required, minimum 6 characters

**Success Response (302):**
```
Redirect to /login
Flash message: "Registrasi berhasil! Silahkan login."
```

#### 5. Logout
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/logout` |
| **Controller** | `AuthController@logout` |
| **Auth Required** | ⚠️ Recommended but not enforced |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Destroy session and logout user |

**Request Headers:**
```http
X-CSRF-TOKEN: <csrf_token>
```

**Success Response (302):**
```
Redirect to /login
Session destroyed
```

#### 6. Show Forgot Password Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/forgot-password` |
| **Controller** | `PasswordResetController@showForgot` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Display forgot password form |

#### 7. Send Password Reset Link
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/forgot-password` |
| **Controller** | `PasswordResetController@sendResetLink` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Send password reset link to email |

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

#### 8. Show Reset Password Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/reset-password/{token}` |
| **Controller** | `PasswordResetController@showReset` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Display reset password form with token |

**URL Parameters:**
- `token`: Password reset token from email

#### 9. Reset Password
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/reset-password` |
| **Controller** | `PasswordResetController@resetPassword` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Reset user password with token |

**Request Body:**
```json
{
  "token": "reset_token_from_email",
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

## 🧪 Testing Endpoints (No Auth)

These endpoints are specifically designed for API testing and development without authentication.

#### 1. Get Statistics for Testing
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/api/test/pemetaan/stats` |
| **Controller** | `PemetaanAkreditasiController@getStatsForTesting` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Get accreditation mapping statistics (for testing/development) |

**Request Parameters:** None

**Success Response (200):**
```json
{
  "success": true,
  "message": "Statistik berhasil diambil",
  "data": {
    "total_program_studi": 800,
    "status": {
      "aktif": 712,
      "kedaluwarsa": 10,
      "belum_terakreditasi": 78
    },
    "segera_kedaluwarsa": {
      "dalam_3_bulan": 39,
      "dalam_6_bulan": 93,
      "dalam_12_bulan": 150
    },
    "by_peringkat": {
      "A": 50,
      "B": 300,
      "C": 200,
      "Unggul": 100
    },
    "persentase": {
      "aktif": 89.0,
      "kedaluwarsa": 1.25,
      "belum_terakreditasi": 9.75
    }
  },
  "timestamp": "2026-01-22T12:34:56+07:00"
}
```

#### 2. Get Timeline for Testing
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/api/test/pemetaan/timeline/{periode?}` |
| **Controller** | `PemetaanAkreditasiController@getTimelineForTesting` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Get timeline data per period (for testing/development) |

**URL Parameters:**
- `periode` (optional): `1bulan`, `3bulan`, `4bulan`, `6bulan`, `12bulan` (default: `3bulan`)

**Example Requests:**
```
GET /api/test/pemetaan/timeline
GET /api/test/pemetaan/timeline/1bulan
GET /api/test/pemetaan/timeline/6bulan
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Timeline data berhasil diambil",
  "data": {
    "selected_periode": "3bulan",
    "periode_label": "Per 3 Bulan (Triwulan)",
    "total_periods": 20,
    "timeline": [
      {
        "period": 1,
        "label": "Triwulan 1 - 2026",
        "start_date": "2026-01-22",
        "end_date": "2026-04-22",
        "count": 12,
        "is_urgent": true,
        "programs": [
          {
            "id": 1,
            "name": "Teknik Informatika",
            "university": "Universitas Indonesia",
            "degree_level": "S1",
            "peringkat": "A",
            "tanggal_kedaluwarsa": "2026-03-15",
            "status": "Aktif"
          }
        ]
      }
    ]
  },
  "timestamp": "2026-01-22T12:34:56+07:00"
}
```

#### 3. Get Calendar for Testing
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/api/test/pemetaan/calendar` |
| **Controller** | `PemetaanAkreditasiController@getCalendarForTesting` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Get 12-month calendar data (for testing/development) |

**Request Parameters:** None

**Success Response (200):**
```json
{
  "success": true,
  "message": "Calendar data berhasil diambil (12 bulan ke depan)",
  "data": {
    "total_months": 12,
    "calendar": [
      {
        "month": "Januari 2026",
        "month_num": 1,
        "year": 2026,
        "count": 14,
        "programs": [
          {
            "id": 1,
            "name": "Teknik Informatika",
            "university": "Universitas Indonesia",
            "degree_level": "S1",
            "peringkat": "A",
            "tanggal_kedaluwarsa": "2026-01-31",
            "status": "Aktif"
          }
        ]
      }
    ]
  },
  "timestamp": "2026-01-22T12:34:56+07:00"
}
```

#### 4. Get Programs for Testing
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/api/test/pemetaan/programs` |
| **Controller** | `PemetaanAkreditasiController@getProgramsForTesting` |
| **Auth Required** | ❌ No |
| **CSRF Required** | ❌ No |
| **Purpose** | Get study programs with filters (for testing/development) |

**Query Parameters (all optional):**
- `status`: Filter by status (`Aktif`, `Kedaluwarsa`, `Belum Terakreditasi`)
- `peringkat`: Filter by ranking (`A`, `B`, `C`, `Unggul`)
- `university_id`: Filter by university ID
- `degree_level_id`: Filter by degree level ID
- `search`: Search by program name
- `sort_by`: Sort field (`name`, `tanggal_kedaluwarsa`)
- `sort_order`: Sort order (`asc`, `desc`)
- `limit`: Number of results (default: 10)

**Example Requests:**
```
GET /api/test/pemetaan/programs?status=Aktif&limit=5
GET /api/test/pemetaan/programs?peringkat=A&sort_by=name
GET /api/test/pemetaan/programs?search=informatika&limit=20
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Program studi berhasil diambil",
  "data": {
    "total": 5,
    "limit": 5,
    "filters_applied": {
      "status": "Aktif",
      "peringkat": null,
      "university_id": null,
      "degree_level_id": null,
      "search": null,
      "sort_by": "tanggal_kedaluwarsa",
      "sort_order": "asc"
    },
    "programs": [
      {
        "id": 1,
        "name": "Teknik Informatika",
        "code": "TI001",
        "university": {
          "id": 1,
          "name": "Universitas Indonesia",
          "code": "UI"
        },
        "degree_level": {
          "id": 5,
          "name": "Sarjana",
          "code": "S1"
        },
        "category": {
          "id": 1,
          "name": "Akreditasi",
          "code": "AK"
        },
        "bentuk_pt": "Universitas",
        "email": "ti@ui.ac.id",
        "akreditasi": {
          "peringkat": "A",
          "tanggal_kedaluwarsa": "2026-03-15",
          "status": "Aktif",
          "hari_tersisa": 52
        }
      }
    ]
  },
  "timestamp": "2026-01-22T12:34:56+07:00"
}
```

---

## 🔒 Authenticated Endpoints

All endpoints below require authentication via session cookie.

### Required Headers
```http
Cookie: laravel_session=<session_value>
X-CSRF-TOKEN: <csrf_token>
```

---

### Dashboard & Profile

#### 1. Dashboard
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dashboard` |
| **Controller** | `DashboardController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No (GET request) |
| **Purpose** | Display role-based dashboard |
| **Response** | HTML view (role-specific dashboard) |

#### 2. Change Password First Time (Show Form)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/change-password-first` |
| **Controller** | `AuthController@showChangePasswordFirst` |
| **Auth Required** | ✅ Yes (auth) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display first-time password change form |

#### 3. Change Password First Time (Submit)
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/change-password-first` |
| **Controller** | `AuthController@changePasswordFirst` |
| **Auth Required** | ✅ Yes (auth) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update password on first login |

**Request Body:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

**Validation Rules:**
- `current_password`: required
- `new_password`: required, min:8, confirmed

#### 4. Skip Change Password
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/change-password-skip` |
| **Controller** | `AuthController@skipChangePassword` |
| **Auth Required** | ✅ Yes (auth) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Skip first-time password change |

---

### Pemetaan Akreditasi (Accreditation Mapping)

#### 1. Pemetaan Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pemetaan` |
| **Controller** | `PemetaanAkreditasiController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display accreditation mapping dashboard |

**Query Parameters (optional):**
- `university_id`: Filter by university
- `degree_level_id`: Filter by degree level
- `status_kedaluwarsa`: Filter by expiration status
- `peringkat`: Filter by ranking
- `search`: Search program name
- `sort_by`: Sort field
- `sort_order`: Sort order (`asc`/`desc`)
- `page`: Pagination page

#### 2. Get Timeline (AJAX)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pemetaan/timeline/ajax` |
| **Controller** | `PemetaanAkreditasiController@getTimelineAjax` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Get timeline data via AJAX |

**Query Parameters:**
- `periode`: Period type (`1bulan`, `3bulan`, `6bulan`, `12bulan`)

**Response Type:** JSON

#### 3. Get Calendar (AJAX)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pemetaan/calendar/ajax` |
| **Controller** | `PemetaanAkreditasiController@getCalendarAjax` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Get calendar data via AJAX |

**Response Type:** JSON

#### 4. Get Table (AJAX)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pemetaan/table/ajax` |
| **Controller** | `PemetaanAkreditasiController@getTableAjax` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Get table data via AJAX with filters |

**Query Parameters:** Same as Pemetaan Index

#### 5. Export Pemetaan
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pemetaan/export` |
| **Controller** | `PemetaanAkreditasiController@export` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Export accreditation mapping data (Excel/PDF) |

**Response Type:** File download

#### 6. Show Pemetaan Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pemetaan/{id}` |
| **Controller** | `PemetaanAkreditasiController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed view of study program accreditation |

**URL Parameters:**
- `id`: Study program ID

---

### Pengajuan Akreditasi DE (Accreditation Submission)

#### 1. DE Pengajuan
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/de/pengajuan` |
| **Controller** | `DEController@pengajuan` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display accreditation submission form (DE) |

---

### Penawaran Asesmen (Assessment Offers)

#### 1. Penawaran Baru
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/penawaran/baru` |
| **Controller** | `PenawaranController@baru` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display new assessment offers |

#### 2. Penawaran Riwayat
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/penawaran/riwayat` |
| **Controller** | `PenawaranController@riwayat` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display assessment offers history |

#### 3. Show Penawaran Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/penawaran/{id}` |
| **Controller** | `PenawaranController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed view of assessment offer |

**URL Parameters:**
- `id`: Offer ID

#### 4. Accept Penawaran
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/penawaran/{id}/terima` |
| **Controller** | `PenawaranController@terima` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Accept assessment offer |

**URL Parameters:**
- `id`: Offer ID

#### 5. Reject Penawaran
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/penawaran/{id}/tolak` |
| **Controller** | `PenawaranController@tolak` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Reject assessment offer |

**URL Parameters:**
- `id`: Offer ID

---

### Penugasan Asesmen (Assessment Assignments)

#### 1. Penugasan Aktif
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/penugasan/aktif` |
| **Controller** | `PenugasanController@aktif` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display active assessment assignments |

#### 2. Penugasan Selesai
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/penugasan/selesai` |
| **Controller** | `PenugasanController@selesai` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display completed assessment assignments |

#### 3. Penugasan Riwayat
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/penugasan/riwayat` |
| **Controller** | `PenugasanController@riwayat` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display assessment assignments history |

#### 4. Show Penugasan Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/penugasan/{id}` |
| **Controller** | `PenugasanController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed view of assignment |

**URL Parameters:**
- `id`: Assignment ID

#### 5. Update Penugasan Status
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/penugasan/{id}/update-status` |
| **Controller** | `PenugasanController@updateStatus` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update assignment status |

**URL Parameters:**
- `id`: Assignment ID

---

### Proses AK (AK Process)

#### 1. AK Berkas (Documents List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/ak/berkas` |
| **Controller** | `AKController@berkas` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display AK documents list |

#### 2. Show AK Berkas Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/ak/berkas/{id}` |
| **Controller** | `AKController@showBerkas` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed AK document |

**URL Parameters:**
- `id`: Document ID

#### 3. Save AK Nilai (Score)
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/ak/berkas/{id}/nilai` |
| **Controller** | `AKController@simpanNilai` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Save assessment score for AK document |

**URL Parameters:**
- `id`: Document ID

#### 4. AK Split (Assessment Split)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/ak/split` |
| **Controller** | `AKController@split` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display split assessment list |

#### 5. Show AK Split Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/ak/split/{id}` |
| **Controller** | `AKController@showSplit` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed split assessment |

**URL Parameters:**
- `id`: Split ID

#### 6. AK Rekonsiliasi
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/ak/split/{id}/rekonsiliasi` |
| **Controller** | `AKController@rekonsiliasi` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Perform reconciliation for split assessment |

**URL Parameters:**
- `id`: Split ID

#### 7. AK Upload (Upload Form)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/ak/upload` |
| **Controller** | `AKController@upload` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display upload form |

#### 8. Store AK Upload
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/ak/upload` |
| **Controller** | `AKController@storeUpload` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Upload AK document |

**Request Body:** Multipart form data with file

#### 9. Delete AK Upload
| Attribute | Value |
|-----------|-------|
| **Method** | DELETE |
| **Path** | `/ak/upload/{id}` |
| **Controller** | `AKController@deleteUpload` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Delete uploaded AK document |

**URL Parameters:**
- `id`: Upload ID

#### 10. AK Validasi (Validation List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/ak/validasi` |
| **Controller** | `AKController@validasi` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display validation list |

#### 11. Show AK Validasi Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/ak/validasi/{id}` |
| **Controller** | `AKController@showValidasi` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed validation view |

**URL Parameters:**
- `id`: Validation ID

---

### Asesmen Resource (CRUD)

#### 1. List Asesmen
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/asesmen` |
| **Controller** | `AsesmenController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | List all assessments |

#### 2. Create Asesmen Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/asesmen/create` |
| **Controller** | `AsesmenController@create` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show create assessment form |

#### 3. Store Asesmen
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/asesmen` |
| **Controller** | `AsesmenController@store` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Create new assessment |

#### 4. Show Asesmen Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/asesmen/{asesmen}` |
| **Controller** | `AsesmenController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed assessment view |

**URL Parameters:**
- `asesmen`: Assessment ID

#### 5. Edit Asesmen Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/asesmen/{asesmen}/edit` |
| **Controller** | `AsesmenController@edit` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show edit assessment form |

**URL Parameters:**
- `asesmen`: Assessment ID

#### 6. Update Asesmen
| Attribute | Value |
|-----------|-------|
| **Method** | PUT/PATCH |
| **Path** | `/asesmen/{asesmen}` |
| **Controller** | `AsesmenController@update` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update assessment |

**URL Parameters:**
- `asesmen`: Assessment ID

#### 7. Delete Asesmen
| Attribute | Value |
|-----------|-------|
| **Method** | DELETE |
| **Path** | `/asesmen/{asesmen}` |
| **Controller** | `AsesmenController@destroy` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Delete assessment |

**URL Parameters:**
- `asesmen`: Assessment ID

#### 8. Asesmen Dashboard
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/asesmen/dashboard` |
| **Controller** | `AsesmenController@dashboard` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display assessment overview dashboard |

#### 9. Assign User to Asesmen
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/asesmen/{id}/assign-user` |
| **Controller** | `AsesmenController@assignUser` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Assign user to assessment |

**URL Parameters:**
- `id`: Assessment ID

#### 10. Bulk Assign Users
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/asesmen/{id}/bulk-assign` |
| **Controller** | `AsesmenController@bulkAssign` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Assign multiple users to assessment |

**URL Parameters:**
- `id`: Assessment ID

#### 11. Update User Role in Asesmen
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/asesmen/{id}/update-role` |
| **Controller** | `AsesmenController@updateUserRole` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update user role in assessment |

**URL Parameters:**
- `id`: Assessment ID

#### 12. Remove User from Asesmen
| Attribute | Value |
|-----------|-------|
| **Method** | DELETE |
| **Path** | `/asesmen/{id}/remove-user/{userId}` |
| **Controller** | `AsesmenController@removeUser` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Remove user from assessment |

**URL Parameters:**
- `id`: Assessment ID
- `userId`: User ID

#### 13. Search Users (AJAX)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/asesmen/search-users` |
| **Controller** | `AsesmenController@searchUsers` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Search users via AJAX |

**Query Parameters:**
- `q`: Search query

---

### Proses AL (AL Process)

#### 1. AL Berkas
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/berkas` |
| **Controller** | `ALController@berkas` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display AL documents list |

#### 2. AL Jadwal (Schedule List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/jadwal` |
| **Controller** | `ALController@jadwal` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display AL schedule list |

#### 3. Show AL Jadwal Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/jadwal/{id}` |
| **Controller** | `ALController@showJadwal` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed schedule |

**URL Parameters:**
- `id`: Schedule ID

#### 4. Confirm AL Jadwal
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/al/jadwal/{id}/konfirmasi` |
| **Controller** | `ALController@konfirmasiJadwal` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Confirm AL schedule |

**URL Parameters:**
- `id`: Schedule ID

#### 5. AL Dokumen (Documents List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/dokumen` |
| **Controller** | `ALController@dokumen` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display AL documents |

#### 6. Download AL Dokumen
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/dokumen/{id}/download` |
| **Controller** | `ALController@downloadDokumen` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Download AL document |

**URL Parameters:**
- `id`: Document ID

#### 7. AL Upload Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/upload` |
| **Controller** | `ALController@upload` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display upload form |

#### 8. Store AL Upload
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/al/upload` |
| **Controller** | `ALController@storeUpload` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Upload AL document |

#### 9. Delete AL Upload
| Attribute | Value |
|-----------|-------|
| **Method** | DELETE |
| **Path** | `/al/upload/{id}` |
| **Controller** | `ALController@deleteUpload` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Delete uploaded AL document |

**URL Parameters:**
- `id`: Upload ID

#### 10. AL Laporan (Reports List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/laporan` |
| **Controller** | `ALController@laporan` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display AL reports list |

#### 11. Show AL Laporan Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/al/laporan/{id}` |
| **Controller** | `ALController@showLaporan` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed AL report |

**URL Parameters:**
- `id`: Report ID

#### 12. Store AL Laporan
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/al/laporan` |
| **Controller** | `ALController@storeLaporan` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Create new AL report |

---

### Banding (Appeal)

#### 1. Banding Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/banding` |
| **Controller** | `BandingController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display appeals list |

#### 2. Show Banding Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/banding/{id}` |
| **Controller** | `BandingController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed appeal view |

**URL Parameters:**
- `id`: Appeal ID

#### 3. Accept Banding
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/banding/{id}/terima` |
| **Controller** | `BandingController@terima` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Accept appeal |

**URL Parameters:**
- `id`: Appeal ID

#### 4. Reject Banding
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/banding/{id}/tolak` |
| **Controller** | `BandingController@tolak` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Reject appeal |

**URL Parameters:**
- `id`: Appeal ID

#### 5. Submit Banding
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/banding/{id}/submit` |
| **Controller** | `BandingController@submit` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Submit appeal |

**URL Parameters:**
- `id`: Appeal ID

---

### Pedoman AK (AK Guidelines)

#### 1. Pedoman Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pedoman` |
| **Controller** | `PedomanController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display guidelines index |

#### 2. Pedoman by Category
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pedoman/{kategori}` |
| **Controller** | `PedomanController@kategori` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display guidelines by category |

**URL Parameters:**
- `kategori`: Category slug

#### 3. Download Pedoman
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/pedoman/{kategori}/{id}/download` |
| **Controller** | `PedomanController@download` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Download guideline document |

**URL Parameters:**
- `kategori`: Category slug
- `id`: Document ID

---

### Dokumen Administrasi AL (AL Administration Documents)

#### 1. Panduan (Guide List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/panduan` |
| **Controller** | `DokumenController@panduan` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display guides list |

#### 2. Download Panduan
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/panduan/{id}/download` |
| **Controller** | `DokumenController@downloadPanduan` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Download guide document |

**URL Parameters:**
- `id`: Guide ID

#### 3. Instrumen (Instruments List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/instrumen` |
| **Controller** | `DokumenController@instrumen` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display instruments list |

#### 4. Download Instrumen
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/instrumen/{id}/download` |
| **Controller** | `DokumenController@downloadInstrumen` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Download instrument document |

**URL Parameters:**
- `id`: Instrument ID

#### 5. Template List
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/template` |
| **Controller** | `DokumenController@template` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display templates list |

#### 6. Download Template
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/template/{id}/download` |
| **Controller** | `DokumenController@downloadTemplate` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Download template document |

**URL Parameters:**
- `id`: Template ID

#### 7. Surat (Letters List)
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/surat` |
| **Controller** | `DokumenController@surat` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display letters list |

#### 8. Download Surat
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/dokumen/surat/{id}/download` |
| **Controller** | `DokumenController@downloadSurat` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Download letter document |

**URL Parameters:**
- `id`: Letter ID

---

### Panduan Penggunaan DAISY (DAISY User Guide)

#### 1. Panduan Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/panduan` |
| **Controller** | `PanduanController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display user guide index |

#### 2. Show Panduan Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/panduan/{slug}` |
| **Controller** | `PanduanController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed user guide |

**URL Parameters:**
- `slug`: Guide slug

---

### Bantuan Layanan (Help & Support)

#### 1. Bantuan Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/bantuan` |
| **Controller** | `BantuanController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display help & support page |

#### 2. Create Support Ticket
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/bantuan/tiket` |
| **Controller** | `BantuanController@createTicket` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Create new support ticket |

#### 3. Show Ticket Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/bantuan/tiket/{id}` |
| **Controller** | `BantuanController@showTicket` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed ticket view |

**URL Parameters:**
- `id`: Ticket ID

#### 4. Reply to Ticket
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/bantuan/tiket/{id}/reply` |
| **Controller** | `BantuanController@replyTicket` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Reply to support ticket |

**URL Parameters:**
- `id`: Ticket ID

---

### Profile & Settings

#### 1. Profile Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/profile` |
| **Controller** | `ProfileController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display user profile |

#### 2. Update Profile
| Attribute | Value |
|-----------|-------|
| **Method** | PUT |
| **Path** | `/profile` |
| **Controller** | `ProfileController@update` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update user profile information |

#### 3. Update Avatar
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/profile/avatar` |
| **Controller** | `ProfileController@updateAvatar` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update user avatar/photo |

#### 4. Show Password Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/profile/password` |
| **Controller** | `ProfileController@passwordForm` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display password change form |

#### 5. Update Password
| Attribute | Value |
|-----------|-------|
| **Method** | PUT |
| **Path** | `/profile/password` |
| **Controller** | `ProfileController@updatePassword` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update user password |

**Request Body:**
```json
{
  "current_password": "oldpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### 6. Settings Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/settings` |
| **Controller** | `SettingsController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display settings page |

#### 7. Update Settings
| Attribute | Value |
|-----------|-------|
| **Method** | PUT |
| **Path** | `/settings` |
| **Controller** | `SettingsController@update` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update user settings |

#### 8. Update Notification Settings
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/settings/notification` |
| **Controller** | `SettingsController@updateNotification` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update notification preferences |

---

### Activity & Tasks

#### 1. Activity List
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/aktivitas` |
| **Controller** | `ActivityController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display user activities list |

#### 2. Show Activity Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/aktivitas/{id}` |
| **Controller** | `ActivityController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed activity view |

**URL Parameters:**
- `id`: Activity ID

#### 3. Tasks List
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/tugas` |
| **Controller** | `TaskController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display user tasks list |

#### 4. Show Task Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/tugas/{id}` |
| **Controller** | `TaskController@show` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed task view |

**URL Parameters:**
- `id`: Task ID

#### 5. Complete Task
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/tugas/{id}/complete` |
| **Controller** | `TaskController@complete` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Mark task as completed |

**URL Parameters:**
- `id`: Task ID

---

### Laporan (Reports)

#### 1. Laporan Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/laporan` |
| **Controller** | `LaporanController@index` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display reports dashboard |

#### 2. Laporan Statistik
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/laporan/statistik` |
| **Controller** | `LaporanController@statistik` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display statistical reports |

#### 3. Laporan Kinerja
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/laporan/kinerja` |
| **Controller** | `LaporanController@kinerja` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display performance reports |

#### 4. Export Laporan
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/laporan/export` |
| **Controller** | `LaporanController@export` |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Export reports (Excel/PDF) |

**Response Type:** File download

---

### Kriteria, Elemen Standar, Indikator Resources

#### 1. Kriteria (Criteria) - CRUD
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/kriteria` | `KriteriaController@index` | List criteria |
| GET | `/kriteria/create` | `KriteriaController@create` | Create form |
| POST | `/kriteria` | `KriteriaController@store` | Store criteria |
| GET | `/kriteria/{id}` | `KriteriaController@show` | Show detail |
| GET | `/kriteria/{id}/edit` | `KriteriaController@edit` | Edit form |
| PUT/PATCH | `/kriteria/{id}` | `KriteriaController@update` | Update criteria |
| DELETE | `/kriteria/{id}` | `KriteriaController@destroy` | Delete criteria |

**Auth Required:** ✅ Yes (auth, verified)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

#### 2. Elemen Standar (Standard Elements) - CRUD
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/elemen-standar` | `ElemenStandarController@index` | List elements |
| GET | `/elemen-standar/create` | `ElemenStandarController@create` | Create form |
| POST | `/elemen-standar` | `ElemenStandarController@store` | Store element |
| GET | `/elemen-standar/{id}` | `ElemenStandarController@show` | Show detail |
| GET | `/elemen-standar/{id}/edit` | `ElemenStandarController@edit` | Edit form |
| PUT/PATCH | `/elemen-standar/{id}` | `ElemenStandarController@update` | Update element |
| DELETE | `/elemen-standar/{id}` | `ElemenStandarController@destroy` | Delete element |

**Auth Required:** ✅ Yes (auth, verified)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

#### 3. Jenis Indikator (Indicator Types) - CRUD
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/jenis-indikator` | `JenisIndikatorController@index` | List types |
| GET | `/jenis-indikator/create` | `JenisIndikatorController@create` | Create form |
| POST | `/jenis-indikator` | `JenisIndikatorController@store` | Store type |
| GET | `/jenis-indikator/{id}` | `JenisIndikatorController@show` | Show detail |
| GET | `/jenis-indikator/{id}/edit` | `JenisIndikatorController@edit` | Edit form |
| PUT/PATCH | `/jenis-indikator/{id}` | `JenisIndikatorController@update` | Update type |
| DELETE | `/jenis-indikator/{id}` | `JenisIndikatorController@destroy` | Delete type |

**Auth Required:** ✅ Yes (auth, verified)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

#### 4. Indikator (Indicators) - CRUD
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/indikator` | `IndikatorController@index` | List indicators |
| GET | `/indikator/create` | `IndikatorController@create` | Create form |
| POST | `/indikator` | `IndikatorController@store` | Store indicator |
| GET | `/indikator/{id}` | `IndikatorController@show` | Show detail |
| GET | `/indikator/{id}/edit` | `IndikatorController@edit` | Edit form |
| PUT/PATCH | `/indikator/{id}` | `IndikatorController@update` | Update indicator |
| DELETE | `/indikator/{id}` | `IndikatorController@destroy` | Delete indicator |

**Auth Required:** ✅ Yes (auth, verified)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

---

### Notifications

#### 1. Notifications Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/notifications` |
| **Controller** | Anonymous Function |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display notifications list |

#### 2. Mark Notification as Read
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/notifications/{id}/read` |
| **Controller** | Anonymous Function |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Mark notification as read |

**URL Parameters:**
- `id`: Notification ID

**Success Response:**
```json
{
  "success": true
}
```

#### 3. Mark All Notifications as Read
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/notifications/read-all` |
| **Controller** | Anonymous Function |
| **Auth Required** | ✅ Yes (auth, verified) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Mark all notifications as read |

**Success Response:**
```json
{
  "success": true
}
```

---

## 🛡️ Admin-Only Endpoints

These endpoints require both authentication and admin middleware.

### Required Headers
```http
Cookie: laravel_session=<session_value>
X-CSRF-TOKEN: <csrf_token>
```

### Required Role
User must have `role` = `admin` or be marked as administrator.

---

### User Management (Admin Only)

#### 1. List Users
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/users` |
| **Controller** | `UserController@index` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | List all users (DataTables AJAX) |

**Query Parameters:**
- Standard DataTables parameters for AJAX

**Response:** DataTables JSON format

#### 2. Create User Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/users/create` |
| **Controller** | `UserController@create` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display create user form |

#### 3. Store User
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/users` |
| **Controller** | `UserController@store` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Create new user |

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "admin",
  "role_selected": "super_admin",
  "roles": ["super_admin", "admin_univ"],
  "phone": "08123456789",
  "address": "Jakarta",
  "institution": "Universitas Indonesia",
  "id_university": 1,
  "id_study_program": 5,
  "position": "Dosen"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `email`: required, email, unique
- `password`: required, min:8, confirmed
- `role`: required, in:admin,user
- `role_selected`: required, in:super_admin,asesi,asesor,validator,verifikator,admin_univ,admin_prodi,default
- `roles`: array, each in allowed roles
- `id_university`: nullable, exists:universities,id
- `id_study_program`: nullable, exists:study_programs,id

#### 4. Show User Detail
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/users/{user}` |
| **Controller** | `UserController@show` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Show detailed user view |

**URL Parameters:**
- `user`: User ID

#### 5. Edit User Form
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/users/{user}/edit` |
| **Controller** | `UserController@edit` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display edit user form |

**URL Parameters:**
- `user`: User ID

#### 6. Update User
| Attribute | Value |
|-----------|-------|
| **Method** | PUT/PATCH |
| **Path** | `/users/{user}` |
| **Controller** | `UserController@update` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Update user information |

**URL Parameters:**
- `user`: User ID

#### 7. Delete User
| Attribute | Value |
|-----------|-------|
| **Method** | DELETE |
| **Path** | `/users/{user}` |
| **Controller** | `UserController@destroy` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Delete user |

**URL Parameters:**
- `user`: User ID

#### 8. Export Users
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/users/export` |
| **Controller** | `UserController@export` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Export users to Excel |

**Response Type:** Excel file download

#### 9. Download Users Import Template
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/users/template` |
| **Controller** | `UserController@downloadTemplate` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Download Excel template for bulk import |

**Response Type:** Excel file download

#### 10. Import Users
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/users/import` |
| **Controller** | `UserController@import` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Bulk import users from Excel |

**Request Body:** Multipart form data
- `file`: Excel file (.xlsx, .xls)

---

### Master Data (Admin Only)

#### 1. Master Data Index
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/master-data` |
| **Controller** | Redirect to `/master-data/universities` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Redirect to master data with default tab |

#### 2. Master Data with Tab
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/master-data/{tab?}` |
| **Controller** | `UniversityController@masterData` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Display master data page with tab selection |

**URL Parameters:**
- `tab` (optional): Tab name (universities, study-programs, etc.)

#### 3. Universities Resource (CRUD)
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/universities` | `UniversityController@index` | List universities |
| GET | `/universities/create` | `UniversityController@create` | Create form |
| POST | `/universities` | `UniversityController@store` | Store university |
| GET | `/universities/{id}` | `UniversityController@show` | Show detail |
| GET | `/universities/{id}/edit` | `UniversityController@edit` | Edit form |
| PUT/PATCH | `/universities/{id}` | `UniversityController@update` | Update university |
| DELETE | `/universities/{id}` | `UniversityController@destroy` | Delete university |

**Auth Required:** ✅ Yes (auth, verified, admin)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

#### 4. Study Programs Resource (CRUD)
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/study-programs` | `StudyProgramController@index` | List study programs |
| GET | `/study-programs/create` | `StudyProgramController@create` | Create form |
| POST | `/study-programs` | `StudyProgramController@store` | Store program |
| GET | `/study-programs/{id}` | `StudyProgramController@show` | Show detail |
| GET | `/study-programs/{id}/edit` | `StudyProgramController@edit` | Edit form |
| PUT/PATCH | `/study-programs/{id}` | `StudyProgramController@update` | Update program |
| DELETE | `/study-programs/{id}` | `StudyProgramController@destroy` | Delete program |

**Auth Required:** ✅ Yes (auth, verified, admin)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

#### 5. Indikator Penilaian Resource (CRUD)
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/indikator-penilaian` | `IndikatorPenilaianElemenController@index` | List assessment indicators |
| GET | `/indikator-penilaian/create` | `IndikatorPenilaianElemenController@create` | Create form |
| POST | `/indikator-penilaian` | `IndikatorPenilaianElemenController@store` | Store indicator |
| GET | `/indikator-penilaian/{id}` | `IndikatorPenilaianElemenController@show` | Show detail |
| GET | `/indikator-penilaian/{id}/edit` | `IndikatorPenilaianElemenController@edit` | Edit form |
| PUT/PATCH | `/indikator-penilaian/{id}` | `IndikatorPenilaianElemenController@update` | Update indicator |
| DELETE | `/indikator-penilaian/{id}` | `IndikatorPenilaianElemenController@destroy` | Delete indicator |

**Auth Required:** ✅ Yes (auth, verified, admin)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

---

### Bobot Penilaian (Scoring Weights) - Admin Only

#### 1. Bobot Penilaian Resource (CRUD)
| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/bobot-penilaian` | `BobotPenilaianController@index` | List scoring weights |
| GET | `/bobot-penilaian/create` | `BobotPenilaianController@create` | Create form |
| POST | `/bobot-penilaian` | `BobotPenilaianController@store` | Store weight |
| GET | `/bobot-penilaian/{id}` | `BobotPenilaianController@show` | Show detail |
| GET | `/bobot-penilaian/{id}/edit` | `BobotPenilaianController@edit` | Edit form |
| PUT/PATCH | `/bobot-penilaian/{id}` | `BobotPenilaianController@update` | Update weight |
| DELETE | `/bobot-penilaian/{id}` | `BobotPenilaianController@destroy` | Delete weight |

**Auth Required:** ✅ Yes (auth, verified, admin)  
**CSRF Required:** ✅ Yes (for POST/PUT/DELETE)

#### 2. Toggle Bobot Active Status
| Attribute | Value |
|-----------|-------|
| **Method** | POST |
| **Path** | `/bobot-penilaian/{id}/toggle` |
| **Controller** | `BobotPenilaianController@toggleActive` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ✅ Yes |
| **Purpose** | Toggle active/inactive status of scoring weight |

**URL Parameters:**
- `id`: Bobot ID

#### 3. Calculate Bobot
| Attribute | Value |
|-----------|-------|
| **Method** | GET |
| **Path** | `/bobot-penilaian/hitung/{asesmenId}/{categoryId}` |
| **Controller** | `BobotPenilaianController@calculate` |
| **Auth Required** | ✅ Yes (auth, verified, admin) |
| **CSRF Required** | ❌ No |
| **Purpose** | Calculate scoring weight for assessment |

**URL Parameters:**
- `asesmenId`: Assessment ID
- `categoryId`: Category ID

---

## 📊 Response Formats

### Standard HTML Response
Most GET endpoints return HTML views for browser rendering.

### Standard JSON Response (API Testing Endpoints)
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "timestamp": "2026-01-22T12:34:56+07:00"
}
```

### Error Response (422 Validation)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message here"
    ]
  }
}
```

### Error Response (401 Unauthorized)
```
Redirect to /login
```

### Error Response (403 Forbidden)
```html
403 | Forbidden
```

### Error Response (404 Not Found)
```html
404 | Not Found
```

### Error Response (500 Internal Server)
```html
500 | Server Error
(Check storage/logs/laravel.log for details)
```

---

## ⚠️ Error Handling

### Common HTTP Status Codes
- **200 OK:** Request successful
- **302 Found:** Redirect (after successful POST/login/logout)
- **401 Unauthorized:** Not authenticated (redirect to login)
- **403 Forbidden:** Authenticated but not authorized (admin required)
- **404 Not Found:** Resource not found
- **419 Page Expired:** CSRF token mismatch or expired
- **422 Unprocessable Entity:** Validation failed
- **500 Internal Server Error:** Server error (check logs)

### CSRF Token Mismatch (419)
**Solution:** Include fresh CSRF token in request headers
```javascript
// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Include in AJAX request
fetch('/endpoint', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': csrfToken,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(data)
});
```

### Session Expired (401)
**Solution:** User needs to login again
```
GET /login
```

---

## 🧪 Testing with Postman / k6

### Setup for Postman

1. **Login First:**
```http
POST http://127.0.0.1:8000/login
Content-Type: application/x-www-form-urlencoded

email=admin@example.com&password=password123
```

2. **Capture Session Cookie:**
Save the `laravel_session` cookie from response

3. **Get CSRF Token:**
```http
GET http://127.0.0.1:8000/dashboard
```
Extract CSRF token from HTML meta tag or cookie

4. **Use in Subsequent Requests:**
```http
POST http://127.0.0.1:8000/users
Cookie: laravel_session=<session_value>
X-CSRF-TOKEN: <csrf_token>
Content-Type: application/json

{
  "name": "Test User",
  "email": "test@example.com",
  ...
}
```

### Setup for k6 Load Testing

```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 10,
  duration: '30s',
};

export function setup() {
  // Login and get session
  const loginRes = http.post('http://127.0.0.1:8000/login', {
    email: 'admin@example.com',
    password: 'password123',
  });
  
  const cookies = loginRes.cookies;
  const sessionCookie = cookies['laravel_session'][0].value;
  
  return { sessionCookie };
}

export default function(data) {
  // Use testing endpoints (no auth required)
  const res = http.get('http://127.0.0.1:8000/api/test/pemetaan/stats');
  
  check(res, {
    'status is 200': (r) => r.status === 200,
    'response time < 500ms': (r) => r.timings.duration < 500,
    'success is true': (r) => JSON.parse(r.body).success === true,
  });
  
  sleep(1);
}
```

---

## 📝 Notes

1. **CSRF Protection:** All POST/PUT/DELETE requests require CSRF token except login/register
2. **Session Lifetime:** 120 minutes of inactivity
3. **Admin Middleware:** Blocks non-admin users from admin endpoints
4. **Testing Endpoints:** `/api/test/*` are for development/testing only - should be disabled in production
5. **File Uploads:** Use multipart/form-data content type
6. **DataTables:** Some endpoints return DataTables-specific JSON format for AJAX
7. **Pagination:** Most list endpoints support pagination (default: 20 per page)
8. **Soft Deletes:** Some resources may use soft deletes (check model implementation)

---

## 🔗 Quick Reference

**Base URL (Local):** `http://127.0.0.1:8000`  
**Health Check:** `GET /up`  
**Login:** `POST /login`  
**Logout:** `POST /logout`  
**Dashboard:** `GET /dashboard` (requires auth)

**Testing Endpoints (No Auth):**
- `GET /api/test/pemetaan/stats`
- `GET /api/test/pemetaan/timeline/{periode}`
- `GET /api/test/pemetaan/calendar`
- `GET /api/test/pemetaan/programs`

---

**Generated by:** GitHub Copilot  
**Last Updated:** January 22, 2026  
**Total Endpoints:** 150+
