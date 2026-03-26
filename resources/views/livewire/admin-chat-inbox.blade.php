<div class="p-4" wire:poll.5s="refreshInbox">
    <div class="mb-4 flex flex-col gap-3 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Live Chat Inbox</h2>
            <p class="mt-1 text-sm text-gray-500">Review live conversations, reply to customers, and delete old threads.</p>
        </div>
        <div class="flex gap-2">
            <button
                wire:click="toggleChatAvailability"
                class="rounded-2xl px-4 py-2 text-sm font-semibold transition {{ $chatAvailable ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
            >
                {{ $chatAvailable ? 'Available' : 'Currently unavailable' }}
            </button>
            <button wire:click="refreshInbox" class="rounded-2xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Refresh
            </button>
            @if($selectedChat)
                <button
                    wire:click="deleteConversation"
                    wire:confirm="Delete this conversation permanently?"
                    class="rounded-2xl bg-red-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800"
                >
                    Delete Conversation
                </button>
            @endif
        </div>
    </div>

    <div class="mb-4 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Quick Replies</h3>
            <p class="mt-1 text-sm text-gray-500">Add canned responses that the specialist can drop into the floating chat widget.</p>
        </div>

        <form wire:submit="saveQuickReply" class="grid gap-3 lg:grid-cols-[14rem,1fr,auto]">
            <input
                wire:model.defer="quickReplyLabel"
                type="text"
                maxlength="120"
                placeholder="Label"
                class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >
            <input
                wire:model.defer="quickReplyMessage"
                type="text"
                maxlength="2000"
                placeholder="Message"
                class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >
            <button type="submit" class="rounded-2xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                Save Reply
            </button>
        </form>

        <div class="mt-4 flex flex-wrap gap-2">
            @forelse($quickReplies as $reply)
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="text-sm font-semibold text-gray-900">{{ $reply['label'] }}</div>
                    <div class="mt-1 max-w-xl text-sm text-gray-600">{{ $reply['message'] }}</div>
                    <button
                        wire:click="deleteQuickReply({{ $reply['id'] }})"
                        wire:confirm="Delete this quick reply?"
                        class="mt-3 text-sm font-semibold text-red-700 transition hover:text-red-800"
                    >
                        Delete
                    </button>
                </div>
            @empty
                <div class="text-sm text-gray-500">No quick replies added yet.</div>
            @endforelse
        </div>
    </div>

    @if($notice)
        <div class="mb-4 rounded-2xl border px-4 py-3 text-sm {{ $noticeTone === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-green-200 bg-green-50 text-green-700' }}">
            {{ $notice }}
        </div>
    @endif

    <div class="grid gap-4 xl:grid-cols-[20rem,1fr]">
        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-500">Conversations</h3>
            </div>

            <div class="max-h-[70vh] overflow-y-auto p-3">
                @forelse($chats as $chat)
                    @php
                        $isSelected = $selectedChatId === $chat['id'];
                        $statusLabel = match ($chat['attention_state']) {
                            'new' => 'New Chat',
                            'reply_needed' => 'Awaiting Reply',
                            default => match (true) {
                                $chat['status'] === 'offline' => 'Email Lead',
                                !($chat['customer_is_online'] ?? false) => 'Disconnected',
                                default => 'Ongoing',
                            },
                        };
                        $statusClasses = match ($statusLabel) {
                            'New Chat' => 'bg-red-100 text-red-700',
                            'Awaiting Reply' => 'bg-amber-100 text-amber-700',
                            'Waiting' => 'bg-amber-100 text-amber-700',
                            'Email Lead' => 'bg-slate-100 text-slate-700',
                            'Disconnected' => 'bg-gray-100 text-gray-700',
                            default => 'bg-green-100 text-green-700',
                        };
                    @endphp

                    <button
                        wire:click="selectChat({{ $chat['id'] }})"
                        class="mb-3 block w-full rounded-2xl border px-4 py-4 text-left transition {{ $isSelected ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-white hover:border-red-200 hover:bg-red-50' }}"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-semibold text-gray-900">
                                {{ $chat['visitor_name'] ?: ($chat['visitor_email'] ?: 'Visitor #'.$chat['id']) }}
                            </span>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        @if($chat['visitor_email'])
                            <p class="mt-1 truncate text-xs text-gray-400">{{ $chat['visitor_email'] }}</p>
                        @endif

                        <p class="mt-3 line-clamp-3 text-sm leading-5 text-gray-600">
                            {{ $chat['last_message_preview'] ?: 'No messages yet' }}
                        </p>
                    </button>
                @empty
                    <div class="px-3 py-6 text-sm text-gray-500">No conversations available yet.</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            @if($selectedChat)
                <div class="border-b border-gray-100 px-6 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{ $selectedChat['visitor_name'] ?: ($selectedChat['visitor_email'] ?: 'Visitor #'.$selectedChat['id']) }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(($selectedChat['attention_state'] ?? null) === 'new')
                                    Brand new chat request from the customer.
                                @elseif(($selectedChat['attention_state'] ?? null) === 'reply_needed')
                                    Customer sent a new message and is waiting for your reply.
                                @elseif($selectedChat['status'] === 'offline')
                                    Customer left contact details for follow-up.
                                @elseif($selectedChat['assigned_user'])
                                    Assigned to {{ $selectedChat['assigned_user']['name'] }}.
                                @else
                                    Waiting for a specialist to join.
                                @endif
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500">
                                <span class="rounded-full bg-gray-100 px-3 py-1">Status: {{ strtoupper($selectedChat['status']) }}</span>
                                @if($selectedChat['visitor_email'])
                                    <span class="rounded-full bg-gray-100 px-3 py-1">Email: {{ $selectedChat['visitor_email'] }}</span>
                                @endif
                                @if($selectedChat['customer_is_online'])
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-green-700">Customer online</span>
                                @elseif($selectedChat['customer_last_seen_at'])
                                    <span class="rounded-full bg-gray-100 px-3 py-1">Last seen: {{ \Carbon\Carbon::parse($selectedChat['customer_last_seen_at'])->diffForHumans() }}</span>
                                @elseif($selectedChat['status'] === 'active')
                                    <span class="rounded-full bg-gray-100 px-3 py-1">Customer is no longer connected</span>
                                @endif
                                @if(data_get($selectedChat, 'page_context.page_path'))
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">Page: {{ data_get($selectedChat, 'page_context.page_path') }}</span>
                                @endif
                                @if(data_get($selectedChat, 'page_context.product.title') || data_get($selectedChat, 'page_context.product.id'))
                                    <span class="rounded-full bg-red-50 px-3 py-1 text-red-700">
                                        Item:
                                        {{ data_get($selectedChat, 'page_context.product.title') ?: 'Product #'.data_get($selectedChat, 'page_context.product.id') }}
                                        @if(data_get($selectedChat, 'page_context.product.title') && data_get($selectedChat, 'page_context.product.id'))
                                            (#{{ data_get($selectedChat, 'page_context.product.id') }})
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex w-full flex-col items-stretch gap-2 lg:w-auto lg:items-end">
                            <div class="flex w-full items-center gap-2 lg:w-[22rem]">
                                <select
                                    wire:model.live="selectedQuickReplyId"
                                    class="block min-w-0 flex-1 rounded-2xl border border-gray-200 px-4 py-2 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"
                                >
                                    <option value="">Quick reply...</option>
                                    @foreach($quickReplies as $reply)
                                        <option value="{{ $reply['id'] }}">{{ $reply['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2">
                            @if($selectedChat['status'] === 'waiting')
                                <button wire:click="joinSelectedChat" class="rounded-2xl bg-red-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-900">
                                    Join Chat
                                </button>
                            @endif
                            <button
                                wire:click="deleteConversation({{ $selectedChat['id'] }})"
                                wire:confirm="Delete this conversation permanently?"
                                class="rounded-2xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50"
                            >
                                Delete
                            </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="max-h-[48vh] overflow-y-auto bg-gray-50 px-6 py-5">
                    <div class="space-y-4">
                        @foreach($messages as $message)
                            @php($attachment = $message['attachment'] ?? null)
                            @if($message['sender_type'] === 'system')
                                <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-center text-xs leading-5 text-gray-600 shadow-sm">
                                    {{ $message['message'] }}
                                </div>
                            @elseif($message['sender_type'] === 'staff')
                                <div class="flex justify-end">
                                    <div class="max-w-[85%]">
                                        <div class="mb-1 text-right text-[11px] text-gray-400">
                                            You • {{ \Carbon\Carbon::parse($message['created_at'])->format('g:i A') }}
                                        </div>
                                        <div class="rounded-2xl bg-gray-900 px-4 py-3 text-sm leading-6 text-white shadow-sm">
                                            @if(!empty($message['message']))
                                                {!! nl2br(e($message['message'])) !!}
                                            @endif
                                            @if($attachment)
                                                <div class="{{ !empty($message['message']) ? 'mt-3' : '' }}">
                                                    <button
                                                        type="button"
                                                        data-chat-image-url="{{ $attachment['url'] }}"
                                                        data-chat-image-name="{{ $attachment['name'] ?? 'Chat attachment' }}"
                                                        class="block overflow-hidden rounded-2xl border border-white/20 bg-white/10 text-left"
                                                    >
                                                        @if($attachment['is_image'])
                                                            <img src="{{ $attachment['url'] }}" alt="{{ $attachment['name'] ?? 'Chat attachment' }}" class="max-h-64 w-full object-cover">
                                                        @else
                                                            <div class="px-4 py-3 text-sm text-white">{{ $attachment['name'] ?? 'Attachment' }}</div>
                                                        @endif
                                                    </button>
                                                    <div class="mt-2 text-[11px] text-white/80">{{ $attachment['name'] ?? 'Attachment' }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex justify-start">
                                    <div class="max-w-[85%]">
                                        <div class="mb-1 text-[11px] text-gray-400">
                                            Customer • {{ \Carbon\Carbon::parse($message['created_at'])->format('g:i A') }}
                                        </div>
                                        <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm leading-6 text-gray-900 shadow-sm">
                                            @if(!empty($message['message']))
                                                {!! nl2br(e($message['message'])) !!}
                                            @endif
                                            @if($attachment)
                                                <div class="{{ !empty($message['message']) ? 'mt-3' : '' }}">
                                                    <button
                                                        type="button"
                                                        data-chat-image-url="{{ $attachment['url'] }}"
                                                        data-chat-image-name="{{ $attachment['name'] ?? 'Chat attachment' }}"
                                                        class="block overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 text-left"
                                                    >
                                                        @if($attachment['is_image'])
                                                            <img src="{{ $attachment['url'] }}" alt="{{ $attachment['name'] ?? 'Chat attachment' }}" class="max-h-64 w-full object-cover">
                                                        @else
                                                            <div class="px-4 py-3 text-sm text-gray-900">{{ $attachment['name'] ?? 'Attachment' }}</div>
                                                        @endif
                                                    </button>
                                                    <div class="mt-2 text-[11px] text-gray-500">{{ $attachment['name'] ?? 'Attachment' }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100 px-6 py-5">
                    @if(($selectedChat['typing']['customer']['is_typing'] ?? false) === true)
                        <div class="mb-3 text-sm text-gray-500">
                            {{ $selectedChat['typing']['customer']['label'] ?? 'Customer is typing...' }}
                        </div>
                    @endif

                    @if($selectedChat['status'] === 'offline')
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            This customer already left live chat. Follow up using {{ $selectedChat['visitor_email'] ?: 'their saved contact details' }}.
                        </div>
                    @else
                        <form wire:submit="sendReply" class="space-y-3">
                            <textarea
                                wire:model.live.debounce.350ms="replyMessage"
                                rows="4"
                                placeholder="{{ $selectedChat['status'] === 'waiting' ? 'Type your reply and send to join this chat...' : 'Type your reply...' }}"
                                class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"
                            ></textarea>
                            @error('replyMessage')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <label class="inline-flex cursor-pointer items-center rounded-2xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                        <input wire:model="replyAttachment" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
                                        Attach Image
                                    </label>
                                    @if($replyAttachment)
                                        <p class="mt-2 text-xs text-gray-500">{{ $replyAttachment->getClientOriginalName() }}</p>
                                    @endif
                                    @error('replyAttachment')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-900 text-white transition hover:bg-red-800" title="{{ $selectedChat['status'] === 'waiting' ? 'Join and send reply' : 'Send reply' }}" aria-label="{{ $selectedChat['status'] === 'waiting' ? 'Join and send reply' : 'Send reply' }}">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 11.5 20 4l-4.5 16-3.2-6.3L4 11.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="m20 4-7.7 9.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            @else
                <div class="flex min-h-[30rem] items-center justify-center px-6 text-center text-gray-500">
                    Select a conversation from the left to read messages and reply.
                </div>
            @endif
        </div>
    </div>
</div>
