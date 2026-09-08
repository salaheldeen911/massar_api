# Massar API - Architecture & Development Guidelines

This repository contains **Massar API**, a production-grade, pure API Laravel application. All engineers and AI agents working on this codebase MUST strictly follow these guidelines.

---

## 1. Application Overview & Core Requirements

- **API-Only Architecture**: Massar API is strictly a REST API. Do NOT introduce Blade views, CSS, JS, Vite, or frontend assets. All responses must be JSON.
- **Database Engine**: MySQL (`DB_DATABASE=massar`).
- **Core Packages**:
  - `laravel/sanctum`: API token authentication.
  - `spatie/laravel-permission`: Role-based access control (RBAC).
  - `spatie/laravel-medialibrary`: File and media management. All files, images, videos, avatars, logos, and documents MUST be managed via Spatie MediaLibrary (`HasMedia` & `InteractsWithMedia`). Never add plain string URL columns for media in database migrations.
  - `propaganistas/laravel-phone`: Phone number validation and formatting.
  - `spatie/laravel-multitenancy`: Single-database multi-tenancy support.
  - `laravel/reverb`: Real-time WebSockets.

---

## 2. Multi-Tenancy Architecture (Center Scoping)

- **Tenant Definition**: Tenants are referred to as **Center** (`Center` model, `center_id` foreign key).
- **Tenant Context Resolution**:
  - The active Center is resolved dynamically from the authenticated user (`currentUser()->center_id`).
  - Users with the `landlord` role are global administrators and are not bound to a Center (`center_id = null`).
- **Data Isolation Trait**:
  - Use `App\Traits\BelongsToCenter` on any Eloquent model that belongs to a Center.
  - The trait automatically applies a global query scope (`center_scope`) filtering records by `currentCenterId()`.
  - The trait automatically populates `center_id` on record creation.
  - `isLandlord()` users automatically bypass the center scope filter.

---

## 3. Global Helper Functions (`app/helpers.php`)

All global helper functions reside in `app/helpers.php` (auto-loaded via `composer.json`) and use **camelCase** naming wrapped with `if (! function_exists('...'))`:

- `currentUser()`: Returns the currently authenticated `User` model instance or `null`.
- `currentCenterId()`: Returns the integer `center_id` of the active user or `null`.
- `currentCenter()`: Returns the `Center` model instance of the active user or `null`.
- `isLandlord()`: Returns `true` if the authenticated user has the `landlord` role.

---

## 4. Roles & Route Structure

The application supports four primary roles:
1. **`landlord`**: System-wide global administration.
2. **`admin`**: Center-level administrator.
3. **`therapist`**: Center-level specialist/therapist.
4. **`patient`**: Center-level patient/client.

Each role has a dedicated route file and URL prefix:
- `routes/landlord.php` -> Prefix: `/api/landlord` -> Name prefix: `landlord.`
- `routes/admin.php` -> Prefix: `/api/admin` -> Name prefix: `admin.`
- `routes/therapist.php` -> Prefix: `/api/therapist` -> Name prefix: `therapist.`
- `routes/patient.php` -> Prefix: `/api/patient` -> Name prefix: `patient.`
- `routes/api.php` -> Shared / Public API endpoints (`/api`)

---

## 5. Directory Structure Guidelines

Form Requests, Controllers, and API Resources MUST be organized strictly into role subfolders:

```
app/
├── Helpers/
│   └── helpers.php
├── Http/
│   ├── Controllers/
│   │   ├── Landlord/
│   │   ├── Admin/
│   │   ├── Therapist/
│   │   └── Patient/
│   ├── Requests/
│   │   ├── Landlord/
│   │   ├── Admin/
│   │   ├── Therapist/
│   │   └── Patient/
│   └── Resources/
│       ├── Landlord/
│       ├── Admin/
│       ├── Therapist/
│       └── Patient/
├── Models/
│   ├── Center.php
│   └── User.php
├── Observers/
├── Services/
└── Traits/
    └── BelongsToCenter.php
```

---

## 6. Engineering & SOLID Principles

- **Thin Controllers**: Controllers orchestrate request lifecycle only. Controllers must extract validated data from Form Requests, delegate execution to a Service class, and return an API Resource response.
- **Service Layer**: Business workflows MUST live inside `app/Services/`. Service methods performing multiple steps must be broken down into small, focused `private` helper methods within the same Service class.
- **Form Request Validation**: EVERY API request requiring validation MUST have a dedicated Form Request class under the corresponding role folder. Use `$request->validated()` in controllers.
- **API Resources**: EVERY JSON response MUST be transformed via Laravel API Resources (`app/Http/Resources/...`).
- **Eloquent Observers**: Model lifecycle logic (creation, update, deletion events) MUST be placed in Observers (`app/Observers/`), never inside models or controllers.
- **Database & Performance**:
  - Prevent N+1 queries using eager loading (`with(...)`).
  - Wrap multi-step database operations in `DB::transaction(...)`.
- **Testing Standard**: Every feature must include automated feature or unit tests (`php artisan test`).

---

## 7. Standardized API Responses & Exception Handling

- **Base Controller Response Methods**: All Controllers MUST extend `App\Http\Controllers\Controller` and use the base response methods:
  - `$this->success($data, $message, $code, $meta)`: Returns a unified success JSON structure (`success: true`, `message`, `data`, `meta`). Automatically handles normal data, API resources, and paginated datasets.
  - `$this->failed($message, $code, $errors)`: Returns a unified error JSON structure (`success: false`, `message`, `errors`).
- **Global Exception Interception**: Do NOT write ad-hoc `try-catch` blocks in controllers. All exceptions (`ValidationException`, `AuthenticationException`, `AccessDeniedHttpException`, `ModelNotFoundException`, `Throwable`) are intercepted globally in `bootstrap/app.php` and formatted into the unified `failed(...)` JSON structure.
- **Security & Logging**: Unhandled server exceptions must log full trace details to the `daily` log channel without exposing internal backend trace details to the client response.

---

## 8. Git & Version Control Policy

- **No Automatic Git Operations**: NEVER execute `git add`, `git commit`, or `git push` automatically without explicit user instruction. All git staging, committing, and pushing must only be performed when explicitly requested by the user.

---

## 9. Postman Collection Documentation Policy

- **Mandatory Endpoint Documentation**: EVERY API endpoint added or updated in the project MUST be documented in `postman/Massar_API.postman_collection.json`.
- **Endpoint Descriptions & Response Examples**: Each endpoint request in the collection MUST contain:
  - Clear and descriptive `description` explaining the request's purpose, route access control, and parameters.
  - Expected JSON response examples (`response` array in Postman collection) illustrating both success responses (`200`/`201`) and common error responses (`422 Validation Error`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`).
- **Unified Schema Conformance**: All response examples in Postman MUST conform strictly to the application's unified JSON structure (`success`, `message`, `data`, `meta`, `errors`).

