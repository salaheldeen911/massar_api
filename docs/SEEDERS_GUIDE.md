# Massar API - Seeders Documentation & Guide

This document provides a comprehensive reference for all database seeders available in the **Massar API** backend.

---

## 1. Overview of Available Seeders

The application includes five primary seeders configured in [`DatabaseSeeder.php`](file:///C:/laragon/www/massar_api/database/seeders/DatabaseSeeder.php):

| Seeder Class | Purpose | Idempotent |
|---|---|---|
| **`RoleSeeder`** | Seeds Spatie RBAC roles (`landlord`, `admin`, `therapist`, `patient`). | Yes |
| **`LandlordSeeder`** | Seeds the initial landlord system administrator. | Yes |
| **`DiagnosisSeeder`** | Seeds 260+ standard medical physical therapy diagnoses (A-Z). | Yes |
| **`ExerciseSeeder`** | Seeds 30 baseline exercise videos into system library (`is_system = true`). | Yes |
| **`DevSeeder`** | Populates ready-to-use development accounts for all 4 user roles. | Yes |

---

## 2. Development Test Accounts (`DevSeeder`)

Running `DevSeeder` generates ready-to-use accounts for immediate API testing across all 4 user roles.

> [!IMPORTANT]
> **Production Environment Safety**: `DevSeeder` is strictly restricted from running in production environments (`APP_ENV=production`). If executed when `app()->environment('production')` is active, `DevSeeder` automatically skips execution to ensure test accounts are never created in production environments.

### 🔑 Test Credentials Table

| Role | Email | Password | Center Name | Associated Records |
|---|---|---|---|---|
| **Landlord Admin** | `landlord@massar.com` | `password123` | *N/A (Global)* | Global system admin |
| **Center Admin** | `admin@massar.com` | `password123` | Massar Physical Therapy Center | Center Admin User |
| **Therapist** | `therapist@massar.com` | `password123` | Massar Physical Therapy Center | `TherapistProfile` |
| **Patient** | `patient@massar.com` | `password123` | Massar Physical Therapy Center | `PatientProfile` (assigned to Dr. Ahmed) |

---

## 3. Baseline Exercise Library (`ExerciseSeeder`)

The `ExerciseSeeder` reads exercise video assets stored in `database/seeders/data/exercises/` and inserts 30 baseline system exercises.

### Key Characteristics:
- **System-Wide Availability**: Created with `center_id = null` and `therapist_id = null`, making them visible to all centers and therapists.
- **Deletion Protection**: Marked as `is_system = true` and protected from deletion by [`ExerciseObserver.php`](file:///C:/laragon/www/massar_api/app/Observers/ExerciseObserver.php).
- **Supported Media Mimetypes**: Accepts videos (`.mp4`, `.mov`, `.avi`, `.webm`, `.mkv`, `.m4v`), animated GIFs (`.gif`), and images (`.jpeg`, `.jpg`, `.png`, `.webp`).
- **Media Preservation**: Uses `$exercise->addMedia(...)->preservingOriginal()` to maintain source seed assets across repeated seeder runs.

---

## 4. How to Run Seeders

### Run All Seeders (Fresh Database Setup)
```bash
php artisan migrate:fresh --seed
```

### Run All Seeders (Without Wiping DB)
```bash
php artisan db:seed
```

### Run a Specific Seeder Class
```bash
# Seed development accounts only
php artisan db:seed --class=DevSeeder

# Seed baseline exercises only
php artisan db:seed --class=ExerciseSeeder
```
