<?php

namespace Tests\Feature;

use App\Models\VisitorProfile;
use App\Models\VisitorSession;
use App\Services\VisitorTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['visitor-monitor.geo.enabled' => false]);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_heartbeat_creates_a_profile_and_visit_session(): void
    {
        $payload = [
            'visitor_key' => '11111111-1111-4111-8111-111111111111',
            'session_token' => '22222222-2222-4222-8222-222222222222',
            'page_url' => 'https://swissmadecorp.test/watch-products',
            'page_path' => '/watch-products',
            'page_title' => 'Watch Products',
            'referrer_url' => 'https://google.com/search?q=swissmadecorp',
        ];

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'PHPUnit Browser',
        ])->postJson('/visitor-monitor/heartbeat', $payload);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'visitor_key' => $payload['visitor_key'],
                'session_token' => $payload['session_token'],
                'is_returning' => false,
                'visit_count' => 1,
            ]);

        $this->assertDatabaseHas('visitor_profiles', [
            'visitor_key' => $payload['visitor_key'],
            'visit_count' => 1,
            'last_known_ip' => '8.8.8.8',
        ]);

        $this->assertDatabaseHas('visitor_sessions', [
            'session_token' => $payload['session_token'],
            'landing_path' => '/watch-products',
            'current_path' => '/watch-products',
            'referrer_host' => 'google.com',
            'ended_at' => null,
        ]);
    }

    public function test_chat_identity_can_name_a_saved_visitor_profile(): void
    {
        $profile = VisitorProfile::query()->create([
            'visitor_key' => '33333333-3333-4333-8333-333333333333',
            'visit_count' => 1,
            'first_seen_at' => now(),
        ]);

        app(VisitorTrackingService::class)->rememberChatIdentity(
            $profile->visitor_key,
            'John Customer',
            'john@example.com',
        );

        $this->assertDatabaseHas('visitor_profiles', [
            'visitor_key' => $profile->visitor_key,
            'display_name' => 'John Customer',
            'email' => 'john@example.com',
        ]);
    }

    public function test_leave_marks_the_session_as_ended(): void
    {
        $profile = VisitorProfile::query()->create([
            'visitor_key' => '44444444-4444-4444-8444-444444444444',
            'visit_count' => 1,
            'first_seen_at' => now(),
        ]);

        $session = VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '55555555-5555-4555-8555-555555555555',
            'started_at' => now()->subMinute(),
            'last_seen_at' => now()->subSeconds(5),
            'current_path' => '/product-details/demo',
        ]);

        $response = $this->postJson('/visitor-monitor/leave', [
            'session_token' => $session->session_token,
            'page_path' => '/product-details/demo',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertNotNull($session->fresh()->ended_at);
    }

    public function test_reusing_an_ended_session_token_creates_a_new_visit_and_marks_visitor_as_returning(): void
    {
        $profile = VisitorProfile::query()->create([
            'visitor_key' => '66666666-6666-4666-8666-666666666666',
            'visit_count' => 1,
            'first_seen_at' => now()->subHour(),
            'last_seen_at' => now()->subMinutes(10),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '77777777-7777-4777-8777-777777777777',
            'started_at' => now()->subMinutes(20),
            'last_seen_at' => now()->subMinutes(10),
            'ended_at' => now()->subMinutes(9),
            'current_path' => '/watch-products',
        ]);

        $response = $this->postJson('/visitor-monitor/heartbeat', [
            'visitor_key' => $profile->visitor_key,
            'session_token' => '77777777-7777-4777-8777-777777777777',
            'page_url' => 'https://swissmadecorp.test/',
            'page_path' => '/',
            'page_title' => 'Home',
        ]);

        $response->assertOk();
        $this->assertNotSame('77777777-7777-4777-8777-777777777777', $response->json('session_token'));
        $this->assertTrue($response->json('is_returning'));
        $this->assertSame(2, $response->json('visit_count'));

        $this->assertDatabaseCount('visitor_sessions', 2);
        $this->assertDatabaseHas('visitor_profiles', [
            'visitor_key' => $profile->visitor_key,
            'visit_count' => 2,
        ]);
    }

    public function test_left_history_only_contains_visitors_who_already_left(): void
    {
        $profile = VisitorProfile::query()->create([
            'visitor_key' => '88888888-8888-4888-8888-888888888888',
            'visit_count' => 2,
            'first_seen_at' => now()->subDay(),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '99999999-9999-4999-8999-999999999999',
            'started_at' => now()->subHours(2),
            'last_seen_at' => now()->subHours(1),
            'ended_at' => now()->subHour(),
            'current_path' => '/watch-products',
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'started_at' => now()->subSeconds(20),
            'last_seen_at' => now()->subSeconds(5),
            'current_path' => '/checkout',
        ]);

        $leftHistory = app(VisitorTrackingService::class)->leftHistory();

        $this->assertSame(1, $leftHistory->total());
        $this->assertSame('/watch-products', $leftHistory->items()[0]->current_path);
    }
}
