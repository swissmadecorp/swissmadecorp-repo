@if (auth()->check() && auth()->user()->is_chat_ready && !request()->is('admin/live-chat'))
    <a
        href="/admin/live-chat"
        data-staff-chat-launcher
        data-heartbeat-url="/chat/staff/heartbeat"
        data-list-url="/chat/staff/conversations"
        data-live-chat-url="/admin/live-chat"
        class="fixed bottom-4 right-4 z-[95] inline-flex items-center gap-3 rounded-full bg-red-800 px-5 py-3 text-sm font-semibold text-white shadow-2xl ring-1 ring-black/10 transition hover:bg-red-900"
    >
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-red-800">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H17a3 3 0 0 1 3 3v5.5A2.5 2.5 0 0 1 17.5 16H9l-4.5 3V7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span>
            <span class="block text-left leading-none">Live Chat</span>
            <span class="mt-1 block text-left text-xs font-normal text-red-100">
                {{ request()->is('admin/live-chat') ? 'Chat center open' : 'Open chat center' }}
            </span>
        </span>
    </a>
@endif
