<?php

declare(strict_types=1);

namespace App\Services;

use Google\Auth\AccessToken;
use Illuminate\Support\Facades\Log;

final class GoogleTokenVerifier
{
    public function __construct(
        private readonly string $clientId
    ) {}

    /**
     * @return array{email: string, email_verified: bool, name?: string, picture?: string, sub: string}
     * @throws \InvalidArgumentException
     */
    public function verifyIdToken(string $idToken): array
    {
        $idToken = trim($idToken);
        if ($idToken === '') {
            throw new \InvalidArgumentException('Token tidak boleh kosong.');
        }

        try {
            $auth = new AccessToken();
            $payload = $auth->verify($idToken, [
                'audience' => $this->clientId,
                'throwException' => true,
            ]);
        } catch (\Exception $e) {
            Log::warning('Google ID token verification failed', [
                'message' => $e->getMessage(),
            ]);
            throw new \InvalidArgumentException('Token tidak valid atau telah kedaluwarsa.', 0, $e);
        }

        if (! is_array($payload)) {
            throw new \InvalidArgumentException('Token tidak valid.');
        }

        $email = $payload['email'] ?? null;
        if (! is_string($email) || $email === '') {
            throw new \InvalidArgumentException('Email tidak ditemukan dalam token.');
        }

        $emailVerified = $payload['email_verified'] ?? false;
        if (! filter_var($emailVerified, FILTER_VALIDATE_BOOLEAN)) {
            throw new \InvalidArgumentException('Email belum terverifikasi oleh Google.');
        }

        $sub = $payload['sub'] ?? null;
        if (! is_string($sub) || $sub === '') {
            throw new \InvalidArgumentException('Sub (Google ID) tidak ditemukan dalam token.');
        }

        return [
            'email' => $email,
            'email_verified' => true,
            'name' => isset($payload['name']) && is_string($payload['name']) ? $payload['name'] : null,
            'picture' => isset($payload['picture']) && is_string($payload['picture']) ? $payload['picture'] : null,
            'sub' => $sub,
        ];
    }
}
