# Laravel Auth System with OTP

## Features
- User Registration
- OTP Login (Email & Mobile)
- JWT Authentication
- Change Password
- Verify Password

## Tech Stack
- Laravel 11
- MySQL
- JWT (firebase/php-jwt)

## Setup Instructions
1. Clone repo
2. Run: composer install
3. Copy: .env.example to .env
4. Run: php artisan key:generate
5. Setup DB in .env
6. Run: php artisan migrate
7. Start server: php artisan serve

## API Endpoints
- POST /api/login/register
- POST /api/login/generateOTP
- POST /api/login/verifyOTP
- POST /api/password/change-password
- POST /api/password/verify-password