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
    protected $resourceService;

    public function __construct(ResourceService $resourceService, Request $request)
    {
        $this->resourceService = $resourceService;
        $this->resourceService->setValue($request, new User);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        return $this->resourceService->index();
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);
        return $this->resourceService
            ->message('User created successfully')
            ->responseCode(HttpResponse::HTTP_CREATED)
            ->store($request->validated());
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return $this->resourceService->show(null, $user);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        return $this->resourceService->update($request->validated(), $user);
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
