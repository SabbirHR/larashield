#!/bin/bash

PACKAGE_NAME="larashield"
ROOT="src"

echo "Creating $PACKAGE_NAME package structure under $ROOT..."

# ------------------------------
# Directories
# ------------------------------
DIRS=(
  "$ROOT/Config"
  "$ROOT/Models"
  "$ROOT/Facades"
  "$ROOT/Http/Controllers"
  "$ROOT/Http/Requests"
  "$ROOT/Policies"
  "$ROOT/Providers"
  "$ROOT/Traits"
  "$ROOT/routes"
  "$ROOT/database/migrations"
)

for dir in "${DIRS[@]}"; do
  mkdir -p "$dir"
  echo "Created directory: $dir"
done

# ------------------------------
# Config
# ------------------------------
cat <<EOL > "$ROOT/Config/larashield.php"
<?php
return [
    'auth_guard' => 'sanctum',
];
EOL

# ------------------------------
# User Model
# ------------------------------
cat <<EOL > "$ROOT/Models/User.php"
<?php
namespace Larashield\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected \$fillable = ['name','email','password','phone','gender','user_type','status','email_verified_at'];
    protected \$hidden = ['password','remember_token'];
}
EOL

# ------------------------------
# User Migration
# ------------------------------
cat <<EOL > "$ROOT/database/migrations/2025_09_01_000000_create_users_table.php"
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('email')->unique();
            \$table->string('phone')->unique();
            \$table->enum('gender', ['male', 'female', 'other']);
            \$table->string('password');
            \$table->string('user_type')->default('sadmin'); 
            \$table->boolean('status')->default(true);
            \$table->timestamp('email_verified_at')->nullable();
            \$table->rememberToken();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
EOL

# ------------------------------
# User Policy
# ------------------------------
cat <<EOL > "$ROOT/Policies/UserPolicy.php"
<?php
namespace Larashield\Policies;

use Larashield\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User \$user): bool
    {
        return \$user->hasRole(['superadmin','admin']);
    }

    public function view(User \$user, User \$model): bool
    {
        \$excludedRoles = ['b2b','b2c'];
        return \$user->hasRole('superadmin') && !\$model->hasRole(\$excludedRoles);
    }

    public function create(User \$user): bool
    {
        return \$user->hasRole('superadmin');
    }

    public function update(User \$user, User \$model): bool
    {
        \$excludedRoles = ['b2b','b2c','superadmin'];
        return \$user->hasRole('superadmin') && !\$model->hasRole(\$excludedRoles);
    }

    public function delete(User \$user, User \$model): bool
    {
        \$excludedRoles = ['b2b','b2c','superadmin'];
        return \$user->hasRole('superadmin') && !\$model->hasRole(\$excludedRoles);
    }

    public function restore(User \$user, User \$model): bool { return false; }
    public function forceDelete(User \$user, User \$model): bool { return false; }
}
EOL

# ------------------------------
# User Request
# ------------------------------
cat <<EOL > "$ROOT/Http/Requests/UserRequest.php"
<?php
namespace Larashield\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation()
    {
        \$this->merge([
            'user_type' => config('setup-config.admin.user_type'),
            'phone' => '+88' . substr(\$this->phone, -11),
        ]);
    }

    public function rules(): array
    {
        switch (\$this->method()) {
            case 'GET': return [];
            case 'POST':
                return [
                    'image'=>'image|mimes:jpeg,png,jpg,gif|max:2048',
                    'name'=>'required|string|max:255',
                    'email'=>'required|email|unique:users|max:255',
                    'phone'=>'required|numeric|regex:/^(?:\+?88)?01[3-9]\d{8}$/|unique:users,phone',
                    'gender'=>'required|in:male,female,other',
                    'password'=>'required|string|min:6|confirmed',
                    'status'=>'boolean',
                    'email_verified_at'=>'nullable|date',
                    'role'=>['required','not_in:superadmin,b2b,b2c','exists:roles,name'],
                    'permissions'=>'array',
                    'permissions.*'=>'distinct|exists:permissions,name'
                ];
            case 'PUT':
            case 'PATCH':
                \$id = \$this->route('user') ? \$this->route('user')->id : null;
                return [
                    'name'=>'required|string|max:255',
                    'email'=>'required|email|max:255|unique:users,email,' . \$id,
                    'phone'=>['required','numeric','regex:/^(?:\+?88)?01[3-9]\d{8}$/','unique:users,phone,' . \$id],
                    'gender'=>'required|in:male,female,other',
                    'user_type'=>'nullable|in:sadmin,admin,b2b,b2c',
                    'password'=>'nullable|string|min:6|confirmed',
                    'status'=>'sometimes|boolean',
                    'email_verified_at'=>'nullable|date',
                    'role'=>['nullable','not_in:superadmin,b2b,b2c','exists:roles,name'],
                    'permissions'=>'array',
                    'permissions.*'=>'distinct|exists:permissions,name'
                ];
            case 'DELETE': return [];
            default: return [];
        }
    }
}
EOL

