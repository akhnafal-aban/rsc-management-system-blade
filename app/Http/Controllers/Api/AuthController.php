<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\CompleteClaimRequest;
use App\Http\Requests\Api\Auth\GoogleLoginRequest;
use App\Http\Requests\Api\Auth\ValidateMemberCodeRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Services\ClaimAccountService;
use App\Services\GoogleTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly GoogleTokenVerifier $googleTokenVerifier,
        private readonly ClaimAccountService $claimAccountService
    ) {}

    public function loginWithGoogle(GoogleLoginRequest $request): JsonResponse
    {
        try {
            $payload = $this->googleTokenVerifier->verifyIdToken($request->validated('id_token'));
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 401);
        }

        $user = User::where('email', $payload['email'])->first();

        if ($user) {
            if ($user->google_id !== null && $user->google_id !== $payload['sub']) {
                return $this->error('Email ini terhubung dengan akun Google lain.', 409);
            }
            if ($user->google_id === null) {
                $user->update(['google_id' => $payload['sub'], 'name' => $payload['name'] ?? $user->name]);
            }
            Auth::login($user, true);
            return $this->success([
                'user' => $this->userResource($user),
                'requires_claim' => false,
            ]);
        }

        return $this->success([
            'requires_claim' => true,
            'email' => $payload['email'],
            'name' => $payload['name'] ?? null,
        ]);
    }

    public function validateMemberCode(ValidateMemberCodeRequest $request): JsonResponse
    {
        try {
            $result = $this->claimAccountService->validateMemberCode(
                $request->validated('member_code')
            );
            return $this->success($result);
        } catch (ValidationException $e) {
            return $this->error('Validasi gagal', 422, $e->errors());
        }
    }

    public function completeClaim(CompleteClaimRequest $request): JsonResponse
    {
        try {
            $user = $this->claimAccountService->completeClaim(
                $request->validated('id_token'),
                $request->validated('member_code'),
                $request->validated('phone')
            );
            Auth::login($user, true);
            return $this->success([
                'user' => $this->userResource($user),
                'requires_claim' => false,
            ]);
        } catch (ValidationException $e) {
            return $this->error('Validasi gagal', 422, $e->errors());
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    public function user(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->error('Unauthenticated', 401);
        }
        return $this->success(['user' => $this->userResource($user)]);
    }

    public function logout(): JsonResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return $this->success(null, 'Logged out');
    }

    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'member_id' => $user->member_id,
        ];
    }
}
