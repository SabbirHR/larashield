<?php

namespace Larashield\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Larashield\Models\PermissionGroup;
use Larashield\Services\PermissionService;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
    public function show($id)
    {
        return $this->resourceService->show(null, PermissionGroup::with(['permission',])->findOrFail($id));
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
