const TITLE_STATE = {
    original: document.title,
    timer: null,
};

function flashTitle(label) {
    if (document.hasFocus() || TITLE_STATE.timer) {
        return;
    }

    TITLE_STATE.timer = window.setInterval(() => {
        document.title = document.title === label ? TITLE_STATE.original : label;
    }, 1000);
}

function resetTitle() {
    if (TITLE_STATE.timer) {
        window.clearInterval(TITLE_STATE.timer);
        TITLE_STATE.timer = null;
    }

    document.title = TITLE_STATE.original;
}

window.addEventListener('focus', resetTitle);
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        resetTitle();
    }
});

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function requestJson(url, options = {}) {
    const isFormData = options.body instanceof FormData;
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
            ...(options.headers ?? {}),
        },
        ...options,
    });

    const data = response.headers.get('content-type')?.includes('application/json')
        ? await response.json()
        : null;

    if (!response.ok) {
        const error = new Error(data?.message || `Request failed with status ${response.status}`);
        error.status = response.status;
        error.data = data;
        throw error;
    }

    return data;
}

function formatTime(value) {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
    });
}

function formatDateTime(value) {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleString([], {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function formatRelativeTime(value) {
    if (!value) {
        return '';
    }

    const diffMs = Date.now() - new Date(value).getTime();
    const diffMinutes = Math.max(0, Math.round(diffMs / 60000));

    if (diffMinutes < 1) {
        return 'just now';
    }

    if (diffMinutes === 1) {
        return '1 minute ago';
    }

    if (diffMinutes < 60) {
        return `${diffMinutes} minutes ago`;
    }

    const diffHours = Math.round(diffMinutes / 60);
    if (diffHours === 1) {
        return '1 hour ago';
    }

    if (diffHours < 24) {
        return `${diffHours} hours ago`;
    }

    const diffDays = Math.round(diffHours / 24);
    return diffDays === 1 ? '1 day ago' : `${diffDays} days ago`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function templateUrl(template, value) {
    return template.replace('__TOKEN__', value).replace('__ID__', value);
}

function readChatPageContext() {
    const source = document.querySelector('[data-chat-page-context]');
    const pageTitle = source?.dataset.pageTitle || document.title || null;
    const pageContext = {
        page_url: window.location.href,
        page_path: `${window.location.pathname}${window.location.search}`,
        page_title: pageTitle,
    };

    if (source?.dataset.pageType) {
        pageContext.page_type = source.dataset.pageType;
    }

    if (source?.dataset.productId) {
        pageContext.product_id = source.dataset.productId;
    }

    if (source?.dataset.productTitle) {
        pageContext.product_title = source.dataset.productTitle;
    }

    Object.keys(pageContext).forEach((key) => {
        if (pageContext[key] === null || pageContext[key] === '') {
            delete pageContext[key];
        }
    });

    return pageContext;
}

function applyChatPageContext(target, pageContext = readChatPageContext()) {
    Object.entries(pageContext).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') {
            return;
        }

        if (target instanceof FormData || target instanceof URLSearchParams) {
            target.append(key, value);
            return;
        }

        target[key] = value;
    });

    return target;
}

function buildContextUrl(url, pageContext = readChatPageContext()) {
    const requestUrl = new URL(url, window.location.origin);
    applyChatPageContext(requestUrl.searchParams, pageContext);
    return requestUrl.toString();
}

function readVisitorKey() {
    if (window.SwissMadeVisitorMonitor?.getVisitorKey) {
        return window.SwissMadeVisitorMonitor.getVisitorKey();
    }

    try {
        return window.localStorage.getItem('swissmade_visitor_key') || '';
    } catch (error) {
        return '';
    }
}

function applyVisitorIdentity(target) {
    const visitorKey = readVisitorKey();

    if (!visitorKey) {
        return target;
    }

    if (target instanceof FormData || target instanceof URLSearchParams) {
        target.append('visitor_key', visitorKey);
        return target;
    }

    target.visitor_key = visitorKey;

    return target;
}

function isScrolledNearBottom(container, threshold = 48) {
    if (!container) {
        return true;
    }

    return container.scrollHeight - container.scrollTop - container.clientHeight <= threshold;
}

function isRealtimeConnected() {
    return window.Echo?.connector?.pusher?.connection?.state === 'connected';
}

function ensureImageLightbox() {
    let lightbox = document.querySelector('[data-chat-image-lightbox]');

    if (lightbox) {
        return lightbox;
    }

    lightbox = document.createElement('div');
    lightbox.setAttribute('data-chat-image-lightbox', '1');
    lightbox.className = 'fixed inset-0 z-[120] hidden items-center justify-center bg-black/80 p-6';
    lightbox.innerHTML = `
        <button type="button" data-chat-image-close class="absolute right-4 top-4 rounded-full bg-white/10 p-3 text-white transition hover:bg-white/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>
        <img data-chat-image-preview class="max-h-full max-w-full rounded-3xl shadow-2xl" alt="">
    `;

    document.body.appendChild(lightbox);

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox || event.target.closest('[data-chat-image-close]')) {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
        }
    });

    return lightbox;
}

function openImageLightbox(url, alt = 'Chat image') {
    const lightbox = ensureImageLightbox();
    const preview = lightbox.querySelector('[data-chat-image-preview]');
    preview.src = url;
    preview.alt = alt;
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-chat-image-url]');

    if (!trigger) {
        return;
    }

    event.preventDefault();
    openImageLightbox(trigger.dataset.chatImageUrl, trigger.dataset.chatImageName || 'Chat image');
});

function messageMarkup(message, currentUserType = 'customer') {
    const senderType = message.sender_type;

    if (senderType === 'system') {
        return `
            <div data-message-id="${message.id}" class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-center text-xs leading-5 text-gray-600 shadow-sm">
                ${escapeHtml(message.message)}
            </div>
        `;
    }

    const isOwn = senderType === currentUserType;
    const wrapperClass = isOwn ? 'items-end' : 'items-start';
    const bubbleClass = isOwn
        ? 'bg-gray-900 text-white'
        : 'border border-gray-200 bg-white text-gray-900';
    let label = 'You';

    if (currentUserType === 'staff') {
        label = senderType === 'staff'
            ? 'You'
            : 'Customer';
    } else if (senderType === 'staff') {
        label = escapeHtml(message.user_name || 'Swiss Made Corp');
    }

    const attachmentMarkup = message.attachment
        ? `
            <div class="${message.message ? 'mt-3' : ''}">
                <button
                    type="button"
                    data-chat-image-url="${escapeHtml(message.attachment.url)}"
                    data-chat-image-name="${escapeHtml(message.attachment.name || 'Chat attachment')}"
                    class="block overflow-hidden rounded-2xl border border-black/10 bg-white/10 text-left"
                >
                    ${message.attachment.is_image
                        ? `<img src="${escapeHtml(message.attachment.url)}" alt="${escapeHtml(message.attachment.name || 'Chat attachment')}" class="max-h-56 w-full object-cover">`
                        : `<div class="px-4 py-3 text-sm">${escapeHtml(message.attachment.name || 'Attachment')}</div>`
                    }
                </button>
                <div class="mt-2 text-[11px] opacity-80">${escapeHtml(message.attachment.name || 'Attachment')}</div>
            </div>
        `
        : '';

    return `
        <div data-message-id="${message.id}" class="flex flex-col ${wrapperClass}">
            <div class="mb-1 text-[11px] text-gray-400">${label} • ${formatTime(message.created_at)}</div>
            <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-6 shadow-sm ${bubbleClass}">
                ${message.message ? escapeHtml(message.message).replace(/\n/g, '<br>') : ''}
                ${attachmentMarkup}
            </div>
        </div>
    `;
}

