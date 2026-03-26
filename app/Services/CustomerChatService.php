<?php

namespace App\Services;

use App\Events\CustomerChatUpdated;
use App\Models\ChatAutoResponse;
use App\Models\CustomerChat;
use App\Models\CustomerChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CustomerChatService
{
    private const TYPING_CUSTOMER = 'customer';
    private const TYPING_STAFF = 'staff';

    public function __construct(
        private ChatPresenceService $presenceService,
    ) {}

    public function availablePayload(): array
    {
        $availableAgents = $this->presenceService->availableCount();

        return [
            'available' => $availableAgents > 0,
            'available_agents' => $availableAgents,
            'offline_prompt' => $this->resolveAutoResponse(
                'offline_prompt',
                'All chat specialists are currently unavailable. Leave your email and we will reach out as soon as possible.',
            ),
        ];
    }

    public function startChat(?string $visitorName, ?string $visitorEmail, string $message, ?array $pageContext = null): ?CustomerChat
    {
        if ($this->presenceService->availableCount() === 0) {
            return null;
        }

        $chat = DB::transaction(function () use ($visitorName, $visitorEmail, $message, $pageContext) {
            $chat = CustomerChat::create([
                'public_token' => (string) Str::uuid(),
                'status' => CustomerChat::STATUS_WAITING,
                'visitor_name' => $visitorName,
                'visitor_email' => $visitorEmail,
                'last_message_at' => now(),
                'last_customer_message_at' => now(),
                'customer_last_seen_at' => now(),
                'metadata' => $this->metadataWithPageContext([], $pageContext),
            ]);

            $customerMessage = $chat->messages()->create([
                'sender_type' => CustomerChatMessage::SENDER_CUSTOMER,
                'message' => $message,
            ]);

            $autoResponse = $chat->messages()->create([
                'sender_type' => CustomerChatMessage::SENDER_SYSTEM,
                'message' => $this->resolveAutoResponse(
                    'online_greeting',
                    'Thanks for reaching out. A watch specialist will join this chat shortly.',
                ),
                'is_auto_response' => true,
            ]);

            return $chat->fresh(['assignedUser:id,name,is_chat_ready', 'messages.user:id,name']);
        });

        $this->broadcast(
            privateChannels: $this->queueNotificationChannels(),
            payload: [
                'type' => 'chat.created',
                'chat' => $this->chatSummary($chat),
                'messages' => $chat->messages->map(fn (CustomerChatMessage $item) => $this->messageData($item))->values()->all(),
            ],
        );

        return $chat;
    }

    public function leaveEmail(?string $visitorName, string $visitorEmail, ?string $message, ?array $pageContext = null): CustomerChat
    {
        $chat = DB::transaction(function () use ($visitorName, $visitorEmail, $message, $pageContext) {
            $chat = CustomerChat::create([
                'public_token' => (string) Str::uuid(),
                'status' => CustomerChat::STATUS_OFFLINE,
                'visitor_name' => $visitorName,
                'visitor_email' => $visitorEmail,
                'last_message_at' => now(),
                'last_customer_message_at' => now(),
                'customer_last_seen_at' => now(),
                'metadata' => $this->metadataWithPageContext([], $pageContext),
            ]);

            if ($message) {
                $chat->messages()->create([
                    'sender_type' => CustomerChatMessage::SENDER_CUSTOMER,
                    'message' => $message,
                ]);
            }

            $chat->messages()->create([
                'sender_type' => CustomerChatMessage::SENDER_SYSTEM,
                'message' => $this->resolveAutoResponse(
                    'offline_thanks',
                    'Thank you. Your message has been saved and we will follow up by email.',
                ),
                'is_auto_response' => true,
            ]);

            return $chat->fresh(['assignedUser:id,name,is_chat_ready', 'messages.user:id,name']);
        });

        $this->broadcast(
            privateChannels: $this->queueNotificationChannels(),
            payload: [
                'type' => 'chat.offline',
                'chat' => $this->chatSummary($chat),
                'messages' => $chat->messages->map(fn (CustomerChatMessage $item) => $this->messageData($item))->values()->all(),
            ],
        );

        return $chat;
    }

    public function convertChatToOfflineLead(CustomerChat $chat, ?string $visitorName, string $visitorEmail, ?string $message = null): CustomerChat
    {
        $chat = DB::transaction(function () use ($chat, $visitorName, $visitorEmail, $message) {
            $locked = CustomerChat::query()->lockForUpdate()->findOrFail($chat->id);
            $now = now();

            $locked->forceFill([
                'assigned_user_id' => null,
                'assigned_at' => null,
                'status' => CustomerChat::STATUS_OFFLINE,
                'visitor_name' => $visitorName ?: $locked->visitor_name,
                'visitor_email' => $visitorEmail,
                'last_message_at' => $now,
                'last_customer_message_at' => $now,
                'customer_last_seen_at' => $now,
            ])->save();

            if ($message) {
                $locked->messages()->create([
                    'sender_type' => CustomerChatMessage::SENDER_CUSTOMER,
                    'message' => $message,
                ]);
            }

            $locked->messages()->create([
                'sender_type' => CustomerChatMessage::SENDER_SYSTEM,
                'message' => $this->resolveAutoResponse(
                    'offline_thanks',
                    'Thank you. Your message has been saved and we will follow up by email.',
                ),
                'is_auto_response' => true,
            ]);

            return $locked->fresh(['assignedUser:id,name,is_chat_ready', 'messages.user:id,name']);
        });

        $this->broadcast(
            privateChannels: $this->queueNotificationChannels(),
            payload: [
                'type' => 'chat.offline',
                'chat' => $this->chatSummary($chat),
                'messages' => $chat->messages->map(fn (CustomerChatMessage $item) => $this->messageData($item))->values()->all(),
            ],
        );

        return $chat;
    }

    public function findByPublicToken(string $token): CustomerChat
    {
        return $this->baseChatQuery()
            ->where('public_token', $token)
            ->firstOrFail();
    }

    public function sendCustomerMessage(CustomerChat $chat, string $message): CustomerChatMessage
    {
        return $this->createCustomerMessage($chat, $message);
    }

    public function sendCustomerMessageWithAttachment(CustomerChat $chat, ?string $message = null, ?array $attachment = null, ?array $pageContext = null): CustomerChatMessage
    {
        return $this->createCustomerMessage($chat, $message, $attachment, $pageContext);
    }

    public function customerCanSend(CustomerChat $chat): bool
    {
        if ($chat->status === CustomerChat::STATUS_OFFLINE) {
            return false;
        }

        return $this->chatHasLiveCoverage($chat);
    }

    private function createCustomerMessage(CustomerChat $chat, ?string $message = null, ?array $attachment = null, ?array $pageContext = null): CustomerChatMessage
    {
        $created = DB::transaction(function () use ($chat, $message, $attachment, $pageContext) {
            $created = $chat->messages()->create([
                'sender_type' => CustomerChatMessage::SENDER_CUSTOMER,
                'message' => $message ?? '',
                'attachment_path' => $attachment['path'] ?? null,
                'attachment_name' => $attachment['name'] ?? null,
                'attachment_mime_type' => $attachment['mime_type'] ?? null,
                'attachment_size' => $attachment['size'] ?? null,
            ]);

            $chat->forceFill([
                'last_message_at' => $created->created_at,
                'last_customer_message_at' => $created->created_at,
                'customer_last_seen_at' => now(),
                'metadata' => $this->metadataWithPageContext($chat->metadata, $pageContext),
            ])->save();

            return $created->fresh(['user:id,name']);
        });

        $chat = $chat->fresh(['assignedUser:id,name,is_chat_ready']);
        $this->setTypingState($chat, self::TYPING_CUSTOMER, false);
        $privateChannels = $this->staffAudienceChannelsForChat($chat);
        $messageType = $chat->assigned_user_id
            && $chat->assignedUser
            && $this->presenceService->isAvailable($chat->assignedUser)
                ? 'chat.message'
                : 'chat.waiting.message';

        $this->broadcast(
            publicChannels: ['customer-chat.' . $chat->public_token],
            privateChannels: $privateChannels,
            payload: [
                'type' => $messageType,
                'chat' => $this->chatSummary($chat),
                'message' => $this->messageData($created),
            ],
        );

        return $created;
    }

    public function claimChat(CustomerChat $chat, User $user): CustomerChat
    {
        if (! $user->is_chat_ready) {
            abort(403);
        }

        if (! $this->presenceService->isAvailable($user)) {
            throw new ConflictHttpException('You are currently unavailable. Switch to Available before joining this chat.');
        }

        $chat = DB::transaction(function () use ($chat, $user) {
            $locked = CustomerChat::query()->lockForUpdate()->findOrFail($chat->id);
            $wasTakeover = false;

            if ($locked->assigned_user_id && $locked->assigned_user_id !== $user->id) {
                $locked->loadMissing('assignedUser:id,name,is_chat_ready');

                if (! $this->chatCanBeTakenOver($locked)) {
                    throw new ConflictHttpException('This chat was already assigned to another user.');
                }

                $wasTakeover = true;
            }

            if (! $locked->assigned_user_id || $wasTakeover) {
                $locked->forceFill([
                    'assigned_user_id' => $user->id,
                    'assigned_at' => now(),
                    'staff_last_seen_at' => now(),
                    'status' => CustomerChat::STATUS_ACTIVE,
                ])->save();

                $locked->messages()->create([
                    'sender_type' => CustomerChatMessage::SENDER_SYSTEM,
                    'message' => $wasTakeover
                        ? "{$user->name} took over the chat."
                        : "{$user->name} joined the chat.",
                ]);
            } else {
                $locked->forceFill([
                    'staff_last_seen_at' => now(),
                ])->save();
            }

            return $locked->fresh(['assignedUser:id,name,is_chat_ready', 'messages.user:id,name']);
        });

        $systemMessage = $chat->messages->last();

        $this->broadcast(
            publicChannels: ['customer-chat.' . $chat->public_token],
            privateChannels: $this->queueNotificationChannels(),
            payload: [
                'type' => 'chat.claimed',
                'chat' => $this->chatSummary($chat),
                'message' => $systemMessage ? $this->messageData($systemMessage) : null,
            ],
        );

        return $chat;
    }

    public function sendStaffMessage(CustomerChat $chat, User $user, string $message): CustomerChatMessage
    {
        return $this->createStaffMessage($chat, $user, $message);
    }

    public function sendStaffMessageWithAttachment(CustomerChat $chat, User $user, ?string $message = null, ?array $attachment = null): CustomerChatMessage
    {
        return $this->createStaffMessage($chat, $user, $message, $attachment);
    }

    private function createStaffMessage(CustomerChat $chat, User $user, ?string $message = null, ?array $attachment = null): CustomerChatMessage
    {
        if (
            ! $chat->assigned_user_id
            || ($chat->assigned_user_id !== $user->id && $this->chatCanBeTakenOver($chat))
        ) {
            $chat = $this->claimChat($chat, $user);
        }

        if ($chat->assigned_user_id !== $user->id) {
            throw new ConflictHttpException('This chat is assigned to another user.');
        }

        $created = DB::transaction(function () use ($chat, $user, $message, $attachment) {
            $created = $chat->messages()->create([
                'sender_type' => CustomerChatMessage::SENDER_STAFF,
                'user_id' => $user->id,
                'message' => $message ?? '',
                'attachment_path' => $attachment['path'] ?? null,
                'attachment_name' => $attachment['name'] ?? null,
                'attachment_mime_type' => $attachment['mime_type'] ?? null,
                'attachment_size' => $attachment['size'] ?? null,
            ]);

            $chat->forceFill([
                'status' => CustomerChat::STATUS_ACTIVE,
                'last_message_at' => $created->created_at,
                'last_staff_message_at' => $created->created_at,
                'staff_last_seen_at' => now(),
            ])->save();

            return $created->fresh(['user:id,name']);
        });

        $chat = $chat->fresh(['assignedUser:id,name,is_chat_ready']);
        $this->setTypingState($chat, self::TYPING_STAFF, false, $user);

        $this->broadcast(
            publicChannels: ['customer-chat.' . $chat->public_token],
            privateChannels: ['staff-chat.user.' . $user->id],
            payload: [
                'type' => 'chat.message',
                'chat' => $this->chatSummary($chat),
                'message' => $this->messageData($created),
            ],
        );

        return $created;
    }

    public function staffChats(User $user): EloquentCollection
    {
        return $this->baseChatQuery()
            ->where(function (Builder $query) use ($user) {
                $query->whereIn('status', [CustomerChat::STATUS_WAITING, CustomerChat::STATUS_OFFLINE])
                    ->orWhere('assigned_user_id', $user->id)
                    ->orWhere(function (Builder $takeoverQuery) use ($user) {
                        $takeoverQuery
                            ->where('status', CustomerChat::STATUS_ACTIVE)
                            ->whereNotNull('assigned_user_id')
                            ->where('assigned_user_id', '!=', $user->id);
                    });
            })
            ->orderByRaw("case when status = 'waiting' then 0 when status = 'offline' then 1 else 2 end")
            ->orderByDesc('last_message_at')
            ->get()
            ->filter(fn (CustomerChat $chat) => $this->canUserViewChat($user, $chat))
            ->values();
    }

    public function chatSummary(CustomerChat $chat): array
    {
        $previewMessage = $chat->messages
            ->reverse()
            ->first(fn (CustomerChatMessage $message) => $message->sender_type !== CustomerChatMessage::SENDER_SYSTEM)
            ?? $chat->messages->last();
        $lastCustomerMessageAt = $chat->last_customer_message_at;
        $lastStaffMessageAt = $chat->last_staff_message_at;
        $isNewRequest = $chat->status === CustomerChat::STATUS_WAITING && ! $chat->assigned_user_id;
        $needsStaffReply = $chat->status !== CustomerChat::STATUS_OFFLINE
            && $lastCustomerMessageAt !== null
            && ($lastStaffMessageAt === null || $lastCustomerMessageAt->gt($lastStaffMessageAt));
        $attentionState = $isNewRequest
            ? 'new'
            : ($needsStaffReply ? 'reply_needed' : 'ongoing');
        $pageContext = $this->pageContextFromChat($chat);
        $assignedUserAvailable = $chat->assignedUser
            ? $this->presenceService->isAvailable($chat->assignedUser)
            : null;
        $liveChatAvailable = $this->chatHasLiveCoverage($chat);

        return [
            'id' => $chat->id,
            'public_token' => $chat->public_token,
            'status' => $chat->status,
            'visitor_name' => $chat->visitor_name,
            'visitor_email' => $chat->visitor_email,
            'customer_last_seen_at' => optional($chat->customer_last_seen_at)->toIso8601String(),
            'customer_is_online' => $chat->status !== CustomerChat::STATUS_OFFLINE
                && optional($chat->customer_last_seen_at)?->gt(now()->subMinutes(2)),
            'assigned_user' => $chat->assignedUser
                ? ['id' => $chat->assignedUser->id, 'name' => $chat->assignedUser->name]
                : null,
            'assigned_user_available' => $assignedUserAvailable,
            'live_chat_available' => $liveChatAvailable,
            'can_be_claimed' => $chat->status !== CustomerChat::STATUS_OFFLINE
                && (! $chat->assigned_user_id || ! $assignedUserAvailable),
            'last_message_at' => optional($chat->last_message_at)->toIso8601String(),
            'last_customer_message_at' => optional($lastCustomerMessageAt)->toIso8601String(),
            'last_staff_message_at' => optional($lastStaffMessageAt)->toIso8601String(),
            'last_message_preview' => $previewMessage?->message,
            'is_new_request' => $isNewRequest,
            'needs_staff_reply' => $needsStaffReply,
            'attention_state' => $attentionState,
            'page_context' => $pageContext,
        ];
    }

    public function touchCustomerActivity(CustomerChat $chat, ?array $pageContext = null): CustomerChat
    {
        $chat->forceFill([
            'customer_last_seen_at' => now(),
            'metadata' => $this->metadataWithPageContext($chat->metadata, $pageContext),
        ])->save();

        return $chat->fresh(['assignedUser:id,name,is_chat_ready', 'messages.user:id,name']);
    }

    public function touchStaffPresence(User $user): bool
    {
        return $this->presenceService->heartbeat($user);
    }

    public function setCustomerTyping(CustomerChat $chat, bool $isTyping): array
    {
        return $this->setTypingState($chat, self::TYPING_CUSTOMER, $isTyping);
    }

    public function setStaffTyping(CustomerChat $chat, User $user, bool $isTyping): array
    {
        if (! $this->canUserViewChat($user, $chat)) {
            throw new ConflictHttpException('This chat is assigned to another user.');
        }

        if ($chat->status === CustomerChat::STATUS_OFFLINE) {
            return $this->typingState($chat);
        }

        return $this->setTypingState($chat, self::TYPING_STAFF, $isTyping, $user);
    }

    public function typingState(CustomerChat $chat): array
    {
        $customer = Cache::get($this->typingCacheKey($chat->id, self::TYPING_CUSTOMER), []);
        $staff = Cache::get($this->typingCacheKey($chat->id, self::TYPING_STAFF), []);
        $staffName = $chat->assignedUser?->name ?? ($staff['user_name'] ?? 'A specialist');

        return [
            'customer' => [
                'is_typing' => (bool) ($customer['is_typing'] ?? false),
                'label' => 'Customer is typing...',
            ],
            'staff' => [
                'is_typing' => (bool) ($staff['is_typing'] ?? false),
                'label' => trim($staffName . ' is typing...'),
            ],
        ];
    }

    public function chatPayload(CustomerChat $chat): array
    {
        return [
            'chat' => $this->chatSummary($chat),
            'messages' => $chat->messages->map(fn (CustomerChatMessage $item) => $this->messageData($item))->values()->all(),
            'typing' => $this->typingState($chat),
        ];
    }

    public function messageData(CustomerChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'user_id' => $message->user_id,
            'user_name' => $message->user?->name,
            'message' => $message->message,
            'is_auto_response' => $message->is_auto_response,
            'attachment' => $message->attachment_path ? [
                'url' => '/' . ltrim($message->attachment_path, '/'),
                'name' => $message->attachment_name,
                'mime_type' => $message->attachment_mime_type,
                'size' => $message->attachment_size,
                'is_image' => str_starts_with((string) $message->attachment_mime_type, 'image/'),
            ] : null,
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }

    public function quickReplies(): array
    {
        $this->ensureDefaultQuickReplies();

        return ChatAutoResponse::query()
            ->where('is_active', true)
            ->where('key', 'like', 'quick_reply:%')
            ->orderBy('label')
            ->get(['id', 'label', 'message'])
            ->map(fn (ChatAutoResponse $item) => [
                'id' => $item->id,
                'label' => $item->label,
                'message' => $item->message,
            ])
            ->values()
            ->all();
    }

    private function ensureDefaultQuickReplies(): void
    {
        $existingCount = ChatAutoResponse::query()
            ->where('key', 'like', 'quick_reply:%')
            ->count();

        if ($existingCount > 0) {
            return;
        }

        foreach ([
            [
                'label' => 'Checking that',
                'message' => 'Hmm... let me see.',
            ],
            [
                'label' => 'Anything else',
                'message' => 'Is there anything else you would like to know?',
            ],
        ] as $reply) {
            ChatAutoResponse::query()->create([
                'key' => 'quick_reply:' . Str::slug($reply['label']) . ':' . Str::lower(Str::random(6)),
                'label' => $reply['label'],
                'message' => $reply['message'],
                'is_active' => true,
            ]);
        }
    }

    public function saveQuickReply(string $label, string $message): ChatAutoResponse
    {
        return ChatAutoResponse::query()->create([
            'key' => 'quick_reply:' . Str::slug($label) . ':' . Str::lower(Str::random(6)),
            'label' => $label,
            'message' => $message,
            'is_active' => true,
        ]);
    }

    public function deleteQuickReply(int $replyId): void
    {
        ChatAutoResponse::query()
            ->where('key', 'like', 'quick_reply:%')
            ->whereKey($replyId)
            ->delete();
    }

    private function resolveAutoResponse(string $key, string $fallback): string
    {
        return ChatAutoResponse::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->value('message') ?? $fallback;
    }

    private function baseChatQuery(): Builder
    {
        return CustomerChat::query()
            ->with([
                'assignedUser:id,name,is_chat_ready',
                'messages' => fn ($query) => $query->with('user:id,name')->orderBy('id'),
            ]);
    }

    private function setTypingState(CustomerChat $chat, string $participant, bool $isTyping, ?User $user = null): array
    {
        $cacheKey = $this->typingCacheKey($chat->id, $participant);

        if ($isTyping) {
            Cache::put($cacheKey, [
                'is_typing' => true,
                'user_name' => $user?->name,
            ], now()->addSeconds($this->typingTtlSeconds()));
        } else {
            Cache::forget($cacheKey);
        }

        $chat = $chat->fresh(['assignedUser:id,name,is_chat_ready']);
        $typing = $this->typingState($chat);

        $payload = [
            'type' => 'typing.updated',
            'chat' => $this->chatSummary($chat),
            'typing' => $typing,
        ];

        if ($participant === self::TYPING_CUSTOMER) {
            $privateChannels = $this->staffAudienceChannelsForChat($chat);

            $this->broadcast(
                publicChannels: ['customer-chat.' . $chat->public_token],
                privateChannels: $privateChannels,
                payload: $payload,
            );

            return $typing;
        }

        $privateChannels = $chat->assigned_user_id
            ? ['staff-chat.user.' . $chat->assigned_user_id]
            : ['staff-chat.available'];

        $this->broadcast(
            publicChannels: ['customer-chat.' . $chat->public_token],
            privateChannels: $privateChannels,
            payload: $payload,
        );

        return $typing;
    }

    private function typingCacheKey(int $chatId, string $participant): string
    {
        return "chat:typing:{$chatId}:{$participant}";
    }

    private function queueNotificationChannels(): array
    {
        $channels = ['staff-chat.available'];

        $userChannels = User::query()
            ->where('is_chat_ready', 1)
            ->pluck('id')
            ->map(fn ($id) => 'staff-chat.user.' . $id)
            ->all();

        return array_values(array_unique(array_merge($channels, $userChannels)));
    }

    public function canUserViewChat(User $user, CustomerChat $chat): bool
    {
        if (in_array($chat->status, [CustomerChat::STATUS_WAITING, CustomerChat::STATUS_OFFLINE], true)) {
            return true;
        }

        if (! $chat->assigned_user_id || $chat->assigned_user_id === $user->id) {
            return true;
        }

        return $this->chatCanBeTakenOver($chat);
    }

    private function chatHasLiveCoverage(CustomerChat $chat): bool
    {
        if ($chat->assigned_user_id) {
            $chat->loadMissing('assignedUser:id,name,is_chat_ready');
        }

        if ($chat->status === CustomerChat::STATUS_OFFLINE) {
            return false;
        }

        if (! $chat->assignedUser) {
            return $this->presenceService->availableCount() > 0;
        }

        if ($this->presenceService->isAvailable($chat->assignedUser)) {
            return true;
        }

        return $this->hasAvailableBackupSpecialist($chat->assignedUser->id);
    }

    private function chatCanBeTakenOver(CustomerChat $chat): bool
    {
        if ($chat->assigned_user_id) {
            $chat->loadMissing('assignedUser:id,name,is_chat_ready');
        }

        if (! $chat->assignedUser || $chat->status === CustomerChat::STATUS_OFFLINE) {
            return false;
        }

        return ! $this->presenceService->isAvailable($chat->assignedUser);
    }

    private function hasAvailableBackupSpecialist(?int $exceptUserId = null): bool
    {
        return $this->presenceService->availableUsers()
            ->contains(fn (User $user) => $user->id !== $exceptUserId);
    }

    private function staffAudienceChannelsForChat(CustomerChat $chat): array
    {
        if ($chat->assigned_user_id) {
            $chat->loadMissing('assignedUser:id,name,is_chat_ready');
        }

        if (
            $chat->assigned_user_id
            && $chat->assignedUser
            && $this->presenceService->isAvailable($chat->assignedUser)
        ) {
            return ['staff-chat.user.' . $chat->assigned_user_id];
        }

        return $this->queueNotificationChannels();
    }

    private function typingTtlSeconds(): int
    {
        return (int) config('chat.typing_ttl_seconds', 6);
    }

    private function pageContextFromChat(CustomerChat $chat): ?array
    {
        return $this->normalizePageContext($chat->metadata['page_context'] ?? null);
    }

    private function metadataWithPageContext(?array $metadata, ?array $pageContext): ?array
    {
        $metadata = is_array($metadata) ? $metadata : [];
        $normalizedPageContext = $this->normalizePageContext($pageContext);

        if (! $normalizedPageContext) {
            return $metadata ?: null;
        }

        $metadata['page_context'] = $normalizedPageContext;
        $metadata['page_context_updated_at'] = now()->toIso8601String();

        return $metadata;
    }

    private function normalizePageContext(?array $pageContext): ?array
    {
        if (! is_array($pageContext)) {
            return null;
        }

        $pageUrl = $this->cleanContextString($pageContext['page_url'] ?? $pageContext['url'] ?? null, 2048);
        $pagePath = $this->cleanContextString($pageContext['page_path'] ?? $pageContext['path'] ?? null, 2048);
        $pageTitle = $this->cleanContextString($pageContext['page_title'] ?? $pageContext['title'] ?? null, 255);
        $pageType = $this->cleanContextString($pageContext['page_type'] ?? $pageContext['type'] ?? null, 80);
        $productId = $pageContext['product_id'] ?? $pageContext['product']['id'] ?? null;
        $productTitle = $this->cleanContextString($pageContext['product_title'] ?? $pageContext['product']['title'] ?? null, 255);

        if (! $pagePath && $pageUrl) {
            $parsedPath = parse_url($pageUrl, PHP_URL_PATH);

            if (is_string($parsedPath) && $parsedPath !== '') {
                $pagePath = $parsedPath;
            }
        }

        $normalized = array_filter([
            'page_url' => $pageUrl,
            'page_path' => $pagePath,
            'page_title' => $pageTitle,
            'page_type' => $pageType,
        ], fn ($value) => $value !== null && $value !== '');

        $normalizedProductId = is_numeric($productId) && (int) $productId > 0 ? (int) $productId : null;

        if ($normalizedProductId || $productTitle) {
            $normalized['product'] = array_filter([
                'id' => $normalizedProductId,
                'title' => $productTitle,
            ], fn ($value) => $value !== null && $value !== '');
        }

        return $normalized ?: null;
    }

    private function cleanContextString(mixed $value, int $maxLength): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $cleaned = trim((string) $value);

        if ($cleaned === '') {
            return null;
        }

        return Str::limit($cleaned, $maxLength, '');
    }

    private function broadcast(array $publicChannels = [], array $privateChannels = [], array $payload = []): void
    {
        try {
            event(new CustomerChatUpdated(
                publicChannels: $publicChannels,
                privateChannels: $privateChannels,
                payload: $payload,
            ));
        } catch (\Throwable $exception) {
            Log::warning('Customer chat broadcast failed.', [
                'message' => $exception->getMessage(),
                'public_channels' => $publicChannels,
                'private_channels' => $privateChannels,
            ]);
        }
    }
}
