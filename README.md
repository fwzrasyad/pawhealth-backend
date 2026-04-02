```markdown
# 🐾 PawHealth - Core API & Manager Portal

This repository contains the backend infrastructure for the PawHealth ecosystem. It serves two primary functions: a secure RESTful API that powers the Flutter mobile application, and a Vue.js web portal used by System Managers to oversee the platform and verify veterinarian licenses.

## ✨ Key Features
* **Firebase Token Middleware:** Custom API middleware that intercepts, validates, and decodes Firebase ID Tokens from the mobile app to secure all endpoints.
* **Structured Relational Database:** Highly optimized MySQL schema handling complex relationships between Users, Pets, Vets, Appointments, and Medical Records.
* **Vue 3 Manager Portal:** A built-in web dashboard (using Laravel Breeze) for administrators to view system statistics, manage users, and approve veterinary accounts.
* **REST API:** Clean, nested resource routing (e.g., `/api/pets/{petId}/daily-routines`).

## 🛠 Tech Stack
* **Framework:** Laravel 11 (PHP 8.2)
* **Database:** MySQL
* **Frontend Portal:** Vue.js 3 (Composition API), Tailwind CSS
* **Authentication SDK:** `kreait/laravel-firebase` (v6.x)

## 🚀 Local Development Setup

### Prerequisites
* PHP 8.2+ with the `ext-sodium` extension enabled in `php.ini`.
* [Composer](https://getcomposer.org/) installed.
* Node.js & NPM installed.
* MySQL Server running (e.g., via XAMPP Control Panel).
