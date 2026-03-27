<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Mail\GMailer;
use App\Models\User;
use App\Services\CustomerChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerChatController extends Controller
{
    public function availability(CustomerChatService $chatService): JsonResponse
    {
        return response()->json($chatService->availablePayload());
    }

    public function store(Request $request, CustomerChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'visitor_name' => ['nullable', 'string', 'max:120'],
            'visitor_email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'page_path' => ['nullable', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'page_type' => ['nullable', 'string', 'max:80'],
            'product_id' => ['nullable', 'integer'],
            'product_title' => ['nullable', 'string', 'max:255'],
        ]);

        $chat = $chatService->startChat(
            visitorName: $validated['visitor_name'] ?? null,
            visitorEmail: $validated['visitor_email'] ?? null,
            message: $validated['message'],
            pageContext: $this->extractPageContext($validated),
        );

        if (! $chat) {
            return response()->json($chatService->availablePayload(), 409);
        }

        return response()->json($chatService->chatPayload($chat));
    }

    public function show(Request $request, string $token, CustomerChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'page_url' => ['nullable', 'string', 'max:2048'],
            'page_path' => ['nullable', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'page_type' => ['nullable', 'string', 'max:80'],
            'product_id' => ['nullable', 'integer'],
            'product_title' => ['nullable', 'string', 'max:255'],
        ]);

        $chat = $chatService->touchCustomerActivity(
            $chatService->findByPublicToken($token),
            $this->extractPageContext($validated),
        );

        return response()->json($chatService->chatPayload($chat));
    }

    public function sendMessage(Request $request, string $token, CustomerChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120', 'required_without:message'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'page_path' => ['nullable', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'page_type' => ['nullable', 'string', 'max:80'],
            'product_id' => ['nullable', 'integer'],
            'product_title' => ['nullable', 'string', 'max:255'],
        ]);

        $chat = $chatService->findByPublicToken($token);

        if (! $chatService->customerCanSend($chat)) {
            return response()->json([
                'message' => 'Specialists are currently unavailable. Please leave your email instead.',
            ], 409);
        }

        $attachment = $request->hasFile('attachment')
            ? $this->storeAttachment($request->file('attachment'))
            : null;
        $message = $chatService->sendCustomerMessageWithAttachment(
            $chat,
            $validated['message'] ?? null,
            $attachment,
            $this->extractPageContext($validated),
        );

        return response()->json([
            'message' => $chatService->messageData($message),
            'chat' => $chatService->chatSummary($chat->fresh(['assignedUser:id,name,is_chat_ready', 'messages.user:id,name'])),
        ]);
    }

    public function typing(Request $request, string $token, CustomerChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'typing' => ['required', 'boolean'],
        ]);

        $chat = $chatService->touchCustomerActivity(
            $chatService->findByPublicToken($token)
        );

        return response()->json([
            'ok' => true,
            'typing' => $chatService->setCustomerTyping($chat, $validated['typing']),
        ]);
    }

    public function leaveEmail(Request $request, CustomerChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'visitor_name' => ['nullable', 'string', 'max:120'],
            'visitor_email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'page_path' => ['nullable', 'string', 'max:2048'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'page_type' => ['nullable', 'string', 'max:80'],
            'product_id' => ['nullable', 'integer'],
            'product_title' => ['nullable', 'string', 'max:255'],
        ]);

        $chat = $chatService->leaveEmail(
            visitorName: $validated['visitor_name'] ?? null,
            visitorEmail: $validated['visitor_email'],
            message: $validated['message'] ?? null,
            pageContext: $this->extractPageContext($validated),
        );
        $this->sendOfflineLeadNotifications($chatService->chatSummary($chat));

        return response()->json([
            'saved' => true,
            'chat' => $chatService->chatSummary($chat),
        ]);
    }

    public function convertToEmailLead(Request $request, string $token, CustomerChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'visitor_name' => ['nullable', 'string', 'max:120'],
            'visitor_email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $chat = $chatService->findByPublicToken($token);

        $chat = $chatService->convertChatToOfflineLead(
            $chat,
            $validated['visitor_name'] ?? null,
            $validated['visitor_email'],
            $validated['message'] ?? null,
        );
        $this->sendOfflineLeadNotifications($chatService->chatSummary($chat));

        return response()->json([
            'saved' => true,
            'chat' => $chatService->chatSummary($chat),
            'messages' => $chatService->chatPayload($chat)['messages'],
            'typing' => $chatService->typingState($chat),
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

    private function extractPageContext(array $validated): ?array
    {
        $pageContext = array_filter([
            'page_url' => $validated['page_url'] ?? null,
            'page_path' => $validated['page_path'] ?? null,
            'page_title' => $validated['page_title'] ?? null,
            'page_type' => $validated['page_type'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'product_title' => $validated['product_title'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        return $pageContext ?: null;
    }

    private function sendOfflineLeadNotifications(array $chatSummary): void
    {
        $recipients = User::query()
            ->where('is_chat_ready', 1)
            ->whereNotNull('email')
            ->get(['name', 'email'])
            ->map(fn (User $user) => [
                'name' => $user->name ?: 'Watch Specialist',
                'email' => $user->email,
            ])
            ->unique('email')
            ->values();

        \Log::info($recipients->toArray());
        \Log::info($chatSummary);
        if ($recipients->isEmpty() && config('gmailer.mail_from')) {
            $recipients = collect([[
                'name' => 'Customer Support',
                'email' => config('gmailer.mail_from'),
            ]]);
        }

        foreach ($recipients as $recipient) {
            try {
                (new GMailer([
                    'to' => $recipient['email'],
                    'fullname' => $recipient['name'],
                    'subject' => 'New customer chat email lead',
                    'template' => 'emails.chat-offline-lead',
                    'chat' => $chatSummary,
                    'page_context' => $chatSummary['page_context'] ?? null,
                    'chat_url' => url('/admin/live-chat'),
                    'show_chat_url' => true,
                ]))->send();
            } catch (\Throwable $exception) {
                Log::warning('Customer chat email lead notification failed.', [
                    'recipient' => $recipient['email'],
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
