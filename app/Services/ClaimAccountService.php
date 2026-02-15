<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ClaimAccountService
{
    public function __construct(
        private readonly GoogleTokenVerifier $googleTokenVerifier
    ) {}

    /**
     * @return array{member_code: string, name: string}
     * @throws ValidationException
     */
    public function validateMemberCode(string $memberCode): array
    {
        $code = $this->sanitizeMemberCode($memberCode);
        if ($code === '') {
            throw ValidationException::withMessages(['member_code' => ['Kode member wajib diisi.']]);
        }

        $member = Member::where('member_code', $code)->first();
        if (! $member) {
            throw ValidationException::withMessages(['member_code' => ['Kode member tidak ditemukan.']]);
        }

        $existingUser = User::where('member_id', $member->id)->first();
        if ($existingUser) {
            throw ValidationException::withMessages(['member_code' => ['Akun untuk member ini sudah terdaftar.']]);
        }

        return [
            'member_code' => $member->member_code,
            'name' => $member->name,
        ];
    }

    /**
     * @throws ValidationException
     * @throws \Throwable
     */
    public function completeClaim(string $idToken, string $memberCode, string $phone): User
    {
        $googlePayload = $this->googleTokenVerifier->verifyIdToken($idToken);

        $code = $this->sanitizeMemberCode($memberCode);
        $phoneNormalized = $this->sanitizePhone($phone);

        if ($code === '') {
            throw ValidationException::withMessages(['member_code' => ['Kode member wajib diisi.']]);
        }
        if ($phoneNormalized === '') {
            throw ValidationException::withMessages(['phone' => ['Nomor telepon wajib diisi.']]);
        }

        $member = Member::where('member_code', $code)->first();
        if (! $member) {
            throw ValidationException::withMessages(['member_code' => ['Kode member tidak ditemukan.']]);
        }

        if ($this->normalizePhoneForComparison($member->phone ?? '') !== $this->normalizePhoneForComparison($phoneNormalized)) {
            throw ValidationException::withMessages(['phone' => ['Nomor telepon tidak sesuai dengan data member.']]);
        }

        $existingUser = User::where('member_id', $member->id)->first();
        if ($existingUser) {
            throw ValidationException::withMessages(['member_code' => ['Akun untuk member ini sudah terdaftar.']]);
        }

        $existingEmail = User::where('email', $googlePayload['email'])->first();
        if ($existingEmail) {
            throw ValidationException::withMessages(['email' => ['Email ini sudah terhubung dengan akun lain.']]);
        }

        $existingGoogleId = User::where('google_id', $googlePayload['sub'])->first();
        if ($existingGoogleId) {
            throw ValidationException::withMessages(['email' => ['Akun Google ini sudah terdaftar.']]);
        }

        $this->updateMemberEmail($member, $googlePayload['email']);

        return DB::transaction(function () use ($member, $googlePayload) {
            return User::create([
                'google_id' => $googlePayload['sub'],
                'name' => $googlePayload['name'] ?? $member->name,
                'email' => $googlePayload['email'],
                'password' => null,
                'role' => UserRole::MEMBER,
                'member_id' => $member->id,
            ]);
        });
    }

    private function sanitizeMemberCode(string $value): string
    {
        return trim(preg_replace('/\s+/', '', $value));
    }

    private function sanitizePhone(string $value): string
    {
        return trim(preg_replace('/[^\d+]/', '', $value));
    }

    private function normalizePhoneForComparison(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '62') && strlen($digits) > 10) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            return '62' . substr($digits, 1);
        }
        return $digits;
    }

    private function updateMemberEmail(Member $member, string $email): void
    {
        if ($member->email === $email) {
            return;
        } else {
            $member->update(['email' => $email]);
        }
    }
}
