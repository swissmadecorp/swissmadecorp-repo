<?php

namespace App\Services;

use App\Events\ProductActivityUpdated;
use App\Models\Product;
use App\Models\ProductActivityEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductActivityMonitorService
{
    public function touchSession(User $user, string $mode, ?int $productId = null, array $changedFields = []): array
    {
        $payload = [
            'id' => $user->id,
            'user_id' => $user->id,
            'user_name' => $user->name ?? 'Unknown user',
            'mode' => $mode,
            'mode_label' => $mode === 'create' ? 'Creating new item' : 'Updating item',
            'product_id' => $mode === 'update' ? $productId : null,
            'product_title' => null,
            'product_image' => '/images/no-image.jpg',
            'changed_fields' => $mode === 'update'
                ? array_values(array_unique(array_filter($changedFields)))
                : [],
            'last_seen_at' => now()->toIso8601String(),
        ];

        if ($mode === 'update' && $productId) {
            $product = Product::with('images')->find($productId);
            $payload['product_title'] = $product?->title;
            $payload['product_image'] = $this->productImageUrl($product);
        }

        Cache::put($this->sessionKey($user->id), $payload, now()->addMinutes(10));
        $this->storeActiveUserId($user->id);

        event(new ProductActivityUpdated('session', $this->formatSessionPayload($payload)));

        return $payload;
    }

    public function clearSession(?User $user): void
    {
        if (! $user) {
            return;
        }

        Cache::forget($this->sessionKey($user->id));
        $this->forgetActiveUserId($user->id);

        event(new ProductActivityUpdated('session-cleared', [
            'user_id' => $user->id,
        ]));
    }

    public function currentSessionFields(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return Cache::get($this->sessionKey($user->id), [])['changed_fields'] ?? [];
    }

    public function currentSession(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return Cache::get($this->sessionKey($user->id), []);
    }

    public function recordCreated(User $user, Product $product): ProductActivityEvent
    {
        return $this->recordEvent($user, $product, 'created', []);
    }

    public function recordUpdated(User $user, Product $product, array $dirtyColumns = [], array $clientFields = []): ProductActivityEvent
    {
        $changedFields = array_values(array_unique(array_merge(
            $this->mapFieldLabels($dirtyColumns),
            array_filter($clientFields)
        )));

        return $this->recordEvent($user, $product, 'updated', $changedFields);
    }

    public function stats(): array
    {
        $active = $this->readActiveSessions();

        return [
            'active' => $active->count(),
            'creating' => $active->where('mode', 'create')->count(),
            'updating' => $active->where('mode', 'update')->count(),
            'saved_today' => $this->eventTableReady()
                ? ProductActivityEvent::whereDate('created_at', today())->count()
                : 0,
        ];
    }

    public function activeSessions(): Collection
    {
        return $this->readActiveSessions()
            ->map(fn (array $session) => $this->formatSessionPayload($session));
    }

    public function recentEvents(int $limit = 30): Collection
    {
        if (! $this->eventTableReady()) {
            return collect();
        }

        return ProductActivityEvent::query()
            ->with('user:id,name')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ProductActivityEvent $event) => $this->formatEvent($event));
    }

    public function mapFieldLabels(array $fields): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (string $field) => $this->fieldLabel($field),
            $fields
        ))));
    }

    private function recordEvent(User $user, Product $product, string $action, array $changedFields): ProductActivityEvent
    {
        $product->loadMissing('images');

        $event = ProductActivityEvent::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'action' => $action,
            'product_title' => $product->title,
            'product_image' => $this->productImageUrl($product),
            'changed_fields' => $action === 'updated' ? $changedFields : [],
        ]);

        event(new ProductActivityUpdated('event', $this->formatEvent($event->fresh('user'))));

        return $event;
    }

    private function formatSessionPayload(array $session): array
    {
        $lastSeenAt = isset($session['last_seen_at']) ? Carbon::parse($session['last_seen_at']) : null;

        return [
            'id' => $session['id'] ?? $session['user_id'],
            'user_id' => $session['user_id'],
            'user_name' => $session['user_name'] ?? 'Unknown user',
            'mode' => $session['mode'],
            'mode_label' => $session['mode_label'] ?? ($session['mode'] === 'create' ? 'Creating new item' : 'Updating item'),
            'product_id' => $session['product_id'] ?? null,
            'product_title' => $session['product_title'] ?? null,
            'product_image' => $session['product_image'] ?? '/images/no-image.jpg',
            'changed_fields' => $session['changed_fields'] ?? [],
            'last_seen_label' => optional($lastSeenAt)?->diffForHumans() ?? 'Just now',
        ];
    }

    private function formatEvent(ProductActivityEvent $event): array
    {
        return [
            'id' => $event->id,
            'user_id' => $event->user_id,
            'user_name' => $event->user?->name ?? 'Unknown user',
            'action' => $event->action,
            'action_label' => $event->action === 'created' ? 'Created item' : 'Updated item',
            'product_id' => $event->product_id,
            'product_title' => $event->product_title,
            'product_image' => $event->product_image ?: '/images/no-image.jpg',
            'changed_fields' => $event->changed_fields ?? [],
            'created_at_label' => optional($event->created_at)?->diffForHumans() ?? 'Just now',
        ];
    }

    private function productImageUrl(?Product $product): string
    {
        if (! $product || $product->images->isEmpty()) {
            return '/images/no-image.jpg';
        }

        return '/images/thumbs/' . $product->images->first()->location;
    }

    private function fieldLabel(string $field): string
    {
        $map = [
            'title' => 'Title',
            'category_id' => 'Category',
            'p_model' => 'Model Name',
            'p_casesize' => 'Case Size',
            'p_reference' => 'Reference',
            'p_serial' => 'Serial #',
            'p_color' => 'Dial Color',
            'p_gender' => 'Gender',
            'p_strap' => 'Strap/Band',
            'p_clasp' => 'Clasp Type',
            'p_material' => 'Case Material',
            'p_bezelmaterial' => 'Bezel Material',
            'p_condition' => 'Condition',
            'p_price' => 'Cost',
            'p_newprice' => 'Price',
            'web_price' => 'Website Price',
            'p_price3P' => 'Chrono24 Price',
            'p_retail' => 'Retail',
            'p_qty' => 'On Hand',
            'supplier' => 'Supplier Name',
            'supplier_invoice' => 'Supplier Invoice #',
            'p_status' => 'Status',
            'water_resistance' => 'Water Resistance',
            'p_year' => 'Product Year',
            'bezel_features' => 'Bezel Features',
            'movement' => 'Movement',
            'p_dial_style' => 'Dial Style',
            'p_box' => 'Box',
            'p_papers' => 'Papers',
            'p_servicepapers' => 'Service Papers',
            'p_smalldescription' => 'Small Description',
            'p_longdescription' => 'Long Description',
            'p_comments' => 'Comments',
            'slug' => 'Slug',
            'keyword_build' => 'Keyword Build',
            'serial_code' => 'Serial Code',
            'p_additional_cost' => 'Additional Cost',
            'p_additional_cost_notes' => 'Additional Cost Notes',
        ];

        if (array_key_exists($field, $map)) {
            return $map[$field];
        }

        if (Str::startsWith($field, 'c_')) {
            return Str::of($field)
                ->after('c_')
                ->replace('_', ' ')
                ->title()
                ->toString();
        }

        return Str::of($field)
            ->replace('_', ' ')
            ->replace('-', ' ')
            ->title()
            ->toString();
    }

    private function eventTableReady(): bool
    {
        return Schema::hasTable('product_activity_events');
    }

    private function readActiveSessions(): Collection
    {
        $activeUserIds = Cache::get($this->activeUsersKey(), []);
        $sessions = collect();
        $validUserIds = [];

        foreach ($activeUserIds as $userId) {
            $session = Cache::get($this->sessionKey($userId));

            if (! is_array($session) || ! isset($session['last_seen_at'])) {
                continue;
            }

            $lastSeenAt = Carbon::parse($session['last_seen_at']);

            if ($lastSeenAt->lt(now()->subSeconds(45))) {
                Cache::forget($this->sessionKey($userId));
                continue;
            }

            $validUserIds[] = $userId;
            $sessions->push($session);
        }

        Cache::put($this->activeUsersKey(), array_values(array_unique($validUserIds)), now()->addMinutes(10));

        return $sessions->sortByDesc('last_seen_at')->values();
    }

    private function storeActiveUserId(int $userId): void
    {
        $activeUserIds = Cache::get($this->activeUsersKey(), []);
        $activeUserIds[] = $userId;

        Cache::put(
            $this->activeUsersKey(),
            array_values(array_unique($activeUserIds)),
            now()->addMinutes(10)
        );
    }

    private function forgetActiveUserId(int $userId): void
    {
        $activeUserIds = array_values(array_filter(
            Cache::get($this->activeUsersKey(), []),
            fn (int $activeUserId) => $activeUserId !== $userId
        ));

        Cache::put($this->activeUsersKey(), $activeUserIds, now()->addMinutes(10));
    }

    private function sessionKey(int $userId): string
    {
        return "product-activity:session:{$userId}";
    }

    private function activeUsersKey(): string
    {
        return 'product-activity:active-users';
    }
}