function initCustomerWidget(root) {
    if (root.dataset.bound === '1') {
        return;
    }

    root.dataset.bound = '1';

    const storageKey = root.dataset.storageKey;
    const panel = root.querySelector('[data-chat-panel]');
    const toggle = root.querySelector('[data-chat-toggle]');
    const close = root.querySelector('[data-chat-close]');
    const toggleCopy = root.querySelector('[data-customer-toggle-copy]');
    const statusBadge = root.querySelector('[data-customer-status-badge]');
    const prechatPane = root.querySelector('[data-prechat-pane]');
    const prechatCopy = root.querySelector('[data-prechat-copy]');
    const startForm = root.querySelector('[data-start-form]');
    const leaveForm = root.querySelector('[data-leave-email-form]');
    const conversationPane = root.querySelector('[data-conversation-pane]');
    const liveConversationBox = root.querySelector('[data-customer-live-conversation]');
    const alertBox = root.querySelector('[data-customer-alert]');
    const messagesBox = root.querySelector('[data-customer-messages]');
    const chatMeta = root.querySelector('[data-customer-chat-meta]');
    const typingBox = root.querySelector('[data-customer-typing]');
    const messageForm = root.querySelector('[data-customer-message-form]');
    const offlineFollowupBox = root.querySelector('[data-customer-offline-followup]');
    const offlineFollowupCopy = root.querySelector('[data-customer-offline-followup-copy]');
    const conversationLeaveEmailForm = root.querySelector('[data-customer-conversation-leave-email-form]');
    const startName = root.querySelector('[data-customer-name]');
    const startEmail = root.querySelector('[data-customer-email]');
    const startMessage = root.querySelector('[data-customer-start-message]');
    const leadName = root.querySelector('[data-lead-name]');
    const leadEmail = root.querySelector('[data-lead-email]');
    const leadMessage = root.querySelector('[data-lead-message]');
    const conversationLeadName = root.querySelector('[data-conversation-lead-name]');
    const conversationLeadEmail = root.querySelector('[data-conversation-lead-email]');
    const conversationLeadMessage = root.querySelector('[data-conversation-lead-message]');
    const messageInput = root.querySelector('[data-customer-message-input]');
    const attachmentInput = root.querySelector('[data-customer-attachment-input]');
    const attachmentName = root.querySelector('[data-customer-attachment-name]');

    let activeToken = window.localStorage.getItem(storageKey);
    let activeChat = null;
    let activeMessages = [];
    let activeTyping = defaultTypingState();
    let availabilityState = {
        available: true,
        available_agents: 0,
        offline_prompt: 'All chat specialists are currently unavailable. Leave your email and we will reach out as soon as possible.',
    };
    let subscribedChannel = null;
    let availabilityChannelBound = false;
    let customerRealtimeRetryTimer = null;
    let typingIdleTimer = null;
    let lastTypingState = false;
    let disconnectInFlight = false;
    let presenceTimer = null;

    function defaultTypingState() {
        return {
            customer: { is_typing: false, label: 'Customer is typing...' },
            staff: { is_typing: false, label: 'A specialist is typing...' },
        };
    }

    function resetConversationState() {
        if (subscribedChannel && window.Echo) {
            window.Echo.leave(subscribedChannel);
        }

        subscribedChannel = null;
        window.localStorage.removeItem(storageKey);
        activeToken = null;
        activeChat = null;
        activeMessages = [];
        activeTyping = defaultTypingState();
        lastTypingState = false;

        if (typingIdleTimer) {
            window.clearTimeout(typingIdleTimer);
            typingIdleTimer = null;
        }

        if (presenceTimer) {
            window.clearInterval(presenceTimer);
            presenceTimer = null;
        }

        if (attachmentInput) {
            attachmentInput.value = '';
        }

        if (messageInput) {
            messageInput.value = '';
        }

        updateAttachmentName();
        setAlert();
        toggleCopy.textContent = 'Questions about a watch?';
        showPrechat();
    }

    function sendDisconnectBeacon(token) {
        const url = templateUrl(root.dataset.disconnectUrlTemplate, token);
        const payload = new URLSearchParams();
        payload.set('_token', csrfToken());

        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, payload);
            return;
        }

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: payload.toString(),
            keepalive: true,
        }).catch(() => {});
    }

    function sendPresenceBeacon(token, online) {
        const url = templateUrl(root.dataset.presenceUrlTemplate, token);
        const payload = new URLSearchParams();
        payload.set('_token', csrfToken());
        payload.set('online', online ? '1' : '0');

        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, payload);
            return;
        }

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: payload.toString(),
            keepalive: true,
        }).catch(() => {});
    }

    async function syncCustomerPresence(online) {
        if (!activeToken || !activeChat || ['offline', 'closed'].includes(activeChat.status)) {
            return;
        }

        try {
            const data = await requestJson(templateUrl(root.dataset.presenceUrlTemplate, activeToken), {
                method: 'POST',
                body: JSON.stringify(applyChatPageContext({ online })),
            });

            if (data?.chat) {
                activeChat = { ...activeChat, ...data.chat };
            }
        } catch (error) {
            // Keep presence refresh quiet so it never interrupts the chat UI.
        }
    }

    function startCustomerPresenceHeartbeat() {
        if (presenceTimer || !activeToken) {
            return;
        }

        syncCustomerPresence(true);

        presenceTimer = window.setInterval(() => {
            syncCustomerPresence(true);
        }, 20000);
    }

    async function disconnectActiveChat(options = {}) {
        const { useBeacon = false, hidePanel = true } = options;
        const token = activeToken;

        if (!token) {
            if (hidePanel) {
                panel.classList.add('hidden');
            }
            return;
        }

        if (disconnectInFlight) {
            return;
        }

        disconnectInFlight = true;

        try {
            if (activeChat && !['offline', 'closed'].includes(activeChat.status)) {
                if (useBeacon) {
                    sendDisconnectBeacon(token);
                } else {
                    await requestJson(templateUrl(root.dataset.disconnectUrlTemplate, token), {
                        method: 'POST',
                        body: JSON.stringify({}),
                        keepalive: true,
                    });
                }
            }
        } catch (error) {
            // Keep local cleanup even if the unload-time request fails.
        } finally {
            resetConversationState();
            disconnectInFlight = false;

            if (hidePanel) {
                panel.classList.add('hidden');
            }

            fetchAvailability().catch(() => {});
        }
    }

    function updateAttachmentName() {
        attachmentName.textContent = attachmentInput?.files?.[0]?.name || '';
    }

    function setAlert(message = '', tone = 'amber') {
        if (!message) {
            alertBox.classList.add('hidden');
            alertBox.textContent = '';
            return;
        }

        alertBox.className = `mb-4 rounded-2xl border px-4 py-3 text-sm ${
            tone === 'red'
                ? 'border-red-200 bg-red-50 text-red-700'
                : 'border-amber-200 bg-amber-50 text-amber-800'
        }`;
        alertBox.textContent = message;
    }

    function setStatus(text, isAvailable = true) {
        statusBadge.textContent = text;
        statusBadge.className = `mt-1 inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium ${
            isAvailable ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'
        }`;
    }

    function showPrechat() {
        prechatPane.classList.remove('hidden');
        conversationPane.classList.add('hidden');
    }

    function showConversation() {
        prechatPane.classList.add('hidden');
        conversationPane.classList.remove('hidden');
    }

    function conversationHasLiveCoverage() {
        if (!activeChat) {
            return availabilityState.available;
        }

        if (typeof activeChat.live_chat_available === 'boolean') {
            return activeChat.live_chat_available;
        }

        if (activeChat.assigned_user) {
            return activeChat.assigned_user_available !== false || availabilityState.available;
        }

        return availabilityState.available;
    }

    function shouldUseConversationEmailFallback() {
        return !!activeChat
            && activeChat.status !== 'offline'
            && !conversationHasLiveCoverage();
    }

    function applyAvailabilityState(data = {}) {
        availabilityState = {
            available: Boolean(data.available),
            available_agents: Number(data.available_agents || 0),
            offline_prompt: data.offline_prompt || availabilityState.offline_prompt,
        };

        if (availabilityState.available) {
            setStatus(`${availabilityState.available_agents} specialist${availabilityState.available_agents === 1 ? '' : 's'} online`, true);
            prechatCopy.textContent = 'Start a one-to-one conversation with one of our available watch specialists.';
            startForm.classList.remove('hidden');
            leaveForm.classList.add('hidden');
            return;
        }

        setStatus('Currently away', false);
        prechatCopy.textContent = availabilityState.offline_prompt;
        startForm.classList.add('hidden');
        leaveForm.classList.remove('hidden');
    }

    function syncConversationLeadFields() {
        if (!conversationLeadName || !conversationLeadEmail || !conversationLeadMessage) {
            return;
        }

        if (!conversationLeadName.value) {
            conversationLeadName.value = activeChat?.visitor_name || startName.value.trim() || leadName.value.trim();
        }

        if (!conversationLeadEmail.value) {
            conversationLeadEmail.value = activeChat?.visitor_email || startEmail.value.trim() || leadEmail.value.trim();
        }
    }

    function renderConversationActionState() {
        const isOfflineLead = activeChat?.status === 'offline';
        const useEmailFallback = shouldUseConversationEmailFallback();
        const shouldShowEmailOnly = useEmailFallback || isOfflineLead;

        liveConversationBox?.classList.toggle('hidden', shouldShowEmailOnly);
        messageForm?.classList.toggle('hidden', shouldShowEmailOnly);
        offlineFollowupBox?.classList.toggle('hidden', !shouldShowEmailOnly);
        typingBox?.classList.toggle('hidden', shouldShowEmailOnly || !activeTyping?.staff?.is_typing);

        if (isOfflineLead) {
            offlineFollowupCopy.textContent = 'Thank you. Your email has been saved and our team will follow up shortly.';
            conversationLeaveEmailForm?.classList.add('hidden');
            return;
        }

        conversationLeaveEmailForm?.classList.remove('hidden');

        if (useEmailFallback) {
            offlineFollowupCopy.textContent = availabilityState.offline_prompt || 'No specialist is available right now. Leave your email and we will follow up shortly.';
            syncConversationLeadFields();
            syncTypingState(false);
        }
    }

    function scrollMessagesToBottom() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function mergeMessage(message) {
        if (!message?.id || activeMessages.some((item) => item.id === message.id)) {
            return false;
        }

        activeMessages.push(message);
        return true;
    }

    function renderMessages(options = {}) {
        const shouldScroll = options.forceScroll || isScrolledNearBottom(messagesBox);

        messagesBox.innerHTML = activeMessages
            .map((message) => messageMarkup(message, 'customer'))
            .join('');

        if (shouldScroll) {
            scrollMessagesToBottom();
        }
    }

    function renderConversationMeta() {
        const assignedName = activeChat?.assigned_user?.name;
        const assignedSpecialistUnavailable = !!activeChat?.assigned_user
            && activeChat.assigned_user_available === false
            && conversationHasLiveCoverage();
        const heading = shouldUseConversationEmailFallback()
            ? 'Specialists are currently unavailable'
            : assignedSpecialistUnavailable
            ? 'Waiting for another available specialist'
            : assignedName
            ? `Connected with ${assignedName}`
            : 'Waiting for an available specialist';
        const secondary = shouldUseConversationEmailFallback()
            ? 'Leave your email below and we will follow up as soon as a specialist is available.'
            : assignedSpecialistUnavailable
            ? `${assignedName} is unavailable right now. Another specialist can continue this chat.`
            : activeChat?.last_message_at
            ? `Last update ${formatDateTime(activeChat.last_message_at)}`
            : 'Your chat is ready.';

        chatMeta.innerHTML = `
            <div class="font-semibold text-gray-900">${escapeHtml(heading)}</div>
            <div class="mt-1">${escapeHtml(secondary)}</div>
        `;

        toggleCopy.textContent = shouldUseConversationEmailFallback()
            ? 'Email follow-up available'
            : assignedSpecialistUnavailable
            ? 'Waiting for another specialist'
            : assignedName ? `Connected to ${assignedName}` : 'Chat in progress';
    }

    function renderTypingIndicator() {
        if (shouldUseConversationEmailFallback()) {
            typingBox.textContent = '';
            typingBox.classList.add('hidden');
            return;
        }

        if (activeTyping?.staff?.is_typing) {
            typingBox.textContent = activeTyping.staff.label || 'A specialist is typing...';
            typingBox.classList.remove('hidden');
            return;
        }

        typingBox.textContent = '';
        typingBox.classList.add('hidden');
    }

    function renderConversation(options = {}) {
        if (!activeChat) {
            return;
        }

        showConversation();
        renderConversationMeta();
        renderMessages(options);
        renderConversationActionState();

        if (activeChat.status === 'offline') {
            setStatus('Email follow-up requested', false);
        } else if (shouldUseConversationEmailFallback()) {
            setStatus('Currently away', false);
        } else if (activeChat.assigned_user && activeChat.assigned_user_available === false) {
            setStatus('Waiting for another specialist...', true);
        } else if (activeChat.assigned_user) {
            setStatus(`Connected with ${activeChat.assigned_user.name}`, true);
        } else {
            setStatus('Waiting for a specialist...', true);
        }

        renderTypingIndicator();
    }

    async function fetchAvailability() {
        const data = await requestJson(root.dataset.availabilityUrl, { method: 'GET' });
        applyAvailabilityState(data);

        if (activeChat) {
            renderConversation();
        }

        return data;
    }

    function subscribeToCustomerChannel(token) {
        if (!window.Echo || !token) {
            return false;
        }

        const channelName = `customer-chat.${token}`;
        if (subscribedChannel === channelName) {
            return true;
        }

        if (subscribedChannel) {
            window.Echo.leave(subscribedChannel);
        }

        subscribedChannel = channelName;

        window.Echo.channel(channelName).listen('.customer-chat.updated', (payload) => {
            if (payload.chat) {
                if (payload.chat.status === 'closed') {
                    resetConversationState();
                    panel.classList.add('hidden');
                    fetchAvailability().catch(() => {});
                    return;
                }

                activeChat = payload.chat;
            }

            if (payload.typing) {
                activeTyping = payload.typing;
            }

            let hasNewMessage = false;

            if (Array.isArray(payload.messages)) {
                payload.messages.forEach((message) => {
                    hasNewMessage = mergeMessage(message) || hasNewMessage;
                });
            }

            if (payload.message) {
                hasNewMessage = mergeMessage(payload.message) || hasNewMessage;
            }

            renderConversation({ forceScroll: hasNewMessage });

            if (payload.message && payload.message.sender_type !== 'customer') {
                flashTitle('New Chat Reply');
                panel.classList.remove('hidden');
            }
        });

        return true;
    }

    function subscribeToAvailabilityChannel() {
        if (!window.Echo || availabilityChannelBound) {
            return availabilityChannelBound;
        }

        availabilityChannelBound = true;

        window.Echo.channel('customer-chat.availability').listen('.customer-chat.updated', async (payload) => {
            if (payload?.type !== 'availability.updated') {
                return;
            }

            if (payload.availability) {
                applyAvailabilityState(payload.availability);
            }

            if (activeChat?.assigned_user?.id === payload.user_id) {
                activeChat = {
                    ...activeChat,
                    assigned_user_available: Boolean(payload.available),
                    live_chat_available: Boolean(payload.available) || Boolean(payload.availability?.available),
                };
            }

            if (activeChat) {
                renderConversation();
            }

            if (!payload.availability && !activeChat) {
                fetchAvailability().catch(() => {});
            }
        });

        return true;
    }

    function ensureCustomerRealtimeSubscriptions() {
        const availabilityReady = subscribeToAvailabilityChannel();
        const chatReady = !activeToken || subscribeToCustomerChannel(activeToken);

        if (availabilityReady && chatReady) {
            if (customerRealtimeRetryTimer) {
                window.clearInterval(customerRealtimeRetryTimer);
                customerRealtimeRetryTimer = null;
            }

            return;
        }

        if (customerRealtimeRetryTimer) {
            return;
        }

        customerRealtimeRetryTimer = window.setInterval(() => {
            const retryAvailabilityReady = subscribeToAvailabilityChannel();
            const retryChatReady = !activeToken || subscribeToCustomerChannel(activeToken);

            if (retryAvailabilityReady && retryChatReady) {
                window.clearInterval(customerRealtimeRetryTimer);
                customerRealtimeRetryTimer = null;
            }
        }, 1000);
    }

    async function loadExistingConversation(options = {}) {
        if (!activeToken) {
            return;
        }

        try {
            const data = await requestJson(buildContextUrl(templateUrl(root.dataset.showUrlTemplate, activeToken)), {
                method: 'GET',
            });

            activeChat = data.chat;
            if (activeChat?.status === 'closed') {
                resetConversationState();
                panel.classList.add('hidden');
                await fetchAvailability().catch(() => {});
                return;
            }
            activeMessages = Array.isArray(data.messages) ? data.messages : [];
            activeTyping = data.typing ?? activeTyping;
            subscribeToCustomerChannel(activeChat.public_token);
            ensureCustomerRealtimeSubscriptions();
            startCustomerPresenceHeartbeat();
            renderConversation({ forceScroll: !options.silent });
        } catch (error) {
            if (!options.silent || error.status === 404) {
                resetConversationState();
            }
        }
    }

    async function syncTypingState(isTyping) {
        if (!activeToken || lastTypingState === isTyping) {
            return;
        }

        lastTypingState = isTyping;

        try {
            await requestJson(templateUrl(root.dataset.typingUrlTemplate, activeToken), {
                method: 'POST',
                body: JSON.stringify({ typing: isTyping }),
            });
        } catch (error) {
            lastTypingState = false;
        }
    }

    toggle.addEventListener('click', async () => {
        panel.classList.toggle('hidden');

        if (!panel.classList.contains('hidden')) {
            setAlert();

            if (activeChat) {
                renderConversation();
                return;
            }

            showPrechat();
            await fetchAvailability();
        }
    });

    close.addEventListener('click', async () => {
        await disconnectActiveChat({ hidePanel: true });
    });

    window.addEventListener('pagehide', () => {
        if (activeToken && activeChat && !['offline', 'closed'].includes(activeChat.status)) {
            sendPresenceBeacon(activeToken, false);
        }
    });

    startForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setAlert();

        try {
            const payload = applyVisitorIdentity(applyChatPageContext({
                visitor_name: startName.value.trim(),
                visitor_email: startEmail.value.trim(),
                message: startMessage.value.trim(),
            }));
            const data = await requestJson(root.dataset.createUrl, {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            activeChat = data.chat;
            activeMessages = Array.isArray(data.messages) ? data.messages : [];
            activeToken = activeChat.public_token;
            activeTyping = data.typing ?? activeTyping;
            window.localStorage.setItem(storageKey, activeToken);
            subscribeToCustomerChannel(activeToken);
            ensureCustomerRealtimeSubscriptions();
            startCustomerPresenceHeartbeat();
            startForm.reset();
            renderConversation({ forceScroll: true });
        } catch (error) {
            if (error.status === 409) {
                await fetchAvailability();
                setAlert('No specialists are available right now. You can leave your email instead.');
                return;
            }

            setAlert('Unable to start chat right now. Please try again.', 'red');
        }
    });

    leaveForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setAlert();

        try {
            const payload = applyVisitorIdentity(applyChatPageContext({
                visitor_name: leadName.value.trim(),
                visitor_email: leadEmail.value.trim(),
                message: leadMessage.value.trim(),
            }));
            await requestJson(root.dataset.leaveEmailUrl, {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            leaveForm.reset();
            setAlert('Thank you. Your email was saved and our team will follow up soon.');
        } catch (error) {
            setAlert('We could not save your email yet. Please try again.', 'red');
        }
    });

    conversationLeaveEmailForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        setAlert();

        if (!activeToken) {
            return;
        }

        try {
            const payload = applyVisitorIdentity(applyChatPageContext({
                visitor_name: conversationLeadName.value.trim(),
                visitor_email: conversationLeadEmail.value.trim(),
                message: conversationLeadMessage.value.trim(),
            }));

            const data = await requestJson(templateUrl(root.dataset.conversationLeaveEmailUrlTemplate, activeToken), {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            activeChat = data.chat;
            activeMessages = Array.isArray(data.messages) ? data.messages : activeMessages;
            activeTyping = data.typing ?? activeTyping;
            conversationLeaveEmailForm.reset();
            renderConversation({ forceScroll: true });
            setAlert('Thank you. We will follow up with you by email shortly.');
        } catch (error) {
            if (error.status === 409) {
                setAlert('A specialist joined this chat while you were submitting your email. You can continue chatting live.', 'amber');
                await loadExistingConversation();
                return;
            }

            setAlert('We could not save your email yet. Please try again.', 'red');
        }
    });

    messageForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!activeChat || !activeToken) {
            return;
        }

        const body = messageInput.value.trim();
        if (!body && !attachmentInput?.files?.[0]) {
            return;
        }

        try {
            await syncTypingState(false);
            const payload = new FormData();
            if (body) {
                payload.append('message', body);
            }
            if (attachmentInput?.files?.[0]) {
                payload.append('attachment', attachmentInput.files[0]);
            }
            applyChatPageContext(payload);

            const data = await requestJson(templateUrl(root.dataset.messageUrlTemplate, activeToken), {
                method: 'POST',
                body: payload,
            });

            messageInput.value = '';
            if (attachmentInput) {
                attachmentInput.value = '';
            }
            updateAttachmentName();
            if (typingIdleTimer) {
                window.clearTimeout(typingIdleTimer);
                typingIdleTimer = null;
            }
            if (data.chat) {
                activeChat = data.chat;
            }
            if (data.message) {
                mergeMessage(data.message);
            }
            renderConversation({ forceScroll: true });
        } catch (error) {
            if (error.status === 409) {
                await fetchAvailability().catch(() => {});
                await loadExistingConversation({ silent: true }).catch(() => {});
                setAlert('Specialists are currently unavailable. Please leave your email instead.', 'amber');
                return;
            }

            setAlert('We could not send your message. Please try again.', 'red');
        }
    });

    messageInput.addEventListener('input', () => {
        if (!activeToken) {
            return;
        }

        const hasValue = messageInput.value.trim().length > 0;
        syncTypingState(hasValue);

        if (typingIdleTimer) {
            window.clearTimeout(typingIdleTimer);
        }

        typingIdleTimer = window.setTimeout(() => {
            if (!messageInput.value.trim()) {
                syncTypingState(false);
                return;
            }

            syncTypingState(false);
        }, 1800);
    });

    messageInput.addEventListener('blur', () => {
        syncTypingState(false);
    });

    attachmentInput?.addEventListener('change', updateAttachmentName);

    ensureCustomerRealtimeSubscriptions();
    loadExistingConversation();
}

