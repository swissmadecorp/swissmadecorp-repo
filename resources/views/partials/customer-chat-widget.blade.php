<div
    data-customer-chat-widget
    data-storage-key="swissmade_customer_chat_token"
    data-availability-url="/chat/availability"
    data-create-url="/chat/conversations"
    data-leave-email-url="/chat/leave-email"
    data-show-url-template="/chat/conversations/__TOKEN__"
    data-message-url-template="/chat/conversations/__TOKEN__/messages"
    data-conversation-leave-email-url-template="/chat/conversations/__TOKEN__/leave-email"
    data-typing-url-template="/chat/conversations/__TOKEN__/typing"
    class="fixed bottom-4 right-4 z-[90]"
>
    <button
        type="button"
        data-chat-toggle
        class="flex items-center gap-3 rounded-full bg-gray-900 px-5 py-3 text-sm font-semibold text-white shadow-2xl ring-1 ring-black/10 transition hover:bg-red-800"
    >
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-900">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M8 10h8M8 14h5m-8 6 2.6-2.6a2 2 0 0 1 1.4-.6H18a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H6A3 3 0 0 0 3 7v10a3 3 0 0 0 3 3h2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span>
            <span class="block text-left leading-none">Live Chat</span>
            <span data-customer-toggle-copy class="mt-1 block text-left text-xs font-normal text-gray-300">Questions about a watch?</span>
        </span>
    </button>

    <div data-chat-panel class="mt-3 hidden w-[22rem] max-w-[calc(100vw-2rem)] rounded-3xl border border-gray-200 bg-white shadow-2xl ring-1 ring-black/5 sm:w-[25rem]">
        <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4">
            <div>
                <p class="text-sm font-semibold text-gray-900">Swiss Made Corp Chat</p>
                <p data-customer-status-badge class="mt-1 inline-flex rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-medium text-green-700">
                    Checking availability...
                </p>
            </div>
            <button type="button" data-chat-close class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="px-5 py-4">
            <div data-customer-alert class="mb-4 hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"></div>

            <div data-prechat-pane class="space-y-4">
                <p data-prechat-copy class="text-sm leading-6 text-gray-600">
                    Start a one-to-one conversation with one of our available watch specialists.
                </p>

                <form data-start-form class="space-y-3">
                    <input data-customer-name type="text" maxlength="120" placeholder="Your name (optional)" class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                    <input data-customer-email type="email" maxlength="255" placeholder="Your email (optional)" class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                    <textarea data-customer-start-message rows="4" maxlength="5000" placeholder="How can we help you today?" class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                        Start Chat
                    </button>
                </form>

                <form data-leave-email-form class="hidden space-y-3">
                    <input data-lead-name type="text" maxlength="120" placeholder="Your name (optional)" class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                    <input data-lead-email type="email" maxlength="255" placeholder="Your email" class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                    <textarea data-lead-message rows="4" maxlength="5000" placeholder="Leave your message" class="block w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-red-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                        Leave Email
                    </button>
                </form>
            </div>

            <div data-conversation-pane class="hidden">
                <div data-customer-chat-meta class="mb-3 rounded-2xl bg-gray-50 px-4 py-3 text-xs text-gray-600"></div>
                <div data-customer-messages class="flex h-80 flex-col gap-3 overflow-y-auto rounded-3xl bg-gray-50 p-4"></div>
                <div data-customer-typing class="mt-2 hidden px-2 text-xs font-medium text-gray-500"></div>

                <form data-customer-message-form class="mt-4 space-y-3">
                    <div class="flex gap-2">
                        <textarea data-customer-message-input rows="2" maxlength="5000" placeholder="Type your message..." class="block min-h-[3rem] flex-1 resize-none rounded-2xl border border-gray-200 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                        <button type="submit" class="rounded-2xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                            Send
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex cursor-pointer items-center rounded-2xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            <input data-customer-attachment-input type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
                            Attach Image
                        </label>
                        <span data-customer-attachment-name class="text-xs text-gray-500"></span>
                    </div>
                </form>

                <div data-customer-offline-followup class="mt-4 hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                    <p data-customer-offline-followup-copy class="text-sm leading-6 text-amber-800">
                        No specialist is available right now. Leave your email and we will follow up shortly.
                    </p>

                    <form data-customer-conversation-leave-email-form class="mt-3 space-y-3">
                        <input data-conversation-lead-name type="text" maxlength="120" placeholder="Your name (optional)" class="block w-full rounded-2xl border border-amber-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                        <input data-conversation-lead-email type="email" maxlength="255" placeholder="Your email" class="block w-full rounded-2xl border border-amber-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100">
                        <textarea data-conversation-lead-message rows="3" maxlength="5000" placeholder="Anything else you would like us to know?" class="block w-full rounded-2xl border border-amber-200 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-100"></textarea>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-red-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-800">
                            Email Me Back
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
