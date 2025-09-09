<?php

namespace Larashield\Http\Controllers;

use Illuminate\Routing\Controller;
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

    public function index()
    {
        return $this->resourceService->index();
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());
        $role->givePermissionTo($request->permissions);
        return $this->successResponse($role->load('permissions'), 200, 'Role created');
    }

    public function show(Role $role)
    {
        return $this->resourceService->show(null, $role->load('permissions'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());
        $role->syncPermissions($request->permissions);
        return $this->successResponse($role->load('permissions'), 200, 'Role updated');
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        return $this->resourceService->destroy($role);
    }
}
