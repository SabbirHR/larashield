📦 Larashield Installation & Setup Guide

Larashield provides authentication & API security features for your Laravel application.
Follow these steps to install and configure the package in a fresh or existing Laravel project.

1. Install Laravel Project (if not already)
laravel new myapp
cd myapp

2. Require Larashield Package

Run the following command to install Larashield (and its dependencies like Sanctum, Spatie, etc.):

composer require sabbir/larashield:@dev -W

3. Automatic Setup

After installation, Larashield auto-publishes configs and runs migrations.
If you need to manually set up or re-publish, run:

php artisan larashield:install


This will:

Publish Laravel Sanctum config

Publish Larashield config (config/larashield.php)

Run all required migrations

4. Database Setup

Make sure your .env file has a database connection configured. Example:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=

5. Authentication Routes

Larashield automatically loads its API routes from:

routes/api.php (inside the package)


Examples:

POST /api/register – register a new user

POST /api/login – login and receive token

POST /api/logout – logout the user

6. Testing with Postman

Use Postman to test login:

Request:

POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}


Postman Script (Tests Tab):

var data = pm.response.json();
if (data && data.data && data.data.token) {
    pm.environment.set("auth_token", data.data.token);
} else {
    console.log("Token not found in response");
}

7. Using the Token

For any protected routes, set the header in Postman:

Authorization: Bearer {{auth_token}}

✅ Done!

Your Laravel app is now protected with Larashield 🎉