# ------------------------------
# Controllers
# ------------------------------
# UserController
cat <<EOL > "$ROOT/Http/Controllers/UserController.php"
<?php
namespace Larashield\Http\Controllers;

use Larashield\Models\User;
use Illuminate\Http\Request;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Larashield\Http\Requests\UserRequest;

class UserController extends Controller
{
    use ResponseHelperTrait;
    protected \$resourceService;

    public function __construct(ResourceService \$resourceService, Request \$request)
    {
        \$this->resourceService = \$resourceService;
        \$this->resourceService->setValue(\$request, new User);
    }

    public function index(Request \$request)
    {
        \$this->authorize('viewAny', User::class);
        return \$this->resourceService->index();
    }

    public function store(UserRequest \$request)
    {
        \$this->authorize('create', User::class);
        return \$this->resourceService
            ->message('User created successfully')
            ->responseCode(HttpResponse::HTTP_CREATED)
            ->store(\$request->validated());
    }

    public function show(User \$user)
    {
        \$this->authorize('view', \$user);
        return \$this->resourceService->show(null, \$user);
    }

    public function update(UserRequest \$request, User \$user)
    {
        \$this->authorize('update', \$user);
        return \$this->resourceService->update(\$request->validated(), \$user);
    }

    public function destroy(User \$user)
    {
        \$this->authorize('delete', \$user);
        return \$this->resourceService
            ->message('User deleted successfully')
            ->responseCode(HttpResponse::HTTP_OK)
            ->destroy(\$user);
    }
}
EOL

# AuthController
cat <<EOL > "$ROOT/Http/Controllers/AuthController.php"
<?php
namespace Larashield\Http\Controllers;

use Illuminate\Http\Request;
use Larashield\Models\User;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ResponseHelperTrait;
    protected \$resourceService;

    public function __construct(ResourceService \$resourceService, Request \$request)
    {
        \$this->resourceService = \$resourceService;
        \$this->resourceService->setValue(\$request, new User);
    }

    public function login(Request \$request)
    {
        \$request->validate(['email'=>'required|email','password'=>'required|string']);
        \$user = User::where('email', \$request->email)->first();

        if(!\$user || !Hash::check(\$request->password, \$user->password)) {
            return \$this->resourceService
                ->message('Invalid credentials')
                ->responseCode(HttpResponse::HTTP_UNAUTHORIZED);
        }

        \$token = \$user->createToken('api-token')->plainTextToken;

        return \$this->resourceService
            ->message('Login successful')
            ->responseCode(HttpResponse::HTTP_OK)
            ->show(['user'=>\$user,'token'=>\$token], \$user);
    }

    public function logout(Request \$request)
    {
        \$request->user()->currentAccessToken()->delete();
        return \$this->resourceService
            ->message('Logout successful')
            ->responseCode(HttpResponse::HTTP_OK);
    }
}
EOL

# ------------------------------
# Routes
# ------------------------------
cat <<EOL > "$ROOT/routes/api.php"
<?php

use Illuminate\Support\Facades\Route;
use Larashield\Http\Controllers\UserController;
use Larashield\Http\Controllers\AuthController;

Route::prefix('api/v1')->group(function() {
    Route::post('/login', [AuthController::class,'login']);
    Route::post('/logout', [AuthController::class,'logout'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function() {
        Route::apiResource('users', UserController::class);
    });
});
EOL

# ------------------------------
# Service Provider
# ------------------------------
cat <<EOL > "$ROOT/Providers/LarashieldServiceProvider.php"
<?php
namespace Larashield\Providers;

use Illuminate\Support\ServiceProvider;

class LarashieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings if needed
    }

    public function boot(): void
    {
        // Load routes
        \$this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        // Load config
        \$this->publishes([__DIR__.'/../Config/larashield.php'=>config_path('larashield.php')], 'config');
        // Load migrations
        \$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
EOL

# ------------------------------
# Composer.json
# ------------------------------
cat <<EOL > "composer.json"
{
  "name": "sabbir/larashield",
  "description": "User, Role, Auth management package with ResponseBuilder and Policies",
  "type": "library",
  "license": "MIT",
  "autoload": {
    "psr-4": {
      "Larashield\\\\": "src/"
    }
  },
  "authors": [
    { "name": "SabbirHR", "email": "sabbirhossen860@gmail.com" }
  ],
  "require": {
    "spatie/laravel-permission": "^6.10",
    "marcin-orlowski/laravel-api-response-builder": "^12.0",
    "laravel/sanctum": "^4.0",
    "sabbir/response-builder": "@dev"
  },
  "repositories": [
       {
           "type": "path",
           "url": "../response-builder"
       }
   ],
  "extra": {
    "laravel": {
      "providers": [
        "Larashield\\\\Providers\\\\LarashieldServiceProvider"
      ]
    }
  }
}
EOL
# ------------------------------
# Composer Install sabbir/response-builder
# ------------------------------
echo "📦 Installing sabbir/response-builder..."
composer require sabbir/response-builder:@dev
echo "✅ Larashield package fully created under $ROOT!"
echo "Next steps: run composer dump-autoload and migrate database."
