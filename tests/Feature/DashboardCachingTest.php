<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_dashboard_data_is_cached(): void
    {
        // Clear cache first
        Cache::flush();

        // First request should hit database
        $response1 = $this->get(route('dashboard'));
        $response1->assertStatus(200);

        // Check if cache key exists
        $cacheKey = 'dashboard_data_'.now()->format('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));

        // Second request should use cache
        $response2 = $this->get(route('dashboard'));
        $response2->assertStatus(200);

        // Both responses should be identical
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    public function test_dashboard_cache_invalidates_on_member_creation(): void
    {
        // Clear cache first
        Cache::flush();

        // Load dashboard to create cache
        $this->get(route('dashboard'));
        $cacheKey = 'dashboard_data_'.now()->format('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));

        // Create a new member
        $memberData = [
            'name' => 'Test Member',
            'email' => 'test@example.com',
            'phone' => '08123456789',
            'membership_duration' => '12',
            'payment_method' => 'CASH',
            'payment_notes' => 'Test payment',
        ];

        $this->post(route('member.store'), $memberData);

        // Cache should be invalidated
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_dashboard_cache_invalidates_on_membership_extension(): void
    {
        // Clear cache first
        Cache::flush();

        // Create a member first
        $member = Member::factory()->create([
            'status' => \App\Enums\MemberStatus::ACTIVE,
            'exp_date' => now()->addMonth()->format('Y-m-d'),
        ]);

        // Load dashboard to create cache
        $this->get(route('dashboard'));
        $cacheKey = 'dashboard_data_'.now()->format('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));

        // Extend membership
        $extendData = [
            'member_id' => $member->id,
            'membership_duration' => '3',
            'payment_method' => 'CASH',
            'payment_notes' => 'Test extension',
        ];

        $this->post(route('member.extend.store'), $extendData);

        // Cache should be invalidated
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_dashboard_service_invalidate_cache_method(): void
    {
        $dashboardService = app(DashboardService::class);

        // Create some cache entries
        $today = now()->format('Y-m-d-H');
        $cacheKeys = [
            'dashboard_data_'.$today,
            'dashboard_stats_'.$today,
            'dashboard_charts_'.$today,
            'dashboard_insights_'.$today,
        ];

        foreach ($cacheKeys as $key) {
            Cache::put($key, 'test_data', 300);
        }

        // Verify cache exists
        foreach ($cacheKeys as $key) {
            $this->assertTrue(Cache::has($key));
        }

        // Invalidate cache
        $dashboardService->invalidateDashboardCache();

        // Verify cache is cleared
        foreach ($cacheKeys as $key) {
            $this->assertFalse(Cache::has($key));
        }
    }

    public function test_dashboard_stats_are_cached_separately(): void
    {
        // Clear cache first
        Cache::flush();

        $dashboardService = app(DashboardService::class);

        // Access stats through reflection to test caching
        $reflection = new \ReflectionClass($dashboardService);
        $method = $reflection->getMethod('getStats');
        $method->setAccessible(true);

        // First call should create cache
        $stats1 = $method->invoke($dashboardService);
        $cacheKey = 'dashboard_stats_'.now()->format('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));

        // Second call should use cache
        $stats2 = $method->invoke($dashboardService);
        $this->assertEquals($stats1, $stats2);
    }

    public function test_dashboard_charts_are_cached_separately(): void
    {
        // Clear cache first
        Cache::flush();

        $dashboardService = app(DashboardService::class);

        // Access charts through reflection to test caching
        $reflection = new \ReflectionClass($dashboardService);
        $method = $reflection->getMethod('getChartData');
        $method->setAccessible(true);

        // First call should create cache
        $charts1 = $method->invoke($dashboardService);
        $cacheKey = 'dashboard_charts_'.now()->format('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));

        // Second call should use cache
        $charts2 = $method->invoke($dashboardService);
        $this->assertEquals($charts1, $charts2);
    }
}
