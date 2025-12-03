<?php

namespace Larashield\Http\Controllers;

use Larashield\Http\Controllers\Controller;
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
        $this->middleware('permission:read_user', ['only' => ['index', 'show']]);
        $this->middleware('permission:create_user', ['only' => ['create', 'store']]);
        $this->middleware('permission:update_user', ['only' => ['update']]);
        $this->middleware('permission:delete_user', ['only' => ['destroy']]);
        $this->authorizeResource(User::class, 'user');
        $this->resourceService = $resourceService;
        $this->resourceService->setValue($request, new User);
    }

    public function index()
    {
        return $this->resourceService->index(null, User::with(['roles:id,name']));
    }

    public function store(UserRequest $request)
    {
        $user = User::create($request->validated());
        $user->user_type = config('setup-config.admin.user_type');
        $user->save();
        $user->assignRole($request->validated()['role']);
        $user->givePermissionTo($request->validated()['permissions'] ?? []);
        return $this->resourceService->store([], null, $user);
    }

    public function show($id)
    {
        return $this->resourceService->show('User retrieved successfully', User::findOrFail($id));
    }

    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());
        isset($request->validated()['role']) ? $user->syncRoles($request->validated()['role']) : null;
        $user->syncPermissions($request->validated()['permissions'] ?? []);
        return $this->resourceService->update([], $user->load(["roles", "permissions:id,name"]));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        return $this->resourceService->message('User deleted successfully')->responseCode(HttpResponse::HTTP_OK)->destroy($user);
    }
}
