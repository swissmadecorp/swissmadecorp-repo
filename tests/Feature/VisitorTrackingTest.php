<?php

namespace Tests\Feature;

use App\SearchCriteriaTrait;
use App\Models\VisitorProfile;
use App\Models\VisitorSession;
use App\Services\CrawlerDetectionService;
use App\Services\VisitorTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['visitor-monitor.geo.enabled' => false]);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutMiddleware(\App\Http\Middleware\DetectSqlInjection::class);
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

    public function test_verified_googlebot_heartbeat_is_ignored_and_not_saved(): void
    {
        $detector = Mockery::mock(CrawlerDetectionService::class);
        $detector->shouldReceive('detectExcludedBot')
            ->once()
            ->with('66.249.79.192', Mockery::type('string'))
            ->andReturn('Googlebot');

        $this->app->instance(CrawlerDetectionService::class, $detector);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '66.249.79.192',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ])->postJson('/visitor-monitor/heartbeat', [
            'visitor_key' => '34343434-3434-4343-8343-343434343434',
            'session_token' => '35353535-3535-4353-8353-353535353535',
            'page_url' => 'https://swissmadecorp.test/watch-products',
            'page_path' => '/watch-products',
            'page_title' => 'Watch Products',
        ]);

        $response->assertOk()->assertJson([
            'ok' => true,
            'ignored' => true,
        ]);

        $this->assertDatabaseCount('visitor_profiles', 0);
        $this->assertDatabaseCount('visitor_sessions', 0);
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

    public function test_live_page_trail_is_available_while_active_and_cleared_when_the_visitor_leaves(): void
    {
        $payload = [
            'visitor_key' => '45454545-4545-4545-8545-454545454545',
            'session_token' => '46464646-4646-4646-8646-464646464646',
            'page_url' => 'https://swissmadecorp.test/watch-products',
            'page_path' => '/watch-products',
            'page_title' => 'Watch Products',
            'visibility_state' => 'visible',
        ];

        $this->postJson('/visitor-monitor/heartbeat', $payload)->assertOk();

        $this->postJson('/visitor-monitor/heartbeat', [
            ...$payload,
            'page_url' => 'https://swissmadecorp.test/product-details/demo',
            'page_path' => '/product-details/demo',
            'page_title' => 'Product Details',
            'referrer_url' => 'https://swissmadecorp.test/watch-products',
        ])->assertOk();

        $activeVisitor = app(VisitorTrackingService::class)->activeVisitors()->first();

        $this->assertCount(2, $activeVisitor['live_page_trail']);
        $this->assertSame('/watch-products', $activeVisitor['live_page_trail'][0]['path']);
        $this->assertSame('/product-details/demo', $activeVisitor['live_page_trail'][1]['path']);

        $this->postJson('/visitor-monitor/leave', [
            'session_token' => $payload['session_token'],
            'page_url' => 'https://swissmadecorp.test/product-details/demo',
            'page_path' => '/product-details/demo',
            'page_title' => 'Product Details',
        ])->assertOk();

        $session = VisitorSession::query()->firstWhere('session_token', $payload['session_token']);

        $this->assertSame([], data_get($session?->metadata, 'recent_pages', []));
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

    public function test_new_visit_repairs_stale_visit_count_from_existing_sessions(): void
    {
        $profile = VisitorProfile::query()->create([
            'visitor_key' => '18181818-1818-4818-8818-181818181818',
            'visit_count' => 0,
            'first_seen_at' => now()->subDays(2),
            'last_seen_at' => now()->subHour(),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '19191919-1919-4919-8919-191919191919',
            'started_at' => now()->subDay(),
            'last_seen_at' => now()->subDay(),
            'ended_at' => now()->subDay(),
            'current_path' => '/watch-products',
        ]);

        $response = $this->postJson('/visitor-monitor/heartbeat', [
            'visitor_key' => $profile->visitor_key,
            'session_token' => '20202020-2020-4020-8020-202020202020',
            'page_url' => 'https://swissmadecorp.test/return-visit',
            'page_path' => '/return-visit',
            'page_title' => 'Return Visit',
            'visibility_state' => 'visible',
        ]);

        $response->assertOk()
            ->assertJson([
                'is_returning' => true,
                'visit_count' => 2,
            ]);

        $this->assertDatabaseHas('visitor_profiles', [
            'visitor_key' => $profile->visitor_key,
            'visit_count' => 2,
        ]);
    }

    public function test_same_ip_and_browser_can_reconnect_a_returning_visitor_when_the_browser_key_changes(): void
    {
        $profile = VisitorProfile::query()->create([
            'visitor_key' => '26262626-2626-4262-8262-262626262626',
            'visit_count' => 1,
            'first_seen_at' => now()->subDay(),
            'last_seen_at' => now()->subHour(),
            'last_known_ip' => '8.8.8.8',
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '27272727-2727-4272-8272-272727272727',
            'ip_address' => '8.8.8.8',
            'user_agent' => 'PHPUnit Browser',
            'started_at' => now()->subHours(2),
            'last_seen_at' => now()->subHour(),
            'ended_at' => now()->subHour(),
            'current_path' => '/watch-products',
        ]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'PHPUnit Browser',
        ])->postJson('/visitor-monitor/heartbeat', [
            'visitor_key' => '28282828-2828-4282-8282-282828282828',
            'session_token' => '29292929-2929-4292-8292-292929292929',
            'page_url' => 'https://swissmadecorp.test/returning-again',
            'page_path' => '/returning-again',
            'page_title' => 'Returning Again',
            'visibility_state' => 'visible',
        ]);

        $response->assertOk()
            ->assertJson([
                'visitor_key' => $profile->visitor_key,
                'is_returning' => true,
                'visit_count' => 2,
            ]);

        $profile->refresh();

        $this->assertSame(2, $profile->visit_count);
        $this->assertContains('28282828-2828-4282-8282-282828282828', $profile->metadata['visitor_key_aliases'] ?? []);
        $this->assertDatabaseCount('visitor_profiles', 1);
    }

    public function test_recent_internal_navigation_reuses_the_same_visit(): void
    {
        $profile = VisitorProfile::query()->create([
            'visitor_key' => '21212121-2121-4212-8212-212121212121',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(10),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        $session = VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '22222222-1212-4212-8212-222222222222',
            'started_at' => now()->subMinutes(10),
            'last_seen_at' => now()->subSeconds(3),
            'ended_at' => now()->subSecond(),
            'current_url' => 'https://swissmadecorp.test/watch-products',
            'current_path' => '/watch-products',
            'landing_url' => 'https://swissmadecorp.test/watch-products',
            'landing_path' => '/watch-products',
        ]);

        $response = $this->postJson('/visitor-monitor/heartbeat', [
            'visitor_key' => $profile->visitor_key,
            'session_token' => $session->session_token,
            'page_url' => 'https://swissmadecorp.test/product-details/demo',
            'page_path' => '/product-details/demo',
            'page_title' => 'Product Details',
            'referrer_url' => 'https://swissmadecorp.test/watch-products',
            'visibility_state' => 'visible',
        ]);

        $response->assertOk()
            ->assertJson([
                'session_token' => $session->session_token,
                'visit_count' => 1,
                'is_returning' => false,
            ]);

        $this->assertDatabaseCount('visitor_sessions', 1);
        $this->assertDatabaseHas('visitor_sessions', [
            'id' => $session->id,
            'current_path' => '/product-details/demo',
            'ended_at' => null,
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

    public function test_googlebot_sessions_are_hidden_from_history_active_visitors_and_stats(): void
    {
        config(['visitor-monitor.online_window_seconds' => 120]);

        $normalProfile = VisitorProfile::query()->create([
            'visitor_key' => '36363636-3636-4363-8363-363636363636',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(3),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        $botProfile = VisitorProfile::query()->create([
            'visitor_key' => '37373737-3737-4373-8373-373737373737',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(3),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $normalProfile->id,
            'session_token' => '38383838-3838-4383-8383-383838383838',
            'ip_address' => '8.8.8.8',
            'user_agent' => 'PHPUnit Browser',
            'started_at' => now()->subMinutes(3),
            'last_seen_at' => now()->subSeconds(5),
            'current_path' => '/watch-products',
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $botProfile->id,
            'session_token' => '39393939-3939-4393-8393-393939393939',
            'ip_address' => '66.249.79.192',
            'user_agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'started_at' => now()->subMinutes(2),
            'last_seen_at' => now()->subSeconds(5),
            'current_path' => '/product-details/demo',
        ]);

        $service = app(VisitorTrackingService::class);

        $this->assertCount(1, $service->activeVisitors());
        $this->assertSame(1, $service->history()->total());
        $this->assertSame(1, $service->stats()['total_visits']);
    }

    public function test_visitor_monitor_search_filters_by_ip_current_page_landing_page_and_referrer(): void
    {
        config(['visitor-monitor.online_window_seconds' => 120]);

        $matchingProfile = VisitorProfile::query()->create([
            'visitor_key' => '40404040-4040-4040-8040-404040404040',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(10),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        $otherProfile = VisitorProfile::query()->create([
            'visitor_key' => '41414141-4141-4141-8141-414141414141',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(10),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $matchingProfile->id,
            'session_token' => '42424242-4242-4242-8242-424242424242',
            'ip_address' => '8.8.8.8',
            'user_agent' => 'PHPUnit Browser',
            'landing_path' => '/contactus',
            'current_path' => '/product-details/patek-philippe-calatrava-3588-7g-blue-kgzsz-62243',
            'referrer_url' => 'https://google.com/search?q=calatrava',
            'started_at' => now()->subMinutes(10),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $otherProfile->id,
            'session_token' => '43434343-4343-4343-8343-434343434343',
            'ip_address' => '1.1.1.1',
            'user_agent' => 'PHPUnit Browser',
            'landing_path' => '/aboutus',
            'current_path' => '/product-details/cartier-tank-francaise-w51007q4-white-ngrz-47058',
            'referrer_url' => 'https://bing.com/search?q=cartier',
            'started_at' => now()->subMinutes(10),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        $search = new class {
            use SearchCriteriaTrait;
        };

        $searchTerm = $search->generateSearchQuery('8.8.8.8 /product-details/patek-philippe-calatrava /contactus google.com', [
            'visitor_sessions.ip_address',
            'visitor_sessions.current_path',
            'visitor_sessions.current_url',
            'visitor_sessions.landing_path',
            'visitor_sessions.landing_url',
            'visitor_sessions.referrer_url',
            'visitor_sessions.referrer_host',
        ]);

        $service = app(VisitorTrackingService::class);

        $this->assertCount(1, $service->activeVisitors($searchTerm));
        $this->assertSame(1, $service->totalVisitsHistory(12, $searchTerm)->total());
        $this->assertSame('8.8.8.8', $service->activeVisitors($searchTerm)->first()['ip_address']);
    }

    public function test_active_visitors_stay_visible_within_the_configured_online_window(): void
    {
        config(['visitor-monitor.online_window_seconds' => 120]);

        $profile = VisitorProfile::query()->create([
            'visitor_key' => 'bcbcbcbc-bcbc-4cbc-8cbc-bcbcbcbcbcbc',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(5),
            'last_seen_at' => now()->subSeconds(80),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '15151515-1515-4515-8515-151515151515',
            'started_at' => now()->subMinutes(5),
            'last_seen_at' => now()->subSeconds(80),
            'current_path' => '/watch-products',
        ]);

        $activeVisitors = app(VisitorTrackingService::class)->activeVisitors();

        $this->assertCount(1, $activeVisitors);
        $this->assertSame('/watch-products', $activeVisitors->first()['current_path']);
        $this->assertSame('5m 0s', $activeVisitors->first()['time_on_site']);
    }

    public function test_multiple_live_tabs_for_the_same_visitor_are_grouped_into_one_monitor_card(): void
    {
        config(['visitor-monitor.online_window_seconds' => 120]);

        $profile = VisitorProfile::query()->create([
            'visitor_key' => '23232323-2323-4232-8232-232323232323',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(8),
            'last_seen_at' => now()->subSeconds(10),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '24242424-2424-4242-8242-242424242424',
            'started_at' => now()->subMinutes(8),
            'last_seen_at' => now()->subSeconds(10),
            'current_url' => 'https://swissmadecorp.test/watch-products',
            'current_path' => '/watch-products',
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '25252525-2525-4252-8252-252525252525',
            'started_at' => now()->subMinutes(5),
            'last_seen_at' => now()->subSeconds(5),
            'current_url' => 'https://swissmadecorp.test/product-details/demo',
            'current_path' => '/product-details/demo',
        ]);

        $activeVisitors = app(VisitorTrackingService::class)->activeVisitors();

        $this->assertCount(1, $activeVisitors);
        $this->assertSame(2, $activeVisitors->first()['active_page_count']);
        $this->assertSame('/product-details/demo', $activeVisitors->first()['current_path']);
        $this->assertCount(2, $activeVisitors->first()['active_pages']);
    }

    public function test_active_page_groups_automatically_group_matching_watch_pages(): void
    {
        config(['visitor-monitor.online_window_seconds' => 120]);

        $patekProfile = VisitorProfile::query()->create([
            'visitor_key' => '30303030-3030-4030-8030-303030303030',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(4),
            'last_seen_at' => now()->subSeconds(10),
        ]);

        $cartierProfile = VisitorProfile::query()->create([
            'visitor_key' => '31313131-3131-4131-8131-313131313131',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(6),
            'last_seen_at' => now()->subSeconds(5),
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $patekProfile->id,
            'session_token' => '32323232-3232-4232-8232-323232323232',
            'started_at' => now()->subMinutes(4),
            'last_seen_at' => now()->subSeconds(10),
            'current_path' => '/product-details/patek-philippe-calatrava-3588-7g-blue-kgzsz-62243',
        ]);

        VisitorSession::query()->create([
            'visitor_profile_id' => $cartierProfile->id,
            'session_token' => '33333333-3333-4333-8333-333333333333',
            'started_at' => now()->subMinutes(6),
            'last_seen_at' => now()->subSeconds(5),
            'current_path' => '/product-details/cartier-tank-francaise-w51007q4-white-ngrz-47058',
        ]);

        $groups = app(VisitorTrackingService::class)->activePageGroups()->keyBy('key');

        $this->assertSame(1, $groups['product:patek-philippe-calatrava']['active_count']);
        $this->assertSame('Patek Philippe Calatrava', $groups['product:patek-philippe-calatrava']['label']);
        $this->assertSame(1, $groups['product:cartier-tank-francaise']['active_count']);
        $this->assertSame('Cartier Tank Francaise', $groups['product:cartier-tank-francaise']['label']);
    }

    public function test_hidden_tab_resumes_without_creating_a_new_visit_or_resetting_time(): void
    {
        config([
            'visitor-monitor.online_window_seconds' => 120,
            'visitor-monitor.background_window_seconds' => 900,
        ]);

        $profile = VisitorProfile::query()->create([
            'visitor_key' => '16161616-1616-4616-8616-161616161616',
            'visit_count' => 1,
            'first_seen_at' => now()->subMinutes(6),
            'last_seen_at' => now()->subMinutes(4),
        ]);

        $session = VisitorSession::query()->create([
            'visitor_profile_id' => $profile->id,
            'session_token' => '17171717-1717-4717-8717-171717171717',
            'started_at' => now()->subMinutes(6),
            'last_seen_at' => now()->subMinutes(4),
            'current_path' => '/watch-products',
            'metadata' => [
                'visibility_state' => 'hidden',
                'hidden_at' => now()->subMinutes(4)->toIso8601String(),
            ],
        ]);

        $this->assertCount(0, app(VisitorTrackingService::class)->activeVisitors());

        $response = $this->postJson('/visitor-monitor/heartbeat', [
            'visitor_key' => $profile->visitor_key,
            'session_token' => $session->session_token,
            'page_url' => 'https://swissmadecorp.test/watch-products',
            'page_path' => '/watch-products',
            'page_title' => 'Watch Products',
            'visibility_state' => 'visible',
        ]);

        $response->assertOk()
            ->assertJson([
                'session_token' => $session->session_token,
                'visit_count' => 1,
            ]);

        $session->refresh();
        $summary = app(VisitorTrackingService::class)->sessionSummary($session, true);

        $this->assertNull($session->ended_at);
        $this->assertSame('visible', data_get($session->metadata, 'visibility_state'));
        $this->assertGreaterThanOrEqual(360, $summary['seconds_on_site']);
    }

    public function test_purge_expired_data_removes_visitors_older_than_a_week(): void
    {
        config(['visitor-monitor.retention_days' => 7]);

        $oldEndedProfile = VisitorProfile::query()->create([
            'visitor_key' => 'abababab-abab-4bab-8bab-abababababab',
            'visit_count' => 1,
            'first_seen_at' => now()->subDays(12),
            'last_seen_at' => now()->subDays(8),
        ]);

        $oldAbandonedProfile = VisitorProfile::query()->create([
            'visitor_key' => 'cdcdcdcd-cdcd-4dcd-8dcd-cdcdcdcdcdcd',
            'visit_count' => 1,
            'first_seen_at' => now()->subDays(11),
            'last_seen_at' => now()->subDays(8),
        ]);

        $recentProfile = VisitorProfile::query()->create([
            'visitor_key' => 'efefefef-efef-4fef-8fef-efefefefefef',
            'visit_count' => 2,
            'first_seen_at' => now()->subDays(3),
            'last_seen_at' => now()->subDays(1),
        ]);

        $oldOrphanProfile = VisitorProfile::query()->create([
            'visitor_key' => '01010101-0101-4101-8101-010101010101',
            'visit_count' => 1,
            'first_seen_at' => now()->subDays(10),
        ]);

        $recentOrphanProfile = VisitorProfile::query()->create([
            'visitor_key' => '02020202-0202-4202-8202-020202020202',
            'visit_count' => 1,
            'first_seen_at' => now()->subDays(2),
        ]);

        $oldEndedSession = VisitorSession::query()->create([
            'visitor_profile_id' => $oldEndedProfile->id,
            'session_token' => '12121212-1212-4212-8212-121212121212',
            'started_at' => now()->subDays(9),
            'last_seen_at' => now()->subDays(8),
            'ended_at' => now()->subDays(8),
            'current_path' => '/old-ended-visit',
        ]);

        $oldAbandonedSession = VisitorSession::query()->create([
            'visitor_profile_id' => $oldAbandonedProfile->id,
            'session_token' => '13131313-1313-4313-8313-131313131313',
            'started_at' => now()->subDays(8),
            'last_seen_at' => now()->subDays(8),
            'current_path' => '/old-abandoned-visit',
        ]);

        $recentSession = VisitorSession::query()->create([
            'visitor_profile_id' => $recentProfile->id,
            'session_token' => '14141414-1414-4414-8414-141414141414',
            'started_at' => now()->subDays(2),
            'last_seen_at' => now()->subDays(1),
            'ended_at' => now()->subDay(),
            'current_path' => '/recent-visit',
        ]);

        $purged = app(VisitorTrackingService::class)->purgeExpiredData();

        $this->assertSame(2, $purged['sessions_deleted']);
        $this->assertSame(3, $purged['profiles_deleted']);

        $this->assertDatabaseMissing('visitor_sessions', [
            'id' => $oldEndedSession->id,
        ]);

        $this->assertDatabaseMissing('visitor_sessions', [
            'id' => $oldAbandonedSession->id,
        ]);

        $this->assertDatabaseHas('visitor_sessions', [
            'id' => $recentSession->id,
        ]);

        $this->assertDatabaseMissing('visitor_profiles', [
            'id' => $oldEndedProfile->id,
        ]);

        $this->assertDatabaseMissing('visitor_profiles', [
            'id' => $oldAbandonedProfile->id,
        ]);

        $this->assertDatabaseMissing('visitor_profiles', [
            'id' => $oldOrphanProfile->id,
        ]);

        $this->assertDatabaseHas('visitor_profiles', [
            'id' => $recentProfile->id,
        ]);

        $this->assertDatabaseHas('visitor_profiles', [
            'id' => $recentOrphanProfile->id,
        ]);
    }
}
