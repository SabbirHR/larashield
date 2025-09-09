#!/bin/bash

PACKAGE_NAME="larashield"
ROOT="src"

echo "🔹 Setting up Role-Permission system for $PACKAGE_NAME..."

# ------------------------------
# Directories
# ------------------------------
DIRS=(
  "$ROOT/Config"
  "$ROOT/Models"
  "$ROOT/Http/Controllers"
  "$ROOT/Http/Requests"
  "$ROOT/Policies"
  "$ROOT/Providers"
  "$ROOT/routes"
  "$ROOT/database/migrations"
)

for dir in "${DIRS[@]}"; do
  mkdir -p "$dir"
  echo "Created directory: $dir"
done

# ------------------------------
# Composer Packages
# ------------------------------
echo "📦 Checking Composer packages..."

# Install Spatie permission if not exists
if ! composer show spatie/laravel-permission >/dev/null 2>&1; then
    echo "Installing spatie/laravel-permission..."
    composer require spatie/laravel-permission:^6.10
fi

# Install sabbir/response-builder if not exists
if ! composer show sabbir/response-builder >/dev/null 2>&1; then
    echo "Installing sabbir/response-builder..."
    composer require sabbir/response-builder:@dev
fi

echo "✅ Composer packages installed or already exist"

# ------------------------------
# Config
# ------------------------------
if [ ! -f "$ROOT/Config/permission.php" ]; then
    cp vendor/spatie/laravel-permission/config/permission.php "$ROOT/Config/permission.php"
    echo "Copied Spatie permission.php to $ROOT/Config/permission.php"
fi

cat <<EOL > "$ROOT/Config/larashield.php"
<?php
return [
    'auth_guard' => 'sanctum',
];
EOL

# ------------------------------
# Models
# ------------------------------
cat <<EOL > "$ROOT/Models/PermissionGroup.php"
<?php
namespace Larashield\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;

class PermissionGroup extends Model
{
    protected \$fillable = ['name'];

    public function permission_group_has_permission(): HasMany
    {
        return \$this->hasMany(PermissionPermissionGroup::class);
    }

    public function permission()
    {
        return \$this->belongsToMany(Permission::class);
    }
}
EOL

cat <<EOL > "$ROOT/Models/PermissionPermissionGroup.php"
<?php
namespace Larashield\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionPermissionGroup extends Model
{
    protected \$table = 'permission_permission_group';
    protected \$hidden = ['created_at','updated_at'];

    public function permission(): BelongsTo
    {
        return \$this->belongsTo(Permission::class,'permission_id');
    }

    public function roles()
    {
        return \$this->belongsToMany(Role::class,'role_has_permissions','permission_id','role_id');
    }
}
EOL

# ------------------------------
# Requests
# ------------------------------
cat <<EOL > "$ROOT/Http/Requests/RoleRequest.php"
<?php
namespace Larashield\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . (\$this->role->id ?? ''),
            'permissions' => 'array',
            'permissions.*' => 'distinct|exists:permissions,name',
        ];
    }
}
EOL

# ------------------------------
# Controllers
# ------------------------------
# PermissionController
cat <<'EOL' > "$ROOT/Http/Controllers/PermissionController.php"
<?php
namespace Larashield\Http\Controllers;

use Illuminate\Http\Request;
use Larashield\Models\PermissionGroup;
use Larashield\Models\PermissionPermissionGroup;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Sabbir\ResponseBuilder\Services\ResourceService;

class PermissionController extends Controller
{
    use ResponseHelperTrait;
    protected $resourceService;

    public function __construct(ResourceService $resourceService, Request $request)
    {
        $this->resourceService = $resourceService;
        $this->resourceService->setValue($request, new PermissionGroup);
    }

    public function index() { return $this->resourceService->index(); }

    public function store(Request $request)
    {
        $PermissionGroup = PermissionGroup::create(['name'=>$request->name]);
        $curd = ['create','read','update','delete'];
        foreach ($curd as $c) {
            $permission = Permission::create(['name'=>$c.'_'.$PermissionGroup->name,'guard_name'=>'web']);
            PermissionPermissionGroup::create([
                'type'=>$c,
                'permission_id'=>$permission->id,
                'permission_group_id'=>$PermissionGroup->id
            ]);
        }

        Role::firstWhere('name','superadmin')->givePermissionTo(Permission::all());
        return $this->successResponse(null,200,'Permission stored successfully');
    }

