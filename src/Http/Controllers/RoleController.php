<?php

namespace Larashield\Http\Controllers;

use Larashield\Http\Controllers\Controller;
use Larashield\Http\Requests\RoleRequest;
use Sabbir\ResponseBuilder\Constants\ApiCodes;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Spatie\Permission\Models\Role;
use OwenIt\Auditing\Models\Audit;

class RoleController extends Controller
{
    use ResponseHelperTrait;
    protected $resourceService;

    public function __construct(ResourceService $resourceService)
    {
        // Middleware for permission-based access control
        $this->middleware('permission:read_role', ['only' => ['index', 'show']]);
        $this->middleware('permission:create_role', ['only' => ['create', 'store']]);
        $this->middleware('permission:update_role', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_role', ['only' => ['destroy']]);
        // $this->authorizeResource(Role::class, 'role');
        $this->resourceService = $resourceService;
        $this->resourceService->setValue(request(), new Role);
    }

    public function index()
    {
        $this->authorize('viewAny', Role::class);
        return $this->resourceService->index();
    }

    public function store(RoleRequest $request)
    {
        $this->authorize('create', Role::class);
        $role = Role::create($request->validated());
        $role->givePermissionTo($request->permissions);
        $this->logRoleAudit('created', $role);
        return $this->successResponse($role->load('permissions'), ApiCodes::OK, 'Role created');
    }

    public function show($id)
    {
        $role = Role::with(['permissions'])->findOrFail($id);
        $this->authorize('view', $role);
        return $this->resourceService->show(null, $role);
    }

    public function update(RoleRequest $request,  $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);
        $oldData = $role->toArray();
        $role->update($request->validated());
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        $this->logRoleAudit('updated', $role, $oldData);
        return $this->successResponse(
            $role->load('permissions:id,name'),
            ApiCodes::OK,
            'Role has been updated successfully.'
        );
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('delete', $role);
        $oldData = $role->toArray();
        $role->permissions()->detach();
        $role->delete();
        $this->logRoleAudit('updated', $role, $oldData);
        return $this->resourceService->destroy($role);
    }

    /**
     * Manual audit logging for Spatie Role model
     */
    protected function logRoleAudit(string $event, Role $role, array $oldData = [])
    {
        $user = auth()->user();
        $userRoles = $user ? $user->roles->pluck('name')->toArray() : [];

        Audit::create([
            'user_type' => $user?->getMorphClass(),
            'user_id' => $user?->id,
            'event' => $event,
            'auditable_type' => Role::class,
            'auditable_id' => $role->id ?? null,
            'old_values' => array_merge($oldData, ['user_roles' => $userRoles]),
            'new_values' => array_merge($role->toArray(), ['user_roles' => $userRoles]),
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'tags' => 'Role ' . ucfirst($event),
        ]);
    }
}
