<?php

namespace App\Http\Controllers\Chat;

use App\Events\CustomerChatUpdated;
use App\Http\Controllers\Controller;
use App\Models\ChatAutoResponse;
use App\Models\CustomerChat;
use App\Services\ChatPresenceService;
use App\Services\CustomerChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StaffChatController extends Controller
{
    public function heartbeat(Request $request, ChatPresenceService $presenceService): JsonResponse
    {
        $this->ensureChatReady();

        $available = $presenceService->heartbeat($request->user());

        return response()->json([
            'ok' => true,
            'available' => $available,
            'ttl' => $presenceService->ttlSeconds(),
        ]);
    }

    public function index(Request $request, CustomerChatService $chatService): JsonResponse
    {
        $user = $this->ensureChatReady();
        $chatService->touchStaffPresence($user);

        $chats = $chatService->staffChats($user)
            ->map(fn (CustomerChat $chat) => $chatService->chatSummary($chat))
            ->values();

        return response()->json([
            'chats' => $chats,
            'quick_replies' => $chatService->quickReplies(),
            'current_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'chat_available' => $chatService->touchStaffPresence($user),
            ],
        ]);
    }

    public function setAvailability(
        Request $request,
        ChatPresenceService $presenceService,
        CustomerChatService $chatService,
    ): JsonResponse
    {
        $user = $this->ensureChatReady();

        $validated = $request->validate([
            'available' => ['required', 'boolean'],
        ]);

        $available = $presenceService->setAvailability($user, $validated['available']);
        $availabilityPayload = $chatService->availablePayload();

        try {
            broadcast(new CustomerChatUpdated(
                publicChannels: ['customer-chat.availability'],
                privateChannels: ['staff-chat.available', 'staff-chat.user.' . $user->id],
                payload: [
                    'type' => 'availability.updated',
                    'user_id' => $user->id,
                    'available' => $available,
                    'requested_available' => (bool) $validated['available'],
                    'availability' => $availabilityPayload,
                ],
            ));
        } catch (\Throwable $exception) {
            Log::warning('Customer chat availability broadcast failed.', [
                'user_id' => $user->id,
                'requested_available' => (bool) $validated['available'],
                'message' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'available' => $available,
            'availability' => $availabilityPayload,
        ]);
    }

    public function show(CustomerChat $chat, CustomerChatService $chatService): JsonResponse
    {
        $user = $this->ensureChatReady();
        $chatService->touchStaffPresence($user);

        if ($chat->assigned_user_id && $chat->assigned_user_id !== $user->id && $chat->status !== CustomerChat::STATUS_WAITING) {
            abort(403);
        }

        $chat = $chat->fresh(['assignedUser:id,name', 'messages.user:id,name']);

        return response()->json($chatService->chatPayload($chat));
    }

    public function claim(CustomerChat $chat, Request $request, CustomerChatService $chatService): JsonResponse
    {
        $user = $this->ensureChatReady();
        $chatService->touchStaffPresence($user);
        $chat = $chatService->claimChat($chat, $user);

        return response()->json($chatService->chatPayload($chat));
    }

    public function sendMessage(CustomerChat $chat, Request $request, CustomerChatService $chatService): JsonResponse
    {
        $user = $this->ensureChatReady();
        $chatService->touchStaffPresence($user);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120', 'required_without:message'],
        ]);

        $attachment = $request->hasFile('attachment')
            ? $this->storeAttachment($request->file('attachment'))
            : null;
        $message = $chatService->sendStaffMessageWithAttachment(
            $chat,
            $user,
            $validated['message'] ?? null,
            $attachment,
        );

        return response()->json([
            'message' => $chatService->messageData($message),
            'chat' => $chatService->chatSummary($chat->fresh(['assignedUser:id,name', 'messages.user:id,name'])),
        ]);
    }

    public function typing(CustomerChat $chat, Request $request, CustomerChatService $chatService): JsonResponse
    {
        $user = $this->ensureChatReady();
        $chatService->touchStaffPresence($user);

        $validated = $request->validate([
            'typing' => ['required', 'boolean'],
        ]);

        return response()->json([
            'ok' => true,
            'typing' => $chatService->setStaffTyping($chat->fresh(['assignedUser:id,name']), $user, $validated['typing']),
        ]);
    }

    public function storeQuickReply(Request $request, CustomerChatService $chatService): JsonResponse
    {
        $user = $this->ensureChatReady();
        $chatService->touchStaffPresence($user);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $reply = $chatService->saveQuickReply(
            trim($validated['label']),
            trim($validated['message']),
        );

        return response()->json([
            'reply' => [
                'id' => $reply->id,
                'label' => $reply->label,
                'message' => $reply->message,
            ],
            'quick_replies' => $chatService->quickReplies(),
        ]);
    }

    public function deleteQuickReply(ChatAutoResponse $reply, Request $request, CustomerChatService $chatService): JsonResponse
    {
        $user = $this->ensureChatReady();
        $chatService->touchStaffPresence($user);
        $chatService->deleteQuickReply($reply->id);

        return response()->json([
            'deleted' => true,
            'quick_replies' => $chatService->quickReplies(),
        ]);
    }

    private function storeAttachment(UploadedFile $file): array
    {
        $directory = public_path('uploads/chat');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize();
        $filename = Str::uuid() . '-' . preg_replace('/[^A-Za-z0-9.\-_]/', '-', $originalName);
        $file->move($directory, $filename);

        return [
            'path' => 'uploads/chat/' . $filename,
            'name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
        ];
    }

    private function ensureChatReady()
    {
        $user = auth()->user();

        abort_unless($user && $user->is_chat_ready, 403);

        return $user;
    }
}
