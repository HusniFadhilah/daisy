# Refactoring Bobot Penilaian: From Degree Levels to Categories

## Executive Summary

Successfully refactored the bobot penilaian (weight assessment) system from using 11 degree levels to 6 accreditation categories, aligning with LAMEMBA standards.

## Problem Identified

The original system used `degree_levels` (11 levels: S1, S2, S3, D2, D3, D4, ST, MT, DT, PR, SP1, SP2) for bobot penilaian, but LAMEMBA accreditation requires only 6 category groupings:
- **Akademik** (S1)
- **Magister** (S2)
- **Doktor** (S3)
- **Vokasi** (D2, D3, D4, ST)
- **Magister Terapan** (MT, DT)
- **Profesi** (PR, SP1, SP2)

## Solution Implemented

### 1. Database Structure Changes

#### New Table: `study_program_categories`
```sql
- id
- code (AK, MAG, DOK, VOK, MT, PRO)
- name
- description
- timestamps
```

#### Updated Table: `study_programs`
Added `category_id` foreign key to link programs to their accreditation category.

#### Refactored Table: `bobot_penilaian`
Changed from:
- `id_level` → foreign key to degree_levels

To:
- `id_category` → foreign key to study_program_categories

### 2. Code Changes Summary

#### Models Updated
- **StudyProgramCategory** (NEW): Manages 6 accreditation categories
- **StudyProgram**: Added `category()` relationship
- **BobotPenilaian**: Changed `degreeLevel()` to `category()` relationship

#### Controllers Updated
- **BobotPenilaianController**: 
  - Changed imports from `DegreeLevel` to `StudyProgramCategory`
  - Updated `index()` to pass `$categories` instead of `$levels`
  - Updated `calculate()` to use `$categoryId` instead of `$levelId`
  - Updated filters from `id_level` to `id_category`

#### Services Updated
- **BobotPenilaianService**:
  - `getBobotForLevel()` → `getBobotForCategory()`
  - `calculateByKriteria()` → uses `$categoryId` parameter
  - `calculateTotalScore()` → uses `$categoryId` parameter
  - `getAll()` → filters by `id_category`, eager loads `category` relationship

#### Resources Updated
- **BobotPenilaianResource**: Changed `degree_level` to `category` in JSON output

#### Requests Updated
- **BobotPenilaianRequest**:
  - Validation rules: `id_level` → `id_category`
  - Unique constraint: `(id_elemen, id_category)` 
  - Error messages: "jenjang" → "kategori"

#### Views Updated
- **bobot-penilaian/index.blade.php**:
  - Filter dropdown: "Jenjang" → "Kategori"
  - Table headers: "Jenjang Pendidikan" → "Kategori"
  - Modal form: `id_level`/`inputLevel` → `id_category`/`inputCategory`
  - JavaScript: `editBobot()` uses `categoryId` parameter
  - Calculation form: `level_id` → `category_id`
  - AJAX calls updated to use category endpoints

#### Routes Updated
- `/bobot-penilaian/hitung/{asesmenId}/{levelId}` → `/{asesmenId}/{categoryId}`

### 3. Migration Files Created

1. **2025_12_14_144903_create_study_program_categories_table.php**
   - Creates categories lookup table with 6 entries

2. **2025_12_14_144917_add_category_id_to_study_programs_table.php**
   - Links study programs to categories (nullable FK)

3. **2025_12_14_145112_update_bobot_penilaian_use_category.php**
   - Drops old `bobot_penilaian` table
   - Recreates with `id_category` instead of `id_level`

### 4. Seeders Created

1. **StudyProgramCategorySeeder**
   - Seeds 6 categories: AK, MAG, DOK, VOK, MT, PRO
   - Uses `updateOrCreate` for idempotency

2. **BobotPenilaianSeederNew**
   - Seeds 180 records (30 elements × 6 categories)
   - Default bobot value: 1
   - Organized by kriteria grouping

## Data Results

✅ **6 Categories** successfully seeded:
- AK (Akademik)
- MAG (Magister)  
- DOK (Doktor)
- VOK (Vokasi)
- MT (Magister Terapan)
- PRO (Profesi)

✅ **180 Bobot Records** successfully seeded:
- 30 elements × 6 categories
- All with default bobot = 1
- Proper foreign key constraints

## Architecture Benefits

### Separation of Concerns
- **degree_levels**: Administrative/academic classification (11 levels)
- **study_program_categories**: Accreditation grouping (6 categories)

### Scalability
- Adding new degree levels doesn't affect accreditation weights
- Categories can be reused across multiple related degree levels

### Data Integrity
- Unique constraint: `(id_elemen, id_category)` prevents duplicates
- Cascade deletes maintain referential integrity
- Nullable `category_id` in study_programs allows gradual data migration

## Testing Checklist

- [x] Migrations run successfully
- [x] Categories seeded (6 records)
- [x] Bobot seeded (180 records)
- [x] Foreign key constraints working
- [x] Unique constraints enforced
- [ ] View renders correctly in browser
- [ ] Filter by category works
- [ ] Add bobot form works
- [ ] Edit bobot form works
- [ ] Delete bobot works
- [ ] Calculate weighted scores works

## Next Steps for User

1. **Access the application**: http://127.0.0.1:8000
2. **Login** as admin: admin@daisy.ac.id / password
3. **Navigate to Bobot Penilaian** menu
4. **Test the following**:
   - Filter by kategori dropdown shows 6 options
   - Table displays category names correctly
   - Add new bobot with kategori selection
   - Edit existing bobot
   - Delete bobot
   - Calculate weighted scores by kategori

## Files Modified (Summary)

**Created** (7 files):
- 3 migration files
- 1 model (StudyProgramCategory)
- 2 seeders
- 1 verification script (check_data.php)

**Modified** (9 files):
- 2 models (StudyProgram, BobotPenilaian)
- 1 controller (BobotPenilaianController)
- 1 service (BobotPenilaianService)
- 1 resource (BobotPenilaianResource)
- 1 request (BobotPenilaianRequest)
- 1 view (bobot-penilaian/index.blade.php)
- 1 route file (web.php)
- 1 config file (if any)

## Rollback Instructions

If needed, rollback using:
```bash
php artisan migrate:rollback --step=3
```

This will:
1. Drop `bobot_penilaian` and recreate with `id_level`
2. Remove `category_id` from `study_programs`
3. Drop `study_program_categories` table

---

**Completion Date**: December 14, 2025  
**Status**: ✅ COMPLETED - Ready for Testing
