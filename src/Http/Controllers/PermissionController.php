<?php

namespace Larashield\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Larashield\Models\PermissionGroup;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Larashield\Services\PermissionService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Sabbir\ResponseBuilder\Services\ResourceService;

class PermissionController extends Controller
{
    use ResponseHelperTrait;

    protected $resourceService;
    protected $permissionService;

    public function __construct(ResourceService $resourceService, PermissionService $permissionService, Request $request)
    {
        $this->resourceService = $resourceService;
        $this->permissionService = $permissionService;
        $this->resourceService->setValue($request, new PermissionGroup);
    }

    /**
     * List Permission Groups
     */
    public function index()
    {
        return $this->resourceService->index();
    }

    /**
     * Create Permission Group + CRUD Permissions
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permission_groups']);

        $this->permissionService->createPermissionGroup($request->name);

        // Give all permissions to superadmin role
        Role::firstWhere('name', 'superadmin')->givePermissionTo(Permission::all());

        return $this->successResponse(null, 200, 'Permission group stored successfully');
    }

    /**
     * Show Permission Group with relations
     */
    public function show(PermissionGroup $permissionGroup)
    {
        return $permissionGroup;
        return $this->resourceService->show(null, PermissionGroup::with(['permission', 'permission_group_has_permission'])->find($permissionGroup));
    }

    /**
     * Delete Permission Group and its permissions
     */
    public function destroy(PermissionGroup $permissionGroup)
    {
        $this->permissionService->deletePermissionGroup($permissionGroup->id);

        return $this->successResponse(null, 200, 'Permission group deleted successfully');
    }
}
