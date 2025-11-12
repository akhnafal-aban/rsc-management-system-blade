<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CommandNotification;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCachingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHasAll([
            'stats',
            'activities',
            'charts',
            'insights',
        ]);
    }

    public function test_dashboard_service_returns_expected_sections(): void
    {
        $dashboardService = app(DashboardService::class);

        $data = $dashboardService->getDashboardData();

        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('activities', $data);
        $this->assertArrayHasKey('charts', $data);
        $this->assertArrayHasKey('insights', $data);

        $this->assertCount(4, $data['stats']);
        $this->assertArrayHasKey('weekly_trend', $data['charts']);
        $this->assertArrayHasKey('member_distribution', $data['charts']);
        $this->assertArrayHasKey('daily_activity', $data['charts']);
    }

    public function test_command_notifications_are_persisted(): void
    {
        CommandNotification::query()->create([
            'command' => 'Auto Check-out Process',
            'status' => 'success',
            'message' => 'Test notification',
            'member_name' => 'Sample Member',
            'checkout_at' => now(),
            'is_read' => false,
        ]);

        $response = $this->getJson(route('notifications.scheduled-commands'));

        $response->assertOk()
            ->assertJson([
                'total' => 1,
            ]);

        $this->assertNotEmpty($response->json('notifications'));
        $this->assertFalse($response->json('notifications')[0]['read']);
    }
}
