<?php
namespace Larashield\Http\Controllers;

use Illuminate\Http\Request;
use Larashield\Models\User;
use Sabbir\ResponseBuilder\Services\ResourceService;
use Sabbir\ResponseBuilder\Traits\ResponseHelperTrait;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Support\Facades\Hash;

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
        $request->validate(['email'=>'required|email','password'=>'required|string']);
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)) {
            return $this->resourceService
                ->message('Invalid credentials')
                ->responseCode(HttpResponse::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->resourceService
            ->message('Login successful')
            ->responseCode(HttpResponse::HTTP_OK)
            ->show(['user'=>$user,'token'=>$token], $user);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->resourceService
            ->message('Logout successful')
            ->responseCode(HttpResponse::HTTP_OK);
    }
}
