<?php

namespace App\Http\Controllers;

use App\Services\VisitorTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorTrackingController extends Controller
{
    public function heartbeat(Request $request, VisitorTrackingService $trackingService): JsonResponse
    {
        $validated = $request->validate([
            'visitor_key' => ['required', 'uuid'],
            'session_token' => ['required', 'uuid'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'page_path' => ['nullable', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'referrer_url' => ['nullable', 'string', 'max:2048'],
            'visibility_state' => ['nullable', 'in:visible,hidden'],
        ]);

        $session = $trackingService->trackHeartbeat($request, $validated);
        $summary = $trackingService->sessionSummary($session, true);

        return response()->json([
            'ok' => true,
            'visitor_key' => $summary['visitor_key'],
            'session_token' => $summary['session_token'],
            'display_name' => $summary['display_name'],
            'is_returning' => $summary['is_returning'],
            'visit_count' => $summary['visit_count'],
        ]);
    }

    public function leave(Request $request, VisitorTrackingService $trackingService): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'uuid'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'page_path' => ['nullable', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
        ]);

        $trackingService->markVisitorLeft($validated);

        return response()->json(['ok' => true]);
    }
}
