<?php

namespace App\Http\Controllers;

use App\Services\ProductActivityMonitorService;
use Illuminate\Http\JsonResponse;

class ProductActivityHistoryController extends Controller
{
    public function __invoke(ProductActivityMonitorService $activityService, int $productId): JsonResponse
    {
        $history = $activityService->productHistory($productId);

        if (! $history) {
            return response()->json([
                'message' => 'Product history was not found.',
            ], 404);
        }

        return response()->json($history);
    }
}