function initStaffWidget(root) {
    if (root.dataset.bound === '1') {
        return;
    }

    root.dataset.bound = '1';

    const currentUserId = Number(root.dataset.userId);
    const storageKey = root.dataset.storageKey || 'swissmade_staff_chat_state';
    const panel = root.querySelector('[data-staff-panel]');
    const toggle = root.querySelector('[data-staff-toggle]');
    const dragHandle = root.querySelector('[data-staff-drag-handle]') || toggle;
    const close = root.querySelector('[data-staff-close]');
    const availabilityToggle = root.querySelector('[data-staff-availability-toggle]');
    const badge = root.querySelector('[data-staff-badge]');
    const alertBox = root.querySelector('[data-staff-alert]');
    const list = root.querySelector('[data-staff-chat-list]');
    const empty = root.querySelector('[data-staff-empty]');
    const conversation = root.querySelector('[data-staff-conversation]');
    const title = root.querySelector('[data-staff-active-title]');
    const subtitle = root.querySelector('[data-staff-active-subtitle]');
    const meta = root.querySelector('[data-staff-active-meta]');
    const contextBox = root.querySelector('[data-staff-active-context]');
    const note = root.querySelector('[data-staff-active-note]');
    const typingNote = root.querySelector('[data-staff-typing-note]');
    const claimButton = root.querySelector('[data-staff-claim]');
    const messages = root.querySelector('[data-staff-messages]');
    const form = root.querySelector('[data-staff-message-form]');
    const input = root.querySelector('[data-staff-message-input]');
    const sendButton = root.querySelector('[data-staff-send-button]');
    const quickReplySelect = root.querySelector('[data-staff-quick-reply]');
    const quickReplyManageToggle = root.querySelector('[data-staff-quick-reply-manage-toggle]');
    const quickReplyManager = root.querySelector('[data-staff-quick-reply-manager]');
    const quickReplyManagerClose = root.querySelector('[data-staff-quick-reply-manager-close]');
    const quickReplyLabelInput = root.querySelector('[data-staff-quick-reply-label]');
    const quickReplyMessageInput = root.querySelector('[data-staff-quick-reply-message]');
    const quickReplySaveButton = root.querySelector('[data-staff-quick-reply-save]');
    const quickReplyFillButton = root.querySelector('[data-staff-quick-reply-fill]');
    const quickReplyList = root.querySelector('[data-staff-quick-reply-list]');
    const attachmentInput = root.querySelector('[data-staff-attachment-input]');
    const attachmentName = root.querySelector('[data-staff-attachment-name]');

    let chats = [];
    let activeChat = null;
    let activeMessages = [];
    let quickReplies = [];
    let activeTyping = {
        customer: { is_typing: false, label: 'Customer is typing...' },
        staff: { is_typing: false, label: 'A specialist is typing...' },
    };
    let seenActivity = new Map();
    let typingIdleTimer = null;
    let lastTypingState = false;
    let isSpecialistAvailable = true;
    let dismissed = window.localStorage.getItem(`${storageKey}:dismissed`) === '1';
    let restoredSelection = Number(window.localStorage.getItem(`${storageKey}:chatId`)) || null;
    let dragState = null;
    let heartbeatInFlight = false;
    let heartbeatTimer = null;
    let staffRealtimeRetryTimer = null;
    let chatsRequestInFlight = false;
    let activeChatRequestId = null;
    let staffChannelsBound = false;

    function setStaffAlert(message = '', tone = 'red') {
        if (!message) {
            alertBox.classList.add('hidden');
            alertBox.textContent = '';
            return;
        }

        alertBox.className = `border-b px-5 py-3 text-sm ${
            tone === 'amber'
                ? 'border-amber-200 bg-amber-50 text-amber-800'
                : 'border-red-200 bg-red-50 text-red-700'
        }`;
        alertBox.textContent = message;
    }

    function errorMessage(error, fallback) {
        return error?.data?.message || error?.message || fallback;
    }

    function updateAttachmentName() {
        attachmentName.textContent = attachmentInput?.files?.[0]?.name || '';
    }

    function launcherDimensions() {
        const rect = toggle.getBoundingClientRect();

        return {
            width: rect.width || 48,
            height: rect.height || 48,
        };
    }

    function clampLauncherPosition(left, top) {
        const rect = launcherDimensions();
        const margin = 16;
        const maxLeft = Math.max(margin, window.innerWidth - rect.width - margin);
        const maxTop = Math.max(margin, window.innerHeight - rect.height - margin);

        return {
            left: Math.min(Math.max(left, margin), maxLeft),
            top: Math.min(Math.max(top, margin), maxTop),
        };
    }

    function buildSavedLauncherPosition(left, top) {
        const clamped = clampLauncherPosition(left, top);
        const rect = launcherDimensions();
        const distanceLeft = clamped.left;
        const distanceTop = clamped.top;
        const distanceRight = Math.max(16, window.innerWidth - clamped.left - rect.width);
        const distanceBottom = Math.max(16, window.innerHeight - clamped.top - rect.height);
        const anchorX = distanceRight < distanceLeft ? 'right' : 'left';
        const anchorY = distanceBottom < distanceTop ? 'bottom' : 'top';

        return {
            anchorX,
            anchorY,
            offsetX: anchorX === 'right' ? distanceRight : distanceLeft,
            offsetY: anchorY === 'bottom' ? distanceBottom : distanceTop,
        };
    }

    function applySavedLauncherPosition(position, shouldSave = true) {
        const rect = launcherDimensions();
        const left = position.anchorX === 'right'
            ? window.innerWidth - rect.width - position.offsetX
            : position.offsetX;
        const top = position.anchorY === 'bottom'
            ? window.innerHeight - rect.height - position.offsetY
            : position.offsetY;
        const clamped = clampLauncherPosition(left, top);
        root.style.left = `${clamped.left}px`;
        root.style.top = `${clamped.top}px`;
        root.style.right = 'auto';
        root.style.bottom = 'auto';
        updatePanelPlacement();

        if (shouldSave) {
            window.localStorage.setItem(`${storageKey}:position`, JSON.stringify(buildSavedLauncherPosition(clamped.left, clamped.top)));
        }
    }

    function setLauncherPosition(left, top, shouldSave = true) {
        applySavedLauncherPosition(buildSavedLauncherPosition(left, top), shouldSave);
    }

    function restoreLauncherPosition() {
        const saved = window.localStorage.getItem(`${storageKey}:position`);

        if (!saved) {
            return;
        }

        try {
            const parsed = JSON.parse(saved);

            if (typeof parsed?.anchorX === 'string' && typeof parsed?.anchorY === 'string') {
                applySavedLauncherPosition(parsed, false);
                return;
            }

            if (typeof parsed?.left !== 'number' || typeof parsed?.top !== 'number') {
                return;
            }

            setLauncherPosition(parsed.left, parsed.top, false);
        } catch (error) {
            window.localStorage.removeItem(`${storageKey}:position`);
        }
    }

    function renderAvailabilityToggle() {
        if (!availabilityToggle) {
            return;
        }

        availabilityToggle.textContent = isSpecialistAvailable ? 'Available' : 'Currently unavailable';
        availabilityToggle.className = isSpecialistAvailable
            ? 'rounded-full bg-green-100 px-3 py-2 text-xs font-semibold text-green-700 transition hover:bg-green-200'
            : 'rounded-full bg-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-300';
    }

    function setQuickReplyManagerOpen(isOpen) {
        if (!quickReplyManager || !quickReplyManageToggle) {
            return;
        }

        quickReplyManager.classList.toggle('hidden', !isOpen);
        quickReplyManageToggle.textContent = isOpen ? 'Close' : 'Manage';
    }

    function resetQuickReplyForm() {
        if (quickReplyLabelInput) {
            quickReplyLabelInput.value = '';
        }

        if (quickReplyMessageInput) {
            quickReplyMessageInput.value = '';
        }
    }

    function applyQuickReply(reply) {
        if (!reply) {
            return;
        }

        input.value = reply.message;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
    }

    function renderQuickReplyManager() {
        if (!quickReplyList) {
            return;
        }

        if (quickReplies.length === 0) {
            quickReplyList.innerHTML = '<div class="text-xs text-gray-500">No quick replies saved yet.</div>';
            return;
        }

        quickReplyList.innerHTML = quickReplies.map((reply) => `
            <div class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 py-2">
                <button
                    type="button"
                    data-staff-quick-reply-use="${reply.id}"
                    class="min-w-0 flex-1 truncate text-left text-xs font-semibold text-gray-900 transition hover:text-red-800"
                >
                    ${escapeHtml(reply.label)}
                </button>
                <button
                    type="button"
                    data-staff-quick-reply-delete="${reply.id}"
                    class="text-[11px] font-semibold text-red-700 transition hover:text-red-800"
                >
                    Delete
                </button>
            </div>
        `).join('');

        quickReplyList.querySelectorAll('[data-staff-quick-reply-use]').forEach((button) => {
            button.addEventListener('click', () => {
                const selected = quickReplies.find((reply) => String(reply.id) === button.dataset.staffQuickReplyUse);
                applyQuickReply(selected);
                setQuickReplyManagerOpen(false);
            });
        });

        quickReplyList.querySelectorAll('[data-staff-quick-reply-delete]').forEach((button) => {
            button.addEventListener('click', async () => {
                const replyId = button.dataset.staffQuickReplyDelete;

                try {
                    const data = await requestJson(templateUrl(root.dataset.quickReplyDestroyUrlTemplate, replyId), {
                        method: 'DELETE',
                    });

                    quickReplies = Array.isArray(data.quick_replies) ? data.quick_replies : [];
                    if (quickReplySelect && quickReplySelect.value === String(replyId)) {
                        quickReplySelect.value = '';
                    }
                    renderQuickReplies();
                    setQuickReplyManagerOpen(false);
                    setStaffAlert('Quick reply deleted.', 'amber');
                } catch (error) {
                    setStaffAlert(errorMessage(error, 'Could not delete this quick reply.'));
                }
            });
        });
    }

    function renderQuickReplies() {
        if (!quickReplySelect) {
            return;
        }

        const currentValue = quickReplySelect.value;
        quickReplySelect.innerHTML = `
            <option value="">Select a quick reply...</option>
            ${quickReplies.map((reply) => `
                <option value="${reply.id}">${escapeHtml(reply.label)}</option>
            `).join('')}
        `;

        if (quickReplies.some((reply) => String(reply.id) === currentValue)) {
            quickReplySelect.value = currentValue;
        }

        renderQuickReplyManager();
    }

    function shouldRestoreOpenPanel() {
        return !dismissed && (window.localStorage.getItem(`${storageKey}:open`) === '1' || restoredSelection !== null);
    }

    function updatePanelPlacement() {
        if (!panel || !toggle) {
            return;
        }

        const launcherRect = toggle.getBoundingClientRect();
        const openLeft = launcherRect.left + launcherRect.width / 2 > window.innerWidth / 2;
        const openUp = launcherRect.top + launcherRect.height / 2 > window.innerHeight / 2;

        panel.style.maxHeight = 'calc(100vh - 0.75rem)';
        panel.style.left = openLeft ? 'auto' : '0';
        panel.style.right = openLeft ? '0' : 'auto';
        panel.style.top = openUp ? 'auto' : 'calc(100% + 0.75rem)';
        panel.style.bottom = openUp ? 'calc(100% + 0.75rem)' : 'auto';
        panel.style.transformOrigin = `${openLeft ? 'right' : 'left'} ${openUp ? 'bottom' : 'top'}`;
    }

    function persistState({
        open = !panel.classList.contains('hidden'),
        chatId = activeChat?.id ?? restoredSelection,
        dismissedState = dismissed,
    } = {}) {
        window.localStorage.setItem(`${storageKey}:open`, open ? '1' : '0');
        window.localStorage.setItem(`${storageKey}:dismissed`, dismissedState ? '1' : '0');
        dismissed = dismissedState;

        if (chatId) {
            window.localStorage.setItem(`${storageKey}:chatId`, String(chatId));
            restoredSelection = Number(chatId);
            return;
        }

        window.localStorage.removeItem(`${storageKey}:chatId`);
        restoredSelection = null;
    }

    function openPanel({ chatId = activeChat?.id ?? restoredSelection, manual = false } = {}) {
        updatePanelPlacement();
        panel.classList.remove('hidden');
        persistState({
            open: true,
            chatId,
            dismissedState: manual ? false : dismissed,
        });
    }

    function closePanel({ manual = false } = {}) {
        panel.classList.add('hidden');
        persistState({
            open: false,
            dismissedState: manual ? true : dismissed,
        });
    }

    function canClaimStaffChat(chat) {
        if (!chat || typeof chat !== 'object' || chat.id == null || chat.status === 'offline') {
            return false;
        }

        if (chat.status === 'waiting') {
            return true;
        }

        return Boolean(chat.can_be_claimed) && chat.assigned_user?.id !== currentUserId;
    }

    function shouldKeepStaffChatVisible(chat) {
        if (!chat || typeof chat !== 'object' || chat.id == null || chat.status === 'closed') {
            return false;
        }

        if (chat.status === 'offline') {
            return true;
        }

        if (chat.is_new_request || chat.needs_staff_reply) {
            return true;
        }

        if (chat.status === 'waiting' || chat.status === 'active') {
            return Boolean(chat.customer_is_online);
        }

        return true;
    }

    function visibleChats() {
        return chats.filter((chat) =>
            chat
            && typeof chat === 'object'
            && chat.id != null
            && chat.status !== 'closed'
            && shouldKeepStaffChatVisible(chat)
            && (
            chat.status === 'waiting'
            || chat.status === 'offline'
            || chat.assigned_user?.id === currentUserId
            || canClaimStaffChat(chat)
            || chat.id === activeChat?.id
            )
        );
    }

    function syncActiveChatVisibility() {
        if (!activeChat) {
            return;
        }

        const activeChatId = activeChat?.id;

        if (activeChatId != null && visibleChats().some((chat) => chat.id === activeChatId)) {
            return;
        }

        activeChat = null;
        activeMessages = [];
        persistState({
            open: !panel.classList.contains('hidden'),
            chatId: null,
            dismissedState: dismissed,
        });
    }

    function updateBadge() {
        const waitingCount = visibleChats().length;

        if (waitingCount > 0) {
            badge.textContent = waitingCount;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
            badge.textContent = '';
        }
    }

    function sortChats() {
        chats = chats.slice().sort((left, right) => {
            const leftPriority = chatPriority(left);
            const rightPriority = chatPriority(right);

            if (leftPriority !== rightPriority) {
                return leftPriority - rightPriority;
            }
            return new Date(right.last_message_at || 0) - new Date(left.last_message_at || 0);
        });
    }

    function chatPriority(chat) {
        if (chat.is_new_request) {
            return 0;
        }

        if (chat.needs_staff_reply) {
            return 1;
        }

        if (chat.status === 'active') {
            return 2;
        }

        if (chat.status === 'offline') {
            return 3;
        }

        return 4;
    }

    function upsertChat(chat) {
        if (!chat || typeof chat !== 'object' || chat.id == null) {
            return;
        }

        const keep = chat.status !== 'closed' && shouldKeepStaffChatVisible(chat) && (
            chat.status === 'waiting'
            || chat.status === 'offline'
            || chat.assigned_user?.id === currentUserId
            || canClaimStaffChat(chat)
            || chat.id === activeChat?.id
        );
        const index = chats.findIndex((item) => item?.id === chat.id);

        if (!keep) {
            if (index >= 0) {
                chats.splice(index, 1);
            }

            if (activeChat?.id === chat.id) {
                activeChat = null;
                activeMessages = [];
            }

            return;
        }

        if (index >= 0) {
            chats[index] = { ...chats[index], ...chat };
        } else {
            chats.push(chat);
        }

        sortChats();
        updateBadge();
    }

    function mergeStaffMessage(message) {
        if (!message?.id || activeMessages.some((item) => item.id === message.id)) {
            return false;
        }

        activeMessages.push(message);
        return true;
    }

    function renderTypingNote() {
        if (activeTyping?.customer?.is_typing) {
            typingNote.textContent = activeTyping.customer.label || 'Customer is typing...';
            typingNote.classList.remove('hidden');
            return;
        }

        typingNote.textContent = '';
        typingNote.classList.add('hidden');
    }

    function renderList() {
        const items = visibleChats();

        list.innerHTML = items.length
            ? items.map((chat) => {
                const activeClass = activeChat?.id === chat.id ? 'border-red-200 bg-red-50' : 'border-transparent bg-white';
                const name = chat.visitor_name || chat.visitor_email || `Visitor #${chat.id}`;
                const badgeLabel = chat.is_new_request
                    ? '<span class="rounded-full bg-red-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-red-700">New Chat</span>'
                    : canClaimStaffChat(chat) && chat.status !== 'waiting'
                        ? '<span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Take Over</span>'
                    : chat.needs_staff_reply
                        ? '<span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Reply Needed</span>'
                    : chat.status === 'active' && !chat.customer_is_online
                        ? '<span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700">Left Chat</span>'
                    : chat.status === 'offline'
                        ? '<span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700">Email</span>'
                        : '<span class="rounded-full bg-green-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-green-700">Ongoing</span>';
                const secondary = chat.visitor_email
                    ? `<p class="mt-1 truncate text-[11px] text-gray-400">${escapeHtml(chat.visitor_email)}</p>`
                    : '';

                return `
                    <button type="button" data-chat-id="${chat.id}" class="mb-2 block w-full rounded-2xl border ${activeClass} px-3 py-3 text-left shadow-sm transition hover:border-red-200 hover:bg-red-50">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-semibold text-gray-900">${escapeHtml(name)}</span>
                            ${badgeLabel}
                        </div>
                        ${secondary}
                        <p class="mt-2 line-clamp-2 text-xs leading-5 text-gray-500">${escapeHtml(chat.last_message_preview || 'No messages yet')}</p>
                    </button>
                `;
            }).join('')
            : '<div class="px-3 py-4 text-sm text-gray-500">No active chats.</div>';

        list.querySelectorAll('[data-chat-id]').forEach((button) => {
            button.addEventListener('click', () => {
                loadChat(button.dataset.chatId, { forceScroll: true });
            });
        });
    }

    function renderActiveChat(options = {}) {
        const shouldScroll = options.forceScroll || isScrolledNearBottom(messages);

        updateBadge();
        renderList();
        renderAvailabilityToggle();

        if (!activeChat) {
            empty.classList.remove('hidden');
            conversation.classList.add('hidden');
            claimButton.classList.add('hidden');
            title.textContent = 'Select a chat';
            subtitle.textContent = 'Waiting chats and your active conversations appear on the left.';
            meta.classList.add('hidden');
            meta.innerHTML = '';
            contextBox.classList.add('hidden');
            contextBox.innerHTML = '';
            note.classList.add('hidden');
            note.textContent = '';
            typingNote.classList.add('hidden');
            typingNote.textContent = '';
            return;
        }

        const isOfflineLead = activeChat.status === 'offline';
        const isWaiting = activeChat.status === 'waiting';
        const canClaim = canClaimStaffChat(activeChat);
        const isTakeover = canClaim && !isWaiting;
        const contactBits = [];
        const contextBits = [];
        const pageContext = activeChat.page_context || null;

        if (activeChat.visitor_email) {
            contactBits.push(`
                <span class="rounded-full bg-gray-100 px-2.5 py-1">
                    Email: ${escapeHtml(activeChat.visitor_email)}
                </span>
            `);
        }

        if (activeChat.customer_is_online) {
            contactBits.push('<span class="rounded-full bg-green-100 px-2.5 py-1 text-green-700">Customer online</span>');
        } else if (activeChat.customer_last_seen_at) {
            contactBits.push(`
                <span class="rounded-full bg-gray-100 px-2.5 py-1">
                    Last seen ${escapeHtml(formatRelativeTime(activeChat.customer_last_seen_at))}
                </span>
            `);
        }

        if (pageContext?.page_path || pageContext?.page_title) {
            contextBits.push(`
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-blue-700">Current Page</div>
                    <div class="mt-1 font-medium text-gray-900">${escapeHtml(pageContext.page_title || pageContext.page_path || 'Unknown page')}</div>
                    ${pageContext.page_path ? `<div class="mt-1 break-all text-[11px] text-gray-600">${escapeHtml(pageContext.page_path)}</div>` : ''}
                </div>
            `);
        }

        empty.classList.add('hidden');
        conversation.classList.remove('hidden');
        title.textContent = activeChat.visitor_name || activeChat.visitor_email || `Visitor #${activeChat.id ?? ''}`;
        subtitle.textContent = activeChat.is_new_request
            ? 'Brand new chat request from the customer.'
            : activeChat.needs_staff_reply
                ? 'Customer sent a new message and is waiting for your reply.'
            : isOfflineLead
            ? 'Customer left contact details for follow-up.'
            : isTakeover
                ? `${activeChat.assigned_user?.name || 'Another specialist'} is unavailable. You can take over this chat.`
            : activeChat.assigned_user
                ? `Assigned to ${activeChat.assigned_user.name}`
                : 'Waiting for a staff member to join.';

        if (contactBits.length > 0) {
            meta.classList.remove('hidden');
            meta.innerHTML = contactBits.join('');
        } else {
            meta.classList.add('hidden');
            meta.innerHTML = '';
        }

        if (contextBits.length > 0) {
            contextBox.classList.remove('hidden');
            contextBox.innerHTML = contextBits.join('');
        } else {
            contextBox.classList.add('hidden');
            contextBox.innerHTML = '';
        }

        if (canClaim) {
            claimButton.classList.remove('hidden');
            claimButton.textContent = isSpecialistAvailable
                ? (isWaiting ? 'Join Chat' : 'Take Over Chat')
                : 'Set Available To Join';
            claimButton.disabled = !isSpecialistAvailable;
            claimButton.classList.toggle('cursor-not-allowed', !isSpecialistAvailable);
            claimButton.classList.toggle('opacity-60', !isSpecialistAvailable);
        } else {
            claimButton.classList.add('hidden');
            claimButton.disabled = false;
            claimButton.classList.remove('cursor-not-allowed', 'opacity-60');
        }

        if (isOfflineLead) {
            note.classList.remove('hidden');
            note.textContent = activeChat.visitor_email
                ? `This customer is no longer in live chat. Follow up by email at ${activeChat.visitor_email}.`
                : 'This customer is no longer in live chat. Follow up outside the widget.';
            input.value = '';
            input.disabled = true;
            input.placeholder = 'Live reply is disabled for email-only follow-up.';
            sendButton.disabled = true;
            sendButton.classList.add('cursor-not-allowed', 'opacity-60');
            sendButton.title = 'Email follow-up required';
            sendButton.setAttribute('aria-label', 'Email follow-up required');
        } else {
            note.classList.add('hidden');
            note.textContent = '';
            input.disabled = false;
            input.placeholder = canClaim ? 'Type your reply and send to claim this chat...' : 'Type your reply...';
            sendButton.disabled = false;
            sendButton.classList.remove('cursor-not-allowed', 'opacity-60');
            sendButton.title = canClaim ? 'Claim and send reply' : 'Send reply';
            sendButton.setAttribute('aria-label', canClaim ? 'Claim and send reply' : 'Send reply');
        }

        messages.innerHTML = activeMessages
            .map((message) => messageMarkup(message, 'staff'))
            .join('');
        if (shouldScroll) {
            messages.scrollTop = messages.scrollHeight;
        }
        renderTypingNote();
    }

    async function heartbeat() {
        if (heartbeatInFlight) {
            return;
        }

        heartbeatInFlight = true;

        try {
            const data = await requestJson(root.dataset.heartbeatUrl, {
                method: 'POST',
                body: JSON.stringify({}),
            });
            isSpecialistAvailable = data.available ?? isSpecialistAvailable;
            renderAvailabilityToggle();
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not confirm your chat availability status.'), 'amber');
        } finally {
            heartbeatInFlight = false;
        }
    }

    function startStaffHeartbeat() {
        if (heartbeatTimer) {
            return;
        }

        heartbeat();

        heartbeatTimer = window.setInterval(() => {
            if (!document.hidden) {
                heartbeat();
            }
        }, 30000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                heartbeat();
            }
        });
    }

    async function setAvailability(available) {
        try {
            const data = await requestJson(root.dataset.availabilityUrl, {
                method: 'POST',
                body: JSON.stringify({ available }),
            });

            isSpecialistAvailable = data.available ?? isSpecialistAvailable;
            renderAvailabilityToggle();

            if (!isSpecialistAvailable) {
                setStaffAlert('You are currently unavailable for new chats.', 'amber');
            } else {
                setStaffAlert('You are available for new chats.', 'amber');
            }
            loadChats();
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not update your chat availability.'), 'amber');
        }
    }

    async function syncTypingState(isTyping) {
        if (!activeChat?.id || lastTypingState === isTyping || activeChat.status === 'offline') {
            return;
        }

        lastTypingState = isTyping;

        try {
            await requestJson(templateUrl(root.dataset.typingUrlTemplate, activeChat.id), {
                method: 'POST',
                body: JSON.stringify({ typing: isTyping }),
            });
        } catch (error) {
            lastTypingState = false;
            setStaffAlert(errorMessage(error, 'Could not update typing status.'), 'amber');
        }
    }

    function recordSeenActivity(items) {
        items.forEach((chat) => {
            if (chat?.id != null) {
                seenActivity.set(chat.id, chat.last_message_at);
            }
        });
    }

    async function openIncomingChat(chatsToCheck) {
        const incoming = chatsToCheck
            .filter((chat) => chat && typeof chat === 'object' && chat.id != null)
            .find((chat) => {
            const previousLastMessageAt = seenActivity.get(chat.id);
            const hasFreshActivity = previousLastMessageAt && previousLastMessageAt !== chat.last_message_at;

            return hasFreshActivity && (chat.is_new_request || chat.needs_staff_reply);
        });

        recordSeenActivity(chatsToCheck);

        if (!incoming) {
            return false;
        }

        if (!isSpecialistAvailable) {
            setStaffAlert(
                incoming.is_new_request
                    ? 'A new chat request is waiting while you are unavailable.'
                    : 'A customer sent a new message while you are unavailable.',
                'amber',
            );
            flashTitle(incoming.is_new_request ? 'New Chat Waiting' : 'Customer Reply Waiting');
            return false;
        }

        if (dismissed) {
            return false;
        }

        openPanel({ chatId: incoming.id });
        await loadChat(incoming.id, { forceScroll: true });
        flashTitle(incoming.is_new_request ? 'New Chat Request' : 'New Customer Reply');
        return true;
    }

    async function loadChats() {
        if (chatsRequestInFlight) {
            return;
        }

        chatsRequestInFlight = true;

        try {
            const data = await requestJson(root.dataset.listUrl, { method: 'GET' });
            const incomingChats = Array.isArray(data.chats)
                ? data.chats.filter((chat) => chat && typeof chat === 'object' && chat.id != null)
                : [];
            isSpecialistAvailable = data.current_user?.chat_available ?? isSpecialistAvailable;
            quickReplies = Array.isArray(data.quick_replies) ? data.quick_replies : quickReplies;
            renderQuickReplies();
            renderAvailabilityToggle();
            setStaffAlert();

            if (seenActivity.size > 0) {
                const openedIncoming = await openIncomingChat(incomingChats);
                chats = incomingChats;
                sortChats();
                syncActiveChatVisibility();
                updateBadge();
                renderList();

                if (openedIncoming) {
                    return;
                }
            } else {
                recordSeenActivity(incomingChats);
                chats = incomingChats;
            }

            sortChats();
            syncActiveChatVisibility();
            updateBadge();
            renderList();

            const persistedChat = restoredSelection
                ? chats.find((chat) => chat.id === restoredSelection)
                : null;

            if (persistedChat) {
                if (isSpecialistAvailable && !dismissed) {
                    openPanel({ chatId: persistedChat.id });
                }
                await loadChat(persistedChat.id, { forceScroll: true });
                return;
            }

            if (activeChat) {
                const refreshedActiveChat = activeChat?.id != null
                    ? chats.find((chat) => chat.id === activeChat.id)
                    : null;

                if (refreshedActiveChat) {
                    await loadChat(refreshedActiveChat.id, { forceScroll: false });
                    return;
                }
            }

            if (!activeChat && chats.length > 0 && chats[0]?.id != null && shouldRestoreOpenPanel() && isSpecialistAvailable) {
                openPanel({ chatId: chats[0].id });
                await loadChat(chats[0].id, { forceScroll: true });
            }
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not load customer chats.'));
        } finally {
            chatsRequestInFlight = false;
        }
    }

    async function loadChat(chatId, options = {}) {
        if (activeChatRequestId === String(chatId)) {
            return;
        }

        activeChatRequestId = String(chatId);

        try {
            const data = await requestJson(templateUrl(root.dataset.showUrlTemplate, chatId), {
                method: 'GET',
            });

            if (!data?.chat || data.chat.id == null) {
                throw new Error('Could not open this conversation.');
            }

            activeChat = data.chat;
            activeMessages = Array.isArray(data.messages) ? data.messages : [];
            activeTyping = data.typing ?? activeTyping;
            upsertChat(activeChat);
            persistState({
                open: !panel.classList.contains('hidden'),
                chatId: activeChat?.id ?? null,
                dismissedState: dismissed,
            });
            setStaffAlert();
            renderActiveChat({ forceScroll: options.forceScroll !== false });
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not open this conversation.'));
        } finally {
            activeChatRequestId = null;
        }
    }

    async function claimActiveChat() {
        if (!activeChat || activeChat.id == null) {
            return;
        }

        if (!isSpecialistAvailable) {
            setStaffAlert('Switch to Available before claiming this chat.', 'amber');
            return;
        }

        try {
            const data = await requestJson(templateUrl(root.dataset.claimUrlTemplate, activeChat.id), {
                method: 'POST',
                body: JSON.stringify({}),
            });

            activeChat = data.chat;
            activeMessages = Array.isArray(data.messages) ? data.messages : activeMessages;
            activeTyping = data.typing ?? activeTyping;
            upsertChat(activeChat);
            setStaffAlert();
            renderActiveChat({ forceScroll: true });
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not claim this chat.'));
        }
    }

    async function handleStaffEvent(payload) {
        if (payload?.type === 'availability.updated') {
            if (payload.user_id === currentUserId && typeof payload.available === 'boolean') {
                isSpecialistAvailable = payload.available;
                renderAvailabilityToggle();
            }

            if (activeChat?.assigned_user?.id === payload.user_id) {
                activeChat = {
                    ...activeChat,
                    assigned_user_available: typeof payload.available === 'boolean'
                        ? payload.available
                        : activeChat.assigned_user_available,
                    live_chat_available: typeof payload.availability?.available === 'boolean'
                        ? payload.availability.available
                        : activeChat.live_chat_available,
                    can_be_claimed: typeof payload.available === 'boolean'
                        ? !payload.available
                        : activeChat.can_be_claimed,
                };
                renderActiveChat();
            }

            chats = chats.map((chat) => {
                if (!chat || chat.assigned_user?.id !== payload.user_id) {
                    return chat;
                }

                return {
                    ...chat,
                    assigned_user_available: typeof payload.available === 'boolean'
                        ? payload.available
                        : chat.assigned_user_available,
                    live_chat_available: typeof payload.availability?.available === 'boolean'
                        ? payload.availability.available
                        : chat.live_chat_available,
                    can_be_claimed: typeof payload.available === 'boolean'
                        ? !payload.available
                        : chat.can_be_claimed,
                };
            });
            sortChats();
            syncActiveChatVisibility();
            updateBadge();
            renderList();
            await loadChats();
            return;
        }

        if (payload.chat?.id != null) {
            upsertChat(payload.chat);
            sortChats();
            syncActiveChatVisibility();
            updateBadge();
        }

        if (payload.typing && activeChat?.id === payload.chat?.id) {
            activeTyping = payload.typing;
        }

        let hasNewMessage = false;

        if (!activeChat || activeChat?.id === payload.chat?.id) {
            if (Array.isArray(payload.messages)) {
                payload.messages.forEach((message) => {
                    hasNewMessage = mergeStaffMessage(message) || hasNewMessage;
                });
            }
            if (payload.message && activeChat?.id === payload.chat?.id) {
                hasNewMessage = mergeStaffMessage(payload.message) || hasNewMessage;
            }

            if (activeChat?.id === payload.chat?.id) {
                activeChat = { ...activeChat, ...payload.chat };
            }
            renderActiveChat({ forceScroll: hasNewMessage });
        } else {
            renderList();
        }

        if (
            !isSpecialistAvailable
            && payload.chat?.id != null
            && (payload.type === 'chat.created' || payload.type === 'chat.waiting.message' || payload.type === 'chat.offline')
        ) {
            setStaffAlert(
                payload.type === 'chat.offline'
                    ? 'A new email lead is waiting while you are unavailable.'
                    : payload.type === 'chat.created'
                        ? 'A new chat request is waiting while you are unavailable.'
                        : 'A customer sent a new message while you are unavailable.',
                'amber',
            );
            flashTitle(payload.type === 'chat.offline' ? 'New Email Lead' : 'New Chat Waiting');
        }

        if (
            isSpecialistAvailable
            && !dismissed
            && payload.chat?.id != null
            && (payload.type === 'chat.created' || payload.type === 'chat.waiting.message' || payload.type === 'chat.offline')
        ) {
            openPanel({ chatId: payload.chat.id });
            if (!activeChat) {
                loadChat(payload.chat.id, { forceScroll: true });
            }
            flashTitle(payload.type === 'chat.offline' ? 'New Email Lead' : 'New Chat Request');
        }

        if (isSpecialistAvailable && !dismissed && payload.message && payload.chat?.id != null && payload.chat?.assigned_user?.id === currentUserId) {
            openPanel({ chatId: payload.chat.id });
            if (payload.chat.id !== activeChat?.id) {
                loadChat(payload.chat.id, { forceScroll: true });
            }
            flashTitle('New Customer Reply');
        }
    }

    function subscribeToStaffChannels() {
        if (!window.Echo) {
            return false;
        }

        if (staffChannelsBound) {
            return true;
        }

        window.Echo.private('staff-chat.available')
            .listen('.customer-chat.updated', handleStaffEvent);

        window.Echo.private(`staff-chat.user.${currentUserId}`)
            .listen('.customer-chat.updated', handleStaffEvent);

        staffChannelsBound = true;
        return true;
    }

    function ensureStaffRealtimeSubscriptions() {
        if (subscribeToStaffChannels()) {
            if (staffRealtimeRetryTimer) {
                window.clearInterval(staffRealtimeRetryTimer);
                staffRealtimeRetryTimer = null;
            }

            return;
        }

        if (staffRealtimeRetryTimer) {
            return;
        }

        staffRealtimeRetryTimer = window.setInterval(() => {
            if (subscribeToStaffChannels()) {
                window.clearInterval(staffRealtimeRetryTimer);
                staffRealtimeRetryTimer = null;
            }
        }, 1000);
    }

    function beginDrag(event) {
        if (!dragHandle || event.button !== 0) {
            return;
        }

        const rect = root.getBoundingClientRect();
        dragState = {
            pointerId: event.pointerId,
            startX: event.clientX,
            startY: event.clientY,
            originLeft: rect.left,
            originTop: rect.top,
            moved: false,
        };

        dragHandle.setPointerCapture?.(event.pointerId);
    }

    function moveDrag(event) {
        if (!dragState || event.pointerId !== dragState.pointerId) {
            return;
        }

        const deltaX = event.clientX - dragState.startX;
        const deltaY = event.clientY - dragState.startY;

        if (!dragState.moved && (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4)) {
            dragState.moved = true;
        }

        if (!dragState.moved) {
            return;
        }

        event.preventDefault();
        setLauncherPosition(dragState.originLeft + deltaX, dragState.originTop + deltaY, false);
    }

    function endDrag(event) {
        if (!dragState || event.pointerId !== dragState.pointerId) {
            return;
        }

        dragHandle.releasePointerCapture?.(event.pointerId);

        if (dragState.moved) {
            const deltaX = event.clientX - dragState.startX;
            const deltaY = event.clientY - dragState.startY;
            setLauncherPosition(dragState.originLeft + deltaX, dragState.originTop + deltaY);
            toggle.dataset.suppressClick = '1';
            window.setTimeout(() => {
                toggle.dataset.suppressClick = '0';
            }, 0);
        }

        dragState = null;
    }

    root.addEventListener('staff-chat:opened', async () => {
        openPanel({ manual: true });
        await loadChats();
    });

    root.addEventListener('staff-chat:closed', () => {
        closePanel({ manual: true });
    });

    toggle.addEventListener('click', (event) => {
        if (toggle.dataset.suppressClick === '1') {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    claimButton.addEventListener('click', claimActiveChat);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!activeChat) {
            return;
        }

        const body = input.value.trim();
        if ((!body && !attachmentInput?.files?.[0]) || activeChat.status === 'offline') {
            return;
        }

        if (!isSpecialistAvailable && canClaimStaffChat(activeChat)) {
            setStaffAlert('Switch to Available before claiming this chat.', 'amber');
            return;
        }

        try {
            await syncTypingState(false);
            const payload = new FormData();
            if (body) {
                payload.append('message', body);
            }
            if (attachmentInput?.files?.[0]) {
                payload.append('attachment', attachmentInput.files[0]);
            }

            const data = await requestJson(templateUrl(root.dataset.messageUrlTemplate, activeChat.id), {
                method: 'POST',
                body: payload,
            });

            input.value = '';
            if (attachmentInput) {
                attachmentInput.value = '';
            }
            updateAttachmentName();
            if (typingIdleTimer) {
                window.clearTimeout(typingIdleTimer);
                typingIdleTimer = null;
            }
            if (data.chat) {
                activeChat = { ...activeChat, ...data.chat };
                upsertChat(activeChat);
            }
            if (data.message) {
                mergeStaffMessage(data.message);
            }
            setStaffAlert();
            renderActiveChat({ forceScroll: true });
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not send your reply.'));
        }
    });

    input.addEventListener('input', () => {
        if (!activeChat || activeChat.status === 'offline') {
            return;
        }

        const hasValue = input.value.trim().length > 0;
        syncTypingState(hasValue);

        if (typingIdleTimer) {
            window.clearTimeout(typingIdleTimer);
        }

        typingIdleTimer = window.setTimeout(() => {
            syncTypingState(false);
        }, 1800);
    });

    input.addEventListener('blur', () => {
        syncTypingState(false);
    });

    attachmentInput?.addEventListener('change', updateAttachmentName);

    quickReplySelect?.addEventListener('change', () => {
        const selected = quickReplies.find((reply) => String(reply.id) === quickReplySelect.value);

        if (!selected) {
            return;
        }

        applyQuickReply(selected);
        setQuickReplyManagerOpen(false);
    });

    quickReplyManageToggle?.addEventListener('click', () => {
        setQuickReplyManagerOpen(quickReplyManager?.classList.contains('hidden'));
    });

    quickReplyManagerClose?.addEventListener('click', () => {
        setQuickReplyManagerOpen(false);
    });

    quickReplyFillButton?.addEventListener('click', () => {
        if (quickReplyMessageInput) {
            quickReplyMessageInput.value = input.value.trim();
            quickReplyMessageInput.focus();
        }
    });

    quickReplySaveButton?.addEventListener('click', async () => {
        const label = quickReplyLabelInput?.value.trim() || '';
        const message = quickReplyMessageInput?.value.trim() || '';

        if (!label || !message) {
            setQuickReplyManagerOpen(true);
            setStaffAlert('Enter both a label and message to save a quick reply.', 'amber');
            return;
        }

        try {
            const data = await requestJson(root.dataset.quickReplyStoreUrl, {
                method: 'POST',
                body: JSON.stringify({ label, message }),
            });

            quickReplies = Array.isArray(data.quick_replies) ? data.quick_replies : quickReplies;
            renderQuickReplies();
            resetQuickReplyForm();
            setQuickReplyManagerOpen(false);
            setStaffAlert('Quick reply saved.', 'amber');
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not save this quick reply.'));
        }
    });

    availabilityToggle?.addEventListener('click', () => {
        setAvailability(!isSpecialistAvailable);
    });

    dragHandle?.addEventListener('pointerdown', beginDrag);
    dragHandle?.addEventListener('pointermove', moveDrag);
    dragHandle?.addEventListener('pointerup', endDrag);
    dragHandle?.addEventListener('pointercancel', endDrag);
    if (dragHandle) {
        dragHandle.style.touchAction = 'none';
    }

    restoreLauncherPosition();
    updatePanelPlacement();
    window.addEventListener('resize', () => {
        updatePanelPlacement();
        restoreLauncherPosition();
    });

    ensureStaffRealtimeSubscriptions();
    renderAvailabilityToggle();
    startStaffHeartbeat();
    loadChats();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-customer-chat-widget]').forEach((root) => initCustomerWidget(root));
    document.querySelectorAll('[data-staff-chat-widget]').forEach((root) => initStaffWidget(root));
});
