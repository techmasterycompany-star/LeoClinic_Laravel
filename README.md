# LeoClinic — Clinic Appointment Management System

A Laravel REST API for managing doctor/patient appointment booking, built with role-based access control (admin, doctor, patient), Laravel Sanctum authentication, and a full appointment lifecycle: registration & approval, availability scheduling, booking, confirmation, completion, ratings, and payments.

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)
- [Testing](#testing)

## Overview

LeoClinic exposes a JSON API for three kinds of users:

- **Admins** — approve/reject doctors and patients, manage specialties and locations, and manage user accounts (block/unblock).
- **Doctors** — complete their profile, manage assigned locations and availability slots, and act on appointments (confirm/reject/complete).
- **Patients** — browse approved doctors, book appointments against open availability slots, rate completed appointments, and pay for them.

Authentication is token-based via [Laravel Sanctum](https://laravel.com/docs/sanctum) — no sessions or cookies are used for the API itself.

## Tech Stack

- **Framework:** Laravel 10 (PHP 8.1+)
- **Auth:** Laravel Sanctum (API tokens)
- **Database:** MySQL (or any Laravel-supported driver — SQLite works for local/testing)
- **Mail:** Laravel Mail (used for email verification and password reset codes)
- **Testing:** PHPUnit / Laravel's built-in testing tools

## Features

- Email/password registration with role selection (`doctor` or `patient`), email verification via a 6-digit code, and password reset via emailed code.
- Admin approval workflow for both doctors and patients before they can fully use the platform.
- Doctor profile completion with file uploads (professional license required, profile image optional).
- Doctor-managed locations and availability slots (date + time range per location).
- Patient-facing doctor search/browse (filter by specialty, city, name) with ratings and open availability shown.
- Full appointment lifecycle: book → confirm/reject (doctor) → complete (doctor) → rate (patient) → pay (patient), plus cancellation available to either party.
- In-app notifications on key events (e.g., new booking).
- Admin user management: search/filter users, block/unblock, update account status.

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/techmasterycompany-star/LeoClinic_Laravel.git
cd LeoClinic_Laravel

# 2. Install PHP dependencies
composer install

# 3. Copy the environment file and generate an app key
cp .env.example .env
php artisan key:generate

# 4. Configure your database and mail settings in .env (see below)

# 5. Run migrations
php artisan migrate

# 6. Link the public storage disk (needed for uploaded license/profile files)
php artisan storage:link

# 7. Create an admin account (no public registration endpoint exists for admins)
php artisan tinker
>>> \App\Models\User::create([
...     'name' => 'Admin',
...     'email' => 'admin@example.com',
...     'password' => bcrypt('Password123!'),
...     'role' => 'admin',
...     'email_verified_at' => now(),
... ]);
>>> exit

# 8. Serve the application
php artisan serve
```

The API is now available at `http://127.0.0.1:8000/api`.

## Environment Configuration

Key `.env` values to set:

```env
APP_URL=http://127.0.0.1:8000
APP_DEBUG=false        # keep false outside local development

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leoclinic
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp       # use "log" locally to read codes from storage/logs/laravel.log instead of sending real email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
```

> If using Gmail for `MAIL_MAILER=smtp`, `MAIL_PASSWORD` must be a Google **App Password** (16 characters, requires 2-Step Verification enabled on the Google account) — your normal Gmail password will not work.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php              # register, login, logout, email verify, password reset
│   │   │   ├── DoctorController.php            # public/patient-facing doctor browse & details
│   │   │   ├── AppointmentController.php       # book, list, confirm, reject, complete, cancel
│   │   │   ├── RatingController.php            # rate appointment, doctor average rating
│   │   │   ├── PaymentController.php           # record & list payments
│   │   │   ├── NotificationController.php      # list & mark-as-read
│   │   │   ├── PatientProfileController.php    # patient profile show/update
│   │   │   ├── Admin/
│   │   │   │   ├── SpecialtyController.php
│   │   │   │   ├── LocationController.php
│   │   │   │   ├── DoctorApprovalController.php
│   │   │   │   ├── PatientApprovalController.php
│   │   │   │   ├── UserBlockController.php
│   │   │   │   └── AdminUserController.php
│   │   │   └── Doctor/
│   │   │       ├── DoctorProfileController.php
│   │   │       ├── DoctorLocationController.php
│   │   │       ├── AvailabilityController.php
│   │   │       └── ScheduleController.php
│   │   └── Controller.php
│   ├── Middleware/
│   │   ├── Authenticate.php                    # extends base Authenticate (JSON-aware redirect)
│   │   └── RoleMiddleware.php                  # role:admin / role:doctor / role:patient route guard
│   └── Requests/                               # form request validation classes (Auth, Admin, Doctor, Patient)
├── Models/
│   ├── User.php
│   ├── DoctorProfile.php
│   ├── PatientProfile.php
│   ├── Specialty.php
│   ├── Location.php
│   ├── DoctorLocation.php
│   ├── Availability.php
│   ├── Appointment.php
│   ├── Rating.php
│   ├── Payment.php
│   ├── UserNotification.php
│   └── VerificationCode.php
└── Exceptions/
    └── Handler.php                             # centralized JSON error responses (401/403/404/422/500)

routes/
└── api.php                                     # all API route definitions, grouped by auth/role

tests/
└── Feature/                                    # end-to-end API tests

database/
└── migrations/
```

## API Documentation

Full request/response examples for every endpoint are maintained as a Postman collection (`Appointment-Booking-API.postman_collection.json`), organized into the same folders as below, with example request bodies validated against each endpoint's actual `FormRequest` rules.

All protected routes require an `Authorization: Bearer <token>` header, obtained from **Login** or **Register**.

### Auth (`/api/auth`)

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/auth/register` | — | Register as `doctor` or `patient` (doctor requires `specialty_id`) |
| POST | `/auth/login` | — | Log in, returns a Bearer token |
| POST | `/auth/logout` | ✅ | Revoke all tokens for the current user |
| POST | `/auth/verify-email` | — | Verify email with the 6-digit code |
| POST | `/auth/forgot-password` | — | Email a password reset code |
| POST | `/auth/verify-reset-code` | — | Verify a reset code without consuming it |
| POST | `/auth/reset-password` | — | Reset password using the emailed code |
| GET | `/user` | ✅ | Return the current authenticated user |

### Admin (`/api/admin`) — requires `role:admin`

| Method | Endpoint | Description |
|---|---|---|
| GET/POST | `/admin/specialties` | List / create specialties |
| PUT/DELETE | `/admin/specialties/{specialty}` | Update / delete a specialty |
| GET/POST | `/admin/locations` | List / create locations |
| PUT/DELETE | `/admin/locations/{location}` | Update / delete a location |
| GET | `/admin/doctors/pending` | List unapproved doctors |
| PATCH | `/admin/doctors/{doctorProfile}/approve` | Approve a doctor |
| PATCH | `/admin/doctors/{doctorProfile}/reject` | Reject (deletes) a doctor account |
| GET | `/admin/patients/pending` | List unapproved patients |
| PATCH | `/admin/patients/{patientProfile}/approve` | Approve a patient |
| PATCH | `/admin/patients/{patientProfile}/reject` | Reject (deletes) a patient account |
| GET | `/admin/users` | List/search all users (filters: `search`, `role`, `is_blocked`) |
| PATCH | `/admin/users/{user}/block` | Block a user |
| PATCH | `/admin/users/{user}/unblock` | Unblock a user |
| PATCH | `/admin/users/{user}/status` | Set `is_blocked` explicitly |

### Doctor (`/api/doctor`) — requires `role:doctor`

| Method | Endpoint | Description |
|---|---|---|
| GET | `/doctor/profile` | Get own profile |
| POST | `/doctor/profile` | Complete profile (multipart — license required, image optional) |
| PUT | `/doctor/profile` | Update profile fields |
| GET/POST | `/doctor/locations` | List / assign practice locations |
| DELETE | `/doctor/locations/{doctorLocation}` | Remove an assigned location |
| GET/POST | `/doctor/availabilities` | List / add availability slots |
| PUT/DELETE | `/doctor/availabilities/{availability}` | Update / delete a slot (only if not booked) |
| GET | `/doctor/schedule` | Combined view of appointments + booked/open slots |

### Doctor Appointment Actions (`/api/doctor`) — requires auth only (no role restriction in code)

| Method | Endpoint | Description |
|---|---|---|
| PUT | `/doctor/appointments/{id}/confirm` | Confirm a pending appointment |
| PUT | `/doctor/appointments/{id}/reject` | Reject a pending appointment |
| PUT | `/doctor/appointments/{id}/complete` | Mark a confirmed appointment completed |
| GET | `/doctor/{doctorId}/average-rating` | Get a doctor's average rating |

### Patient (`/api/patient`) — requires auth

| Method | Endpoint | Description |
|---|---|---|
| GET/PUT | `/patient/profile` | Get / update own profile |
| GET | `/patient/doctors` | Browse approved doctors (filters: `specialty_id`, `city`, `name`) |
| GET | `/patient/doctors/{id}` | Doctor details, open availability, ratings |
| GET | `/patient/doctors/{id}/reviews` | Paginated reviews for a doctor |
| POST | `/patient/appointments` | Book an appointment against an availability slot |
| GET | `/patient/appointments` | List own appointments (filter: `status`) |
| POST | `/patient/appointments/{id}/rating` | Rate a completed appointment |

### Appointments / Notifications / Payments

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| PUT | `/appointments/{id}/cancel` | ✅ | Cancel (owning patient or doctor only) |
| GET | `/notifications` | ✅ | List own notifications |
| PUT | `/notifications/{id}/read` | ✅ | Mark a notification read |
| POST | `/payments` | `role:patient` | Record a payment for a completed appointment |
| GET | `/payments` | `role:patient` | List own payments (filters: `payment_method`, `from_date`, `to_date`) |

### Standard Response Shape

All endpoints return JSON in the following shape:

```json
{
  "success": true,
  "message": "Optional human-readable message",
  "data": { }
}
```

Validation errors return `422` with a `errors` object (Laravel's default validation error format). Authorization failures return `401` (not authenticated) or `403` (authenticated but wrong role / not the resource owner). Unexpected errors are caught centrally in `app/Exceptions/Handler.php` and always return JSON, never an HTML debug page.

## Testing

An end-to-end feature test (`tests/Feature/FullApiFlowTest.php`) walks through the entire lifecycle — admin setup, specialty/location creation, doctor onboarding and approval, patient registration, booking, confirmation, completion, rating, and payment — against an isolated, auto-refreshed test database.

```bash
php artisan test
```