    public function show(PermissionGroup $permissionGroup)
    {
        return $this->resourceService->show(null, PermissionGroup::with(['permission','permission_group_has_permission'])->find($permissionGroup));
    }

    public function destroy(PermissionGroup $permissionGroup)
    {
        $permissions = PermissionPermissionGroup::where('permission_group_id',$permissionGroup->id)->pluck('permission_id');
        Permission::destroy($permissions);
        PermissionPermissionGroup::where('permission_group_id',$permissionGroup->id)->delete();
        $permissionGroup->delete();
        return $this->successResponse(null,200,'Permission group deleted');
    }
}
EOL

# RoleController
cat <<'EOL' > "$ROOT/Http/Controllers/RoleController.php"
<?php
namespace Larashield\Http\Controllers;

use Larashield\Http\Requests\RoleRequest;
use Spatie\Permission\Models\Role;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Sabbir\ResponseBuilder\Services\ResourceService;

class RoleController extends Controller
{
    use ResponseHelperTrait;
    protected $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
        $this->resourceService->setValue(request(), new Role);
    }

    public function index() { return $this->resourceService->index(); }

    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());
        $role->givePermissionTo($request->permissions);
        return $this->successResponse($role->load('permissions'),200,'Role created');
    }

    public function show(Role $role)
    {
        return $this->resourceService->show(null,$role->load('permissions'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());
        $role->syncPermissions($request->permissions);
        return $this->successResponse($role->load('permissions'),200,'Role updated');
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();
        return $this->successResponse(null,200,'Role deleted');
    }
}
EOL

# ------------------------------
# Policies
# ------------------------------
cat <<EOL > "$ROOT/Policies/PermissionGroupPolicy.php"
<?php
namespace Larashield\Policies;

class PermissionGroupPolicy
{
    public function viewAny(\$user) { return \$user->hasRole(['superadmin','admin']); }
    public function view(\$user,\$model) { return \$user->hasRole('superadmin'); }
    public function create(\$user) { return \$user->hasRole('superadmin'); }
    public function update(\$user,\$model) { return \$user->hasRole('superadmin'); }
    public function delete(\$user,\$model) { return \$user->hasRole('superadmin'); }
}
EOL

cat <<EOL > "$ROOT/Policies/RolePolicy.php"
<?php
namespace Larashield\Policies;

class RolePolicy
{
    public function viewAny(\$user) { return \$user->hasRole(['superadmin','admin']); }
    public function view(\$user,\$model) { return \$user->hasRole('superadmin'); }
    public function create(\$user) { return \$user->hasRole('superadmin'); }
    public function update(\$user,\$model) { return \$user->hasRole('superadmin'); }
    public function delete(\$user,\$model) { return \$user->hasRole('superadmin'); }
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
use Larashield\Http\Controllers\PermissionController;
use Larashield\Http\Controllers\RoleController;

Route::prefix('api/v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'registration'])->name('registration');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('permission-groups', PermissionController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::apiResource('roles', RoleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    });
});
EOL

# ------------------------------
# Migrations
# ------------------------------
cat <<EOL > "$ROOT/database/migrations/2025_09_01_000002_create_permission_groups_table.php"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permission_groups', function(Blueprint \$table) {
            \$table->id();
            \$table->string('name')->unique();
            \$table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('permission_groups'); }
};
EOL

cat <<EOL > "$ROOT/database/migrations/2025_09_01_000003_create_permission_permission_group_table.php"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permission_permission_group', function(Blueprint \$table) {
            \$table->id();
            \$table->string('type');
            \$table->unsignedBigInteger('permission_group_id');
            \$table->unsignedBigInteger('permission_id');
            \$table->timestamps();

            \$table->foreign('permission_group_id')->references('id')->on('permission_groups')->onDelete('cascade');
            \$table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('permission_permission_group'); }
};
EOL

echo "✅ Role-Permission setup created successfully for $PACKAGE_NAME!"
echo "Next steps: composer dump-autoload && php artisan migrate"
