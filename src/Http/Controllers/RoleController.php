<?php

namespace Larashield\Http\Controllers;

use Illuminate\Routing\Controller;
use Larashield\Http\Requests\RoleRequest;
use Sabbir\ResponseBuilder\Constants\ApiCodes;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Spatie\Permission\Models\Role;

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

    public function show($id)
    {
        return $this->resourceService->show(null, Role::with(['permissions',])->findOrFail($id));
    }

    public function update(RoleRequest $request,  $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->validated());
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        return $this->successResponse(
            $role->load('permissions:id,name'),
            ApiCodes::OK,
            'Role has been updated successfully.'
        );
    }

    public function destroy($id)
    {
        // return 
        $role = Role::findOrFail($id);
        $role->permissions()->detach();
        return $this->resourceService->destroy($role);
    }
}
