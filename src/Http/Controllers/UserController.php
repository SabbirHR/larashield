<?php

namespace Larashield\Http\Controllers;

use Illuminate\Routing\Controller;
use Larashield\Models\User;
use Illuminate\Http\Request;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Larashield\Http\Requests\UserRequest;

class UserController extends Controller
{
    use ResponseHelperTrait;
    protected $resourceService;

    public function __construct(ResourceService $resourceService, Request $request)
    {
        $this->resourceService = $resourceService;
        $this->resourceService->setValue($request, new User);
    }

    public function index()
    {
        return $this->resourceService->index(null, User::with(['roles:id,name']));
    }

    public function store(UserRequest $request)
    {
        // return $request;
        $user = User::create($request->validated());
        $user->user_type = config('setup-config.admin.user_type');
        $user->save();
        $user->assignRole($request->validated()['role']);
        $user->givePermissionTo($request->validated()['permissions'] ?? []);
        // $this->authorize('create', User::class);
        return $this->resourceService->store([], null, $user);
    }

    public function show($id)
    {
        return $this->resourceService->show('User retrieved successfully', User::findOrFail($id));
    }

    public function update(UserRequest $request, $id)
    {
        // return User::findOrFail($id)->load(["roles:id,name", "permissions:id,name"]);
        // dd(User::findOrFail($id));
        // $this->authorize('update', $user);
        $user = User::findOrFail($id);
        $user->update($request->validated());
        isset($request->validated()['role']) ? $user->syncRoles($request->validated()['role']) : null;
        $user->syncPermissions($request->validated()['permissions'] ?? []);
        return $this->resourceService->update([], $user->load(["roles", "permissions:id,name"]));
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        return $this->resourceService
            ->message('User deleted successfully')
            ->responseCode(HttpResponse::HTTP_OK)
            ->destroy($user);
    }
}
