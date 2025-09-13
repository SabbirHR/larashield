<?php

namespace Larashield\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Larashield\Models\PermissionGroup;
use Larashield\Services\PermissionService;
use Sabbir\ResponseBuilder\Constants\ApiCodes;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    use ResponseHelperTrait, AuthorizesRequests;

    protected $resourceService;
    protected $permissionService;

    public function __construct(ResourceService $resourceService, PermissionService $permissionService, Request $request)
    {
        $this->middleware('permission:read_permission', ['only' => ['index', 'show']]);
        $this->middleware('permission:create_permission', ['only' => ['create', 'store']]);
        $this->middleware('permission:update_permission', ['only' => ['update']]);
        $this->middleware('permission:delete_permission', ['only' => ['destroy']]);
        $this->authorizeResource(PermissionGroup::class, 'permission_group');
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

        return $this->successResponse(null, ApiCodes::OK, 'Permission stored successfully');
    }

    /**
     * Show Permission Group with relations
     */
    public function show(\Larashield\Models\PermissionGroup $permissionGroup)
    {
        // Policy will now be triggered automatically
        Log::info('[PermissionController] Authorizing view', [
            'user_id' => auth()->id(),
            'permission_group_id' => $permissionGroup->id,
            'user_roles' => auth()->user()->getRoleNames()->toArray(),
            'class' => get_class($permissionGroup)
        ]);
        dd($permissionGroup);
        $permissionGroup =  PermissionGroup::with(['permissions:id,name', 'permission_group_has_permission'])->findOrFail($id);
        Log::info('Authorize called', ['user' => auth()->user(), 'permission_group' => $permissionGroup]);

        $this->authorize('view', $permissionGroup);
        return $this->resourceService->show(null, $permissionGroup);
    }

    /**
     * Delete Permission Group and its permissions
     */
    public function destroy($id)
    {
        $permissionGroup = PermissionGroup::findOrFail($id);
        $this->permissionService->deletePermissionGroup($permissionGroup->id);
        return $this->successResponse(null, ApiCodes::OK, 'Permission deleted successfully');
    }
}
