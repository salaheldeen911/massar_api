# Massar API — Official Response to Frontend Audit & Feedback

> 📌 **Comprehensive Frontend Integration Guide:** For the full list of API endpoints, exercise scoping, authorization rules, and media specs, refer to [FRONTEND_API_CHANGES_AND_ENDPOINTS.md](file:///c:/laragon/www/massar_api/docs/FRONTEND_API_CHANGES_AND_ENDPOINTS.md).

This document provides the official technical response and status breakdown for each point raised in the Frontend Audit Report.

---

## Summary Status Overview

| # | Item / Feature | Frontend Assessment | Backend Status / Action Taken |
|---|---|---|---|
| 1 | **Invalid Birth Date in Test Data** | Correct | ✅ **Fixed & Verified**: Validation `date_format:Y-m-d|before:today` added to all Patient Requests. Seed data verified (`1995-05-15`). |
| 2 | **Nullable Age Property** | Correct | ℹ️ **No Action Needed**: Backend `PatientResource` correctly returns `null` when `birth_date` is missing. Frontend null-checks are appropriate. |
| 3 | **Plaintext Password Display** | Incorrect / Security Risk | 🚫 **Rejected**: Passwords are one-way hashed (`bcrypt`). API will never return plaintext passwords for security reasons. |
| 4 | **Treatment / Nutrition Plan History** | Question / Observation | ℹ️ **By Design**: Creating new plan rows preserves patient clinical audit history. `getPatientDetails()` eager loads `latest('created_at')`. |
| 5 | **Global Exercise `title` is `null`** | Correct (Critical) | ✅ **Fixed & Verified**: Appended `withoutGlobalScope('center_scope')` to `PatientExercise->exercise()` relationship. |
| 6 | **Missing `destroy()` on Exercises API** | Correct | ✅ **Fixed & Verified**: Added `DELETE /api/business/exercises/{exercise}` endpoint with strict RBAC ownership policy. |
| 7 | **Exercise Lock / Unlock Feature** | Observation | ℹ️ **Out of Scope**: Exercise locking is handled via 3-tier scoping (`is_global`, `center_id`, `therapist_id`). |
| 8 | **Therapists Authorization Gap** | Correct (Critical) | ✅ **Fixed & Verified**: Restricted `therapists` CRUD to `role:admin` only in `routes/business.php` and Form Requests. Test added. |
| 9 | **Landlord Support Ticket Submission** | Client Route Mapping | ℹ️ **By Design**: Landlords manage center applications (`/landlord/...`), while center users submit support tickets (`/business/...`). |
| 10 | **Support Ticket 422 vs 404 Exception** | Correct | ✅ **Fixed & Verified**: Updated `SupportTicketService` to throw `abort(404)` on unauthorized center ticket access. |
| 11 | **Clearing Social Links in Center Details** | Correct | ✅ **Fixed & Verified**: Refactored `CenterDetailsService::updateCenterDetails` to support setting nullable fields to `null`. |
| 12 | **Center Status Enum Inconsistency** | Partially Incorrect | ℹ️ **Clarified**: `centers.status` column is `VARCHAR` (not MySQL `ENUM`). `CenterStatus::class` supports `suspended` and `inactive`. |

---

## Detailed Technical Responses

### 1. Patient Birth Date Validation & Seed Data
* **Status**: ✅ **Implemented & Verified**
* **Technical Details**:
  * Validation rule `'birth_date' => ['required', 'date_format:Y-m-d', 'before:today']` has been applied to `StorePatientRequest` and `UpdatePatientRequest` across both `Business` and `Landlord` request classes.
  * Running `php artisan migrate:fresh --seed` populates valid test birth dates (e.g. `1995-05-15`).

### 2. Patient Password Display in Profile
* **Status**: 🚫 **Rejected by Design (Security Standard)**
* **Technical Details**:
  * In compliance with security standards, passwords are stored using one-way cryptographic hashing (`bcrypt`) and are **never** exposed via API resources.
  * Password resets should be performed using the optional `password` field in `UpdatePatientRequest`.

### 3. Plan History Storage (`storeTreatmentPlan` & `storeNutritionPlan`)
* **Status**: ℹ️ **Intentional Medical Audit Trail**
* **Technical Details**:
  * Physical therapy regulations require maintaining an immutable history of clinical treatment and nutrition plans.
  * The API automatically returns the most recent active plan via `latest('created_at')` in `PatientDetailResource`.

### 4. Assigned Global Exercise `title` Returning `null`
* **Status**: ✅ **Implemented & Verified**
* **Technical Details**:
  * Appended `->withoutGlobalScope('center_scope')` to `public function exercise(): BelongsTo` in `PatientExercise.php`.
  * Assigned global system exercises (`center_id = null`) now return their full title, notes, and media URLs properly.

### 5. Missing Exercise `destroy()` Endpoint & Access Policy
* **Status**: ✅ **Implemented & Verified**
* **Technical Details**:
  * Implemented `destroy()` method in `ExerciseController` and `deleteExercise()` method in `ExerciseService`.
  * **Ownership Deletion Policy Enforced**:
    1. **Global System Exercises (`center_id = null`)**: Can only be deleted by System Administrators (`landlord`). Center Admin / Therapist calls return `403 Forbidden`.
    2. **Center Public Exercises (`center_id = center_id`, `therapist_id = null`)**: Can only be deleted by Center Admins (`role:admin`).
    3. **Therapist Private Exercises (`therapist_id = user_id`)**: Can be deleted by the creating Therapist or Center Admin.
  * Added `Route::bind('exercise', ...)` to bind global exercises without global scope filter for proper RBAC error checking.

### 6. Therapists Authorization Gap
* **Status**: ✅ **Fixed & Verified**
* **Technical Details**:
  * Moved `Route::apiResource('therapists')` inside `Route::middleware('role:admin')` in `routes/business.php`.
  * Updated `authorize()` in `GetTherapistsRequest`, `StoreTherapistRequest`, and `UpdateTherapistRequest` to `$this->user()?->hasRole('admin')`.
  * Verified with automated feature test `test_therapist_cannot_create_or_modify_other_therapists`.

### 7. Support Ticket Ownership 422 vs 404 Exception
* **Status**: ✅ **Fixed & Verified**
* **Technical Details**:
  * Replaced `ValidationException` in `SupportTicketService::ensureTicketBelongsToCenter` with `abort(404, 'Support ticket not found or does not belong to your center.')`.

### 8. Clearing Social Links in Center Details
* **Status**: ✅ **Implemented & Verified**
* **Technical Details**:
  * Refactored `CenterDetailsService::updateCenterDetails` to build `$updateData` via `array_key_exists` without stripping `null` values.
  * Admins can now send `{"facebook": null}` to reset social link fields in the database. Added feature test `test_admin_can_clear_social_link_fields`.
