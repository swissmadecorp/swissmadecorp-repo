<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\CustomerChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        ]);

        $chat = $chatService->startChat(
            visitorName: $validated['visitor_name'] ?? null,
            visitorEmail: $validated['visitor_email'] ?? null,
            message: $validated['message'],
        );

        if (! $chat) {
            return response()->json($chatService->availablePayload(), 409);
        }

        return response()->json($chatService->chatPayload($chat));
    }

    public function show(string $token, CustomerChatService $chatService): JsonResponse
    {
        $chat = $chatService->touchCustomerActivity(
            $chatService->findByPublicToken($token)
        );

        return response()->json($chatService->chatPayload($chat));
    }

    public function sendMessage(Request $request, string $token, CustomerChatService $chatService): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000', 'required_without:attachment'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120', 'required_without:message'],
        ]);

        $chat = $chatService->findByPublicToken($token);
        $attachment = $request->hasFile('attachment')
            ? $this->storeAttachment($request->file('attachment'))
            : null;
        $message = $chatService->sendCustomerMessageWithAttachment(
            $chat,
            $validated['message'] ?? null,
            $attachment,
        );

        return response()->json([
            'message' => $chatService->messageData($message),
            'chat' => $chatService->chatSummary($chat->fresh(['assignedUser:id,name', 'messages.user:id,name'])),
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
        ]);

        $chat = $chatService->leaveEmail(
            visitorName: $validated['visitor_name'] ?? null,
            visitorEmail: $validated['visitor_email'],
            message: $validated['message'] ?? null,
        );

        return response()->json([
            'saved' => true,
            'chat' => $chatService->chatSummary($chat),
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
}
