<?php

namespace App\Livewire;

use App\Models\CustomerChat;
use App\Services\ChatPresenceService;
use App\Services\CustomerChatService;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AdminChatInbox extends Component
{
    use WithFileUploads;

    public array $chats = [];
    #[Url(as: 'chat')]
    public ?int $selectedChatId = null;
    public ?array $selectedChat = null;
    public array $messages = [];
    public string $replyMessage = '';
    public string $selectedQuickReplyId = '';
    public $replyAttachment = null;
    public array $quickReplies = [];
    public string $quickReplyLabel = '';
    public string $quickReplyMessage = '';
    public string $notice = '';
    public string $noticeTone = 'success';
    public bool $isReplyTyping = false;
    public bool $chatAvailable = true;
    public array $chatActivity = [];

    public function mount(): void
    {
        $this->refreshInbox();
    }

    public function refreshInbox(): void
    {
        $user = $this->ensureChatReady();
        $service = $this->chatService();
        $this->chatAvailable = $service->touchStaffPresence($user);
        $this->quickReplies = $service->quickReplies();

        $summaries = $service->staffChats($user)
            ->map(fn (CustomerChat $chat) => $service->chatSummary($chat))
            ->sort(fn (array $left, array $right) => $this->compareChatPriority($left, $right))
            ->values();

        $incomingChatId = $this->resolveIncomingAttentionChat($summaries->all());
        $this->chats = $summaries->all();

        if ($incomingChatId) {
            if ($this->selectedChatId && $this->selectedChatId !== $incomingChatId) {
                $this->syncTypingState(false);
            }

            $incomingChat = collect($this->chats)->firstWhere('id', $incomingChatId);
            $this->selectedChatId = $incomingChatId;

            if ($incomingChat) {
                $this->flashNotice(
                    $incomingChat['attention_state'] === 'new'
                        ? 'A new chat request just opened.'
                        : 'A customer sent a new message.'
                );
            }
        }

        if (! $this->selectedChatId && count($this->chats) > 0) {
            $this->selectedChatId = $this->chats[0]['id'];
        }

        if (
            $this->selectedChatId
            && ! collect($this->chats)->contains(fn (array $chat) => $chat['id'] === $this->selectedChatId)
        ) {
            $this->selectedChatId = count($this->chats) > 0 ? $this->chats[0]['id'] : null;
        }

        if ($this->selectedChatId) {
            $this->loadSelectedChat();
        } else {
            $this->selectedChat = null;
            $this->messages = [];
        }

        $this->rememberChatActivity($this->chats);
    }

    public function selectChat(int $chatId): void
    {
        $this->syncTypingState(false);
        $this->selectedChatId = $chatId;
        $this->replyMessage = '';
        $this->selectedQuickReplyId = '';
        $this->replyAttachment = null;
        $this->loadSelectedChat();
    }

    public function joinSelectedChat(): void
    {
        $user = $this->ensureChatReady();

        if (! $this->selectedChatId) {
            return;
        }

        $chat = CustomerChat::findOrFail($this->selectedChatId);

        try {
            $this->chatService()->claimChat($chat, $user);
            $this->flashNotice('You joined the chat.');
        } catch (ConflictHttpException $exception) {
            $this->flashNotice($exception->getMessage(), 'error');
        }

        $this->refreshInbox();
    }

    public function sendReply(): void
    {
        $user = $this->ensureChatReady();

        if (! $this->selectedChatId) {
            return;
        }

        $this->validate([
            'replyMessage' => ['nullable', 'string', 'max:5000', 'required_without:replyAttachment'],
            'replyAttachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120', 'required_without:replyMessage'],
        ]);

        $chat = CustomerChat::findOrFail($this->selectedChatId);

        if ($chat->status === CustomerChat::STATUS_OFFLINE) {
            $this->flashNotice('This conversation is email-only. Follow up using the customer email above.', 'error');
            return;
        }

        try {
            $attachment = $this->replyAttachment
                ? $this->storeAttachment($this->replyAttachment)
                : null;

            $this->chatService()->sendStaffMessageWithAttachment(
                $chat,
                $user,
                trim($this->replyMessage) ?: null,
                $attachment,
            );
            $this->replyMessage = '';
            $this->selectedQuickReplyId = '';
            $this->replyAttachment = null;
            $this->isReplyTyping = false;
            $this->flashNotice('Reply sent.');
        } catch (ConflictHttpException $exception) {
            $this->flashNotice($exception->getMessage(), 'error');
        }

        $this->refreshInbox();
    }

    public function updatedReplyMessage(string $value): void
    {
        $this->syncTypingState(trim($value) !== '');
    }

    public function updatedSelectedQuickReplyId(string $value): void
    {
        if ($value === '') {
            return;
        }

        $reply = collect($this->quickReplies)->firstWhere('id', (int) $value);

        if (! $reply) {
            return;
        }

        $this->replyMessage = $reply['message'];
        $this->syncTypingState(trim($this->replyMessage) !== '');
    }

    public function deleteConversation(?int $chatId = null): void
    {
        $user = $this->ensureChatReady();
        $chatId ??= $this->selectedChatId;

        if (! $chatId) {
            return;
        }

        if ($chatId === $this->selectedChatId) {
            $this->syncTypingState(false);
        }

        $chat = CustomerChat::findOrFail($chatId);

        if ($chat->assigned_user_id && $chat->assigned_user_id !== $user->id && $chat->status === CustomerChat::STATUS_ACTIVE) {
            abort(403);
        }

        $chat->delete();

        if ($this->selectedChatId === $chatId) {
            $this->selectedChatId = null;
            $this->selectedChat = null;
            $this->messages = [];
            $this->replyMessage = '';
            $this->selectedQuickReplyId = '';
            $this->replyAttachment = null;
        }

        $this->flashNotice('Conversation deleted.');
        $this->refreshInbox();
    }

    public function saveQuickReply(): void
    {
        $this->validate([
            'quickReplyLabel' => ['required', 'string', 'max:120'],
            'quickReplyMessage' => ['required', 'string', 'max:2000'],
        ]);

        $this->chatService()->saveQuickReply(
            trim($this->quickReplyLabel),
            trim($this->quickReplyMessage),
        );

        $this->quickReplyLabel = '';
        $this->quickReplyMessage = '';
        $this->flashNotice('Quick reply saved.');
        $this->refreshInbox();
    }

    public function deleteQuickReply(int $replyId): void
    {
        $this->chatService()->deleteQuickReply($replyId);
        $this->flashNotice('Quick reply deleted.');
        $this->refreshInbox();
    }

    public function toggleChatAvailability(): void
    {
        $user = $this->ensureChatReady();
        $this->chatAvailable = app(ChatPresenceService::class)->setAvailability($user, ! $this->chatAvailable);

        if (! $this->chatAvailable) {
            $this->syncTypingState(false);
            $this->flashNotice('You are currently unavailable for new chats.');
        } else {
            $this->flashNotice('You are available for new chats.');
        }

        $this->refreshInbox();
    }

    public function render()
    {
        return view('livewire.admin-chat-inbox')
            ->layout('components.layouts.admin')
            ->layoutData(['pageName' => 'Live Chat'])
            ->title('Live Chat');
    }

    private function loadSelectedChat(): void
    {
        $user = $this->ensureChatReady();
        $chat = CustomerChat::query()
            ->with([
                'assignedUser:id,name',
                'messages' => fn ($query) => $query->with('user:id,name')->orderBy('id'),
            ])
            ->find($this->selectedChatId);

        if (! $chat) {
            $this->selectedChatId = null;
            $this->selectedChat = null;
            $this->messages = [];
            return;
        }

        if (
            $chat->assigned_user_id
            && $chat->assigned_user_id !== $user->id
            && ! in_array($chat->status, [CustomerChat::STATUS_WAITING, CustomerChat::STATUS_OFFLINE], true)
        ) {
            $this->flashNotice('That chat is assigned to another specialist.', 'error');
            $this->selectedChatId = null;
            $this->selectedChat = null;
            $this->messages = [];
            return;
        }

        $payload = $this->chatService()->chatPayload($chat);
        $this->selectedChat = array_merge($payload['chat'], [
            'typing' => $payload['typing'] ?? [
                'customer' => ['is_typing' => false, 'label' => 'Customer is typing...'],
                'staff' => ['is_typing' => false, 'label' => 'A specialist is typing...'],
            ],
        ]);
        $this->messages = $payload['messages'];
    }

    private function chatService(): CustomerChatService
    {
        return app(CustomerChatService::class);
    }

    private function ensureChatReady()
    {
        $user = auth()->user();

        abort_unless($user && $user->is_chat_ready, 403);

        return $user;
    }

    private function storeAttachment($file): array
    {
        $directory = public_path('uploads/chat');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize();
        $filename = Str::uuid() . '-' . preg_replace('/[^A-Za-z0-9.\\-_]/', '-', $originalName);
        $file->move($directory, $filename);

        return [
            'path' => 'uploads/chat/' . $filename,
            'name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
        ];
    }

    private function flashNotice(string $message, string $tone = 'success'): void
    {
        $this->notice = $message;
        $this->noticeTone = $tone;
    }

    private function syncTypingState(bool $isTyping): void
    {
        if (! $this->selectedChatId || $this->isReplyTyping === $isTyping) {
            return;
        }

        $chat = CustomerChat::query()->with('assignedUser:id,name')->find($this->selectedChatId);

        if (! $chat || $chat->status === CustomerChat::STATUS_OFFLINE) {
            $this->isReplyTyping = false;
            return;
        }

        try {
            $this->chatService()->setStaffTyping($chat, $this->ensureChatReady(), $isTyping);
            $this->isReplyTyping = $isTyping;
        } catch (ConflictHttpException) {
            $this->isReplyTyping = false;
        }
    }

    private function resolveIncomingAttentionChat(array $summaries): ?int
    {
        if (count($this->chatActivity) === 0) {
            return null;
        }

        foreach ($summaries as $chat) {
            $previousLastMessageAt = $this->chatActivity[$chat['id']] ?? null;
            $hasFreshActivity = $previousLastMessageAt !== null && $previousLastMessageAt !== $chat['last_message_at'];

            if ($hasFreshActivity && in_array($chat['attention_state'], ['new', 'reply_needed'], true)) {
                return $chat['id'];
            }
        }

        return null;
    }

    private function rememberChatActivity(array $summaries): void
    {
        $this->chatActivity = collect($summaries)
            ->mapWithKeys(fn (array $chat) => [$chat['id'] => $chat['last_message_at']])
            ->all();
    }

    private function compareChatPriority(array $left, array $right): int
    {
        $leftPriority = $this->chatPriority($left);
        $rightPriority = $this->chatPriority($right);

        if ($leftPriority !== $rightPriority) {
            return $leftPriority <=> $rightPriority;
        }

        return strtotime($right['last_message_at'] ?? '') <=> strtotime($left['last_message_at'] ?? '');
    }

    private function chatPriority(array $chat): int
    {
        return match ($chat['attention_state'] ?? null) {
            'new' => 0,
            'reply_needed' => 1,
            default => match ($chat['status'] ?? null) {
                CustomerChat::STATUS_ACTIVE => 2,
                CustomerChat::STATUS_OFFLINE => 3,
                default => 4,
            },
        };
    }
}
