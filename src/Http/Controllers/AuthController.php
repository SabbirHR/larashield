<?php

namespace Larashield\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Larashield\Http\Requests\RegistrationRequest;
use Larashield\Models\User;
use OwenIt\Auditing\Models\Audit;
use Sabbir\ResponseBuilder\Constants\ApiCodes;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthController extends Controller
{
    use ResponseHelperTrait;

    protected $resourceService;

    public function __construct(ResourceService $resourceService, Request $request)
    {
        $this->resourceService = $resourceService;
        $this->resourceService->setValue($request, new User);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                null,
                ApiCodes::UNAUTHORIZED,
                'Invalid credentials',
                HttpResponse::HTTP_UNAUTHORIZED
            );
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $this->logAuthAudit($request, $user, 'login', ['status' => 'logged_in']);
        return $this->successResponse(
            ['user' => $user, 'token' => $token],
            ApiCodes::OK,
            'Login successful',
            HttpResponse::HTTP_OK
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $this->logAuthAudit($request, $request->user(), 'logout', ['status' => 'logged_out']);

        return $this->successResponse(
            null,
            ApiCodes::OK,
            'Logout successful',
            HttpResponse::HTTP_OK
        );
    }

    /**
     * User Registration
     */
    public function registration(RegistrationRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'b2c', // default user_type
            'status' => 1,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse(
            [
                'user' => $user,
                'token' => $token
            ],
            ApiCodes::OK,
            'Registration successful',
            HttpResponse::HTTP_OK
        );
    }
    /**
     * Get authenticated user details
     */
    public function userProfile()
    {
        return $this->successResponse(
            [
                'user' => auth()->user()->makeHidden(['roles', 'permissions']),
                'role' => auth()->user()->getRoleNames(),
                'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
            ],
            ApiCodes::OK,
            'User profile retrieved successfully.'
        );
    }

    /**
     * 🔐 Private helper to log login/logout audits
     */
    private function logAuthAudit(Request $request, User $user, string $event, array $newValues = []): void
    {
        Audit::create([
            'user_type'      => $user->getMorphClass(),
            'user_id'        => $user->id,
            'event'          => $event,
            'auditable_type' => get_class($user),
            'auditable_id'   => $user->id,
            'old_values'     => [],
            'new_values'     => $newValues,
            'url'            => $request->fullUrl(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->header('User-Agent'),
            'tags'           => "User " . ucfirst($event),
        ]);
    }
}
