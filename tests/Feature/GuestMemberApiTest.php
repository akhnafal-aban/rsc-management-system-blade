<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestMemberApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    public function test_auth_user_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/user');
        $response->assertStatus(401);
    }

    public function test_validate_member_code_rejects_empty(): void
    {
        $response = $this->postJson('/api/auth/claim/validate-member-code', [
            'member_code' => '',
        ]);
        $response->assertStatus(422);
    }

    public function test_validate_member_code_returns_404_for_unknown_code(): void
    {
        $response = $this->postJson('/api/auth/claim/validate-member-code', [
            'member_code' => 'INVALID-99999',
        ]);
        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_validate_member_code_succeeds_for_existing_member(): void
    {
        $member = Member::factory()->create(['member_code' => '12-25475']);
        $response = $this->postJson('/api/auth/claim/validate-member-code', [
            'member_code' => '12-25475',
        ]);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.member_code', '12-25475');
        $response->assertJsonPath('data.name', $member->name);
    }

    public function test_validate_member_code_fails_when_member_already_has_user(): void
    {
        $member = Member::factory()->create(['member_code' => '12-25476']);
        User::factory()->create(['member_id' => $member->id]);
        $response = $this->postJson('/api/auth/claim/validate-member-code', [
            'member_code' => '12-25476',
        ]);
        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_dashboard_returns_data_for_authenticated_member_user(): void
    {
        $member = Member::factory()->create();
        $user = User::factory()->create([
            'member_id' => $member->id,
            'role' => \App\Enums\UserRole::MEMBER,
        ]);
        $response = $this->actingAs($user)->getJson('/api/dashboard');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $member->member_code);
        $response->assertJsonPath('data.name', $member->name);
    }
}
