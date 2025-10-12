<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_can_view_member_index_page(): void
    {
        $response = $this->get(route('member.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.main.member.member');
    }

    public function test_can_view_create_member_page(): void
    {
        $response = $this->get(route('member.create'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.main.member.create');
    }

    public function test_can_create_new_member(): void
    {
        $memberData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'exp_date' => now()->addYear()->format('Y-m-d'),
            'status' => \App\Enums\MemberStatus::ACTIVE->value,
        ];

        $response = $this->post(route('member.store'), $memberData);

        $response->assertRedirect();
        $this->assertDatabaseHas('members', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_view_member_detail_page(): void
    {
        $member = Member::factory()->create();

        $response = $this->get(route('member.show', $member));

        $response->assertStatus(200);
        $response->assertViewIs('pages.main.member.show');
        $response->assertViewHas('member');
    }

    public function test_can_view_edit_member_page(): void
    {
        $member = Member::factory()->create();

        $response = $this->get(route('member.edit', $member));

        $response->assertStatus(200);
        $response->assertViewIs('pages.main.member.edit');
        $response->assertViewHas('member');
    }

    public function test_can_update_member(): void
    {
        $member = Member::factory()->create();

        $updateData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '08123456789',
            'exp_date' => now()->addYear()->format('Y-m-d'),
            'status' => \App\Enums\MemberStatus::INACTIVE->value,
        ];

        $response = $this->put(route('member.update', $member), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Jane Doe',
        ]);
    }

    public function test_can_suspend_member(): void
    {
        $member = Member::factory()->create(['status' => \App\Enums\MemberStatus::ACTIVE]);

        $response = $this->post(route('member.suspend', $member));

        $response->assertRedirect();
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'status' => \App\Enums\MemberStatus::INACTIVE->value,
        ]);
    }

    public function test_can_activate_member(): void
    {
        $member = Member::factory()->create(['status' => \App\Enums\MemberStatus::INACTIVE]);

        $response = $this->post(route('member.activate', $member));

        $response->assertRedirect();
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'status' => \App\Enums\MemberStatus::ACTIVE->value,
        ]);
    }

    public function test_can_delete_member(): void
    {
        $member = Member::factory()->create();

        $response = $this->delete(route('member.destroy', $member));

        $response->assertRedirect();
        $this->assertDatabaseMissing('members', [
            'id' => $member->id,
        ]);
    }
}
