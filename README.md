# 📦 Larashield Installation & Setup Guide

Larashield provides **authentication & API security features** for your Laravel application.  
Follow these steps to install and configure the package in a **fresh or existing Laravel project**.

---

## 1️⃣ Install Laravel Project (if not already)

```bash
laravel new myapp
cd myapp

## 2️⃣ Require Larashield Package

Run the following command to install Larashield and its dependencies:

```bash
composer require sabbir/larashield:@dev -W
## 3️⃣ Automatic Setup

After installation, Larashield auto-publishes configs and runs migrations.
If you need to manually set up or re-publish, run:
```bash
php artisan larashield:install

## 4️⃣ Database Setup

Make sure your .env file has a database connection configured. Example:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=

## 5️⃣ Authentication Routes

Larashield automatically loads its API routes from the package:

```bash
| Method | URI           | Description                      |
| ------ | ------------- | -------------------------------- |
| POST   | /api/register | Register a new user              |
| POST   | /api/login    | Login and receive token          |
| POST   | /api/logout   | Logout the user (requires token) |

## 6️⃣ Testing with Postman

Login Request:

```bash
POST /api/login
Content-Type: application/json
```bash
{
    "email": "user@example.com",
    "password": "password"
}
```bash
var data = pm.response.json();
if (data && data.data && data.data.token) {
    pm.environment.set("auth_token", data.data.token);
} else {
    console.log("Token not found in response");
}


## 7️⃣ Using the Token

For any protected routes, set the header in Postman:

```bash
Authorization: Bearer {{auth_token}}






