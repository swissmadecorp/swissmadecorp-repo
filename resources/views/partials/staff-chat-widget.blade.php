<div
    data-staff-chat-widget
    data-user-id="{{ auth()->id() }}"
    data-storage-key="swissmade_staff_chat_state"
    data-list-url="/chat/staff/conversations"
    data-show-url-template="/chat/staff/conversations/__ID__"
    data-claim-url-template="/chat/staff/conversations/__ID__/claim"
    data-message-url-template="/chat/staff/conversations/__ID__/messages"
    data-typing-url-template="/chat/staff/conversations/__ID__/typing"
    data-heartbeat-url="/chat/staff/heartbeat"
    data-availability-url="/chat/staff/availability"
    data-quick-reply-store-url="/chat/staff/quick-replies"
    data-quick-reply-destroy-url-template="/chat/staff/quick-replies/__ID__"
    class="fixed bottom-4 right-4 z-[95] select-none"
>
    <button
        type="button"
        data-staff-drag-handle
        data-staff-toggle
        aria-label="Open customer chats"
        title="Customer chats"
        class="relative flex h-12 w-12 items-center justify-center rounded-full bg-red-800 text-white shadow-2xl ring-1 ring-black/10 transition hover:bg-red-900"
    >
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-red-800">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H17a3 3 0 0 1 3 3v5.5A2.5 2.5 0 0 1 17.5 16H9l-4.5 3V7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span data-staff-badge class="absolute -right-1 -top-1 hidden min-w-[1.35rem] rounded-full bg-white px-1.5 py-0.5 text-center text-[10px] font-bold text-red-800 shadow ring-1 ring-red-100"></span>
    </button>

    <div data-staff-panel class="absolute hidden h-[min(38rem,80vh)] w-[21rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl ring-1 ring-black/5 sm:w-[26rem]">
        <div class="flex h-full min-h-0 flex-col">
        <div class="shrink-0 flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-50 text-red-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H17a3 3 0 0 1 3 3v5.5A2.5 2.5 0 0 1 17.5 16H9l-4.5 3V7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <p class="text-sm font-semibold text-gray-900">Chat Requests</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" data-staff-availability-toggle class="rounded-full bg-green-100 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-200">
                    Available
                </button>
                <button type="button" data-staff-close class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div data-staff-alert class="hidden shrink-0 border-b border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700"></div>

        <div class="grid min-h-0 flex-1 grid-cols-[8.5rem,1fr]">
            <div class="min-h-0 border-r border-gray-100 bg-gray-50/80">
                <div class="border-b border-gray-100 px-3 py-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                    Inbox
                </div>
                <div data-staff-chat-list class="h-[calc(100%-3rem)] overflow-y-auto p-2"></div>
            </div>

            <div class="flex min-h-0 min-w-0 flex-col overflow-hidden">
                <div class="border-b border-gray-100 px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p data-staff-active-title class="text-sm font-semibold text-gray-900">Select a chat</p>
                            <p data-staff-active-subtitle class="mt-1 text-xs text-gray-500">Waiting chats and your active conversations appear on the left.</p>
                            <div data-staff-active-meta class="mt-2 hidden flex-wrap gap-2 text-[11px] text-gray-500"></div>
                            <div data-staff-active-context class="mt-2 hidden rounded-2xl border border-blue-100 bg-blue-50/70 px-3 py-3 text-xs text-gray-700"></div>
                            <p data-staff-active-note class="mt-2 hidden rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-800"></p>
                            <p data-staff-typing-note class="mt-2 hidden text-xs font-medium text-gray-500"></p>
                        </div>
                        <div class="relative flex w-40 shrink-0 flex-col items-end gap-2">
                            <button type="button" data-staff-claim class="hidden rounded-2xl bg-red-800 px-3 py-2 text-xs font-semibold text-white transition hover:bg-red-900">
                                Join Chat
                            </button>
                            <div class="flex w-full items-center gap-2">
                                <select data-staff-quick-reply class="block min-w-0 flex-1 rounded-2xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                                    <option value="">Quick reply...</option>
                                </select>
                                <button type="button" data-staff-quick-reply-manage-toggle class="shrink-0 rounded-2xl border border-gray-200 px-3 py-2 text-[11px] font-semibold text-red-800 transition hover:bg-gray-50 hover:text-red-900">
                                    Manage
                                </button>
                            </div>
                            <div data-staff-quick-reply-manager class="absolute right-0 top-full z-10 mt-2 hidden w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl ring-1 ring-black/5">
                                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Quick Replies</p>
                                        <p class="mt-1 text-xs text-gray-500">Save short replies without covering the conversation.</p>
                                    </div>
                                    <button type="button" data-staff-quick-reply-manager-close class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="space-y-3 p-4">
                                    <input data-staff-quick-reply-label type="text" maxlength="120" placeholder="Reply label" class="block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                                    <textarea data-staff-quick-reply-message rows="3" maxlength="2000" placeholder="Reply text..." class="block w-full resize-none rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                                    <div class="flex items-center justify-between gap-3">
                                        <button type="button" data-staff-quick-reply-fill class="text-xs font-semibold text-gray-600 transition hover:text-gray-900">
                                            Use Current Draft
                                        </button>
                                        <button type="button" data-staff-quick-reply-save class="rounded-2xl bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-800">
                                            Save Reply
                                        </button>
                                    </div>
                                    <div data-staff-quick-reply-list class="max-h-36 overflow-y-auto pr-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-staff-empty class="flex flex-1 items-center justify-center px-6 text-center text-sm text-gray-500">
                    No active customer chats right now.
                </div>

                <div data-staff-conversation class="hidden min-h-0 flex flex-1 flex-col overflow-hidden">
                    <div data-staff-messages class="min-h-0 flex-1 overflow-y-auto bg-white px-4 py-4"></div>
                    <form data-staff-message-form class="shrink-0 border-t border-gray-100 bg-white px-4 py-3">
                        <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">
                            Reply To Customer
                        </label>
                        <div class="flex gap-2">
                            <textarea data-staff-message-input rows="3" maxlength="5000" placeholder="Type your reply to this visitor..." class="block min-h-[4.5rem] flex-1 resize-none rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                            <div class="flex shrink-0 flex-col gap-1">
                                <label class="inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded-md border border-gray-200 text-gray-700 transition hover:bg-gray-50" title="Attach image">
                                    <input data-staff-attachment-input type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12.5 7.5 8 12a3.5 3.5 0 1 0 5 5l6-6a5 5 0 1 0-7.1-7.1L5.6 10.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </label>
                                <button type="submit" data-staff-send-button class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-gray-900 text-white transition hover:bg-red-800" title="Send reply">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 11.5 20 4l-4.5 16-3.2-6.3L4 11.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="m20 4-7.7 9.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 min-h-[1rem] text-right">
                            <span data-staff-attachment-name class="text-xs text-gray-500"></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-staff-chat-widget]').forEach(function (root) {
        if (root.dataset.inlineToggleBound === '1') {
            return;
        }

        root.dataset.inlineToggleBound = '1';

        var panel = root.querySelector('[data-staff-panel]');
        var toggle = root.querySelector('[data-staff-toggle]');
        var close = root.querySelector('[data-staff-close]');
        var storageKey = root.dataset.storageKey || 'swissmade_staff_chat_state';

        if (!panel || !toggle || !close) {
            return;
        }

        toggle.addEventListener('click', function () {
            var willOpen = panel.classList.contains('hidden');
            panel.classList.toggle('hidden');
            window.localStorage.setItem(storageKey + ':open', willOpen ? '1' : '0');
            root.dispatchEvent(new CustomEvent(willOpen ? 'staff-chat:opened' : 'staff-chat:closed'));
        });

        close.addEventListener('click', function () {
            panel.classList.add('hidden');
            window.localStorage.setItem(storageKey + ':open', '0');
            root.dispatchEvent(new CustomEvent('staff-chat:closed'));
        });
    });
});
</script>
