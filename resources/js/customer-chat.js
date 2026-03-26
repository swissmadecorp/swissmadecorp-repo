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
    const alertBox = root.querySelector('[data-customer-alert]');
    const messagesBox = root.querySelector('[data-customer-messages]');
    const chatMeta = root.querySelector('[data-customer-chat-meta]');
    const typingBox = root.querySelector('[data-customer-typing]');
    const messageForm = root.querySelector('[data-customer-message-form]');
    const startName = root.querySelector('[data-customer-name]');
    const startEmail = root.querySelector('[data-customer-email]');
    const startMessage = root.querySelector('[data-customer-start-message]');
    const leadName = root.querySelector('[data-lead-name]');
    const leadEmail = root.querySelector('[data-lead-email]');
    const leadMessage = root.querySelector('[data-lead-message]');
    const messageInput = root.querySelector('[data-customer-message-input]');
    const attachmentInput = root.querySelector('[data-customer-attachment-input]');
    const attachmentName = root.querySelector('[data-customer-attachment-name]');

    let activeToken = window.localStorage.getItem(storageKey);
    let activeChat = null;
    let activeMessages = [];
    let activeTyping = {
        customer: { is_typing: false, label: 'Customer is typing...' },
        staff: { is_typing: false, label: 'A specialist is typing...' },
    };
    let subscribedChannel = null;
    let conversationPollTimer = null;
    let availabilityPollTimer = null;
    let typingIdleTimer = null;
    let lastTypingState = false;

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

    function scrollMessagesToBottom() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function mergeMessage(message) {
        if (!message?.id || activeMessages.some((item) => item.id === message.id)) {
            return;
        }

        activeMessages.push(message);
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
        const heading = assignedName
            ? `Connected with ${assignedName}`
            : 'Waiting for an available specialist';
        const secondary = activeChat?.last_message_at
            ? `Last update ${formatDateTime(activeChat.last_message_at)}`
            : 'Your chat is ready.';

        chatMeta.innerHTML = `
            <div class="font-semibold text-gray-900">${escapeHtml(heading)}</div>
            <div class="mt-1">${escapeHtml(secondary)}</div>
        `;

        toggleCopy.textContent = assignedName ? `Connected to ${assignedName}` : 'Chat in progress';
    }

    function renderTypingIndicator() {
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

        if (activeChat.assigned_user) {
            setStatus(`Connected with ${activeChat.assigned_user.name}`, true);
        } else {
            setStatus('Waiting for a specialist...', true);
        }

        renderTypingIndicator();
    }

    async function fetchAvailability() {
        const data = await requestJson(root.dataset.availabilityUrl, { method: 'GET' });

        if (data.available) {
            setStatus(`${data.available_agents} specialist${data.available_agents === 1 ? '' : 's'} online`, true);
            prechatCopy.textContent = 'Start a one-to-one conversation with one of our available watch specialists.';
            startForm.classList.remove('hidden');
            leaveForm.classList.add('hidden');
        } else {
            setStatus('Currently away', false);
            prechatCopy.textContent = data.offline_prompt;
            startForm.classList.add('hidden');
            leaveForm.classList.remove('hidden');
        }
    }

    function subscribeToCustomerChannel(token) {
        if (!window.Echo || !token) {
            return;
        }

        const channelName = `customer-chat.${token}`;
        if (subscribedChannel === channelName) {
            return;
        }

        if (subscribedChannel) {
            window.Echo.leave(subscribedChannel);
        }

        subscribedChannel = channelName;

        window.Echo.channel(channelName).listen('.customer-chat.updated', (payload) => {
            if (payload.chat) {
                activeChat = payload.chat;
            }

            if (payload.typing) {
                activeTyping = payload.typing;
            }

            if (Array.isArray(payload.messages)) {
                payload.messages.forEach((message) => mergeMessage(message));
            }

            if (payload.message) {
                mergeMessage(payload.message);
            }

            renderConversation();

            if (payload.message && payload.message.sender_type !== 'customer') {
                flashTitle('New Chat Reply');
                panel.classList.remove('hidden');
            }
        });
    }

    async function loadExistingConversation(options = {}) {
        if (!activeToken) {
            return;
        }

        try {
            const data = await requestJson(templateUrl(root.dataset.showUrlTemplate, activeToken), {
                method: 'GET',
            });

            activeChat = data.chat;
            activeMessages = Array.isArray(data.messages) ? data.messages : [];
            activeTyping = data.typing ?? activeTyping;
            subscribeToCustomerChannel(activeChat.public_token);
            renderConversation({ forceScroll: !options.silent });
        } catch (error) {
            if (!options.silent || error.status === 404) {
                window.localStorage.removeItem(storageKey);
                activeToken = null;
                activeChat = null;
                activeMessages = [];
                activeTyping = {
                    customer: { is_typing: false, label: 'Customer is typing...' },
                    staff: { is_typing: false, label: 'A specialist is typing...' },
                };
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

    function startConversationPolling() {
        if (conversationPollTimer) {
            return;
        }

        conversationPollTimer = window.setInterval(() => {
            if (activeToken && !document.hidden) {
                if (isRealtimeConnected()) {
                    return;
                }

                loadExistingConversation({ silent: true });
            }
        }, 4000);
    }

    function startAvailabilityPolling() {
        if (availabilityPollTimer) {
            return;
        }

        availabilityPollTimer = window.setInterval(() => {
            if (!document.hidden && !activeChat && !panel.classList.contains('hidden')) {
                fetchAvailability().catch(() => {});
            }
        }, 15000);
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

    close.addEventListener('click', () => {
        panel.classList.add('hidden');
    });

    startForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setAlert();

        try {
            const data = await requestJson(root.dataset.createUrl, {
                method: 'POST',
                body: JSON.stringify({
                    visitor_name: startName.value.trim(),
                    visitor_email: startEmail.value.trim(),
                    message: startMessage.value.trim(),
                }),
            });

            activeChat = data.chat;
            activeMessages = Array.isArray(data.messages) ? data.messages : [];
            activeToken = activeChat.public_token;
            activeTyping = data.typing ?? activeTyping;
            window.localStorage.setItem(storageKey, activeToken);
            subscribeToCustomerChannel(activeToken);
            startForm.reset();
            renderConversation();
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
            await requestJson(root.dataset.leaveEmailUrl, {
                method: 'POST',
                body: JSON.stringify({
                    visitor_name: leadName.value.trim(),
                    visitor_email: leadEmail.value.trim(),
                    message: leadMessage.value.trim(),
                }),
            });

            leaveForm.reset();
            setAlert('Thank you. Your email was saved and our team will follow up soon.');
        } catch (error) {
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
            renderConversation();
        } catch (error) {
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

    startConversationPolling();
    startAvailabilityPolling();
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
    let restoredSelection = Number(window.localStorage.getItem(`${storageKey}:chatId`)) || null;
    const shouldRestoreOpen = window.localStorage.getItem(`${storageKey}:open`) === '1' || restoredSelection !== null;

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

    function persistState({ open = !panel.classList.contains('hidden'), chatId = activeChat?.id ?? restoredSelection } = {}) {
        window.localStorage.setItem(`${storageKey}:open`, open ? '1' : '0');

        if (chatId) {
            window.localStorage.setItem(`${storageKey}:chatId`, String(chatId));
            restoredSelection = Number(chatId);
            return;
        }

        window.localStorage.removeItem(`${storageKey}:chatId`);
        restoredSelection = null;
    }

    function visibleChats() {
        return chats.filter((chat) =>
            chat.status === 'waiting'
            || chat.status === 'offline'
            || chat.assigned_user?.id === currentUserId
        );
    }

    function updateBadge() {
        const waitingCount = chats.filter((chat) =>
            chat.is_new_request
            || chat.needs_staff_reply
            || chat.status === 'offline'
        ).length;

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
        const keep = chat.status === 'waiting'
            || chat.status === 'offline'
            || chat.assigned_user?.id === currentUserId;
        const index = chats.findIndex((item) => item.id === chat.id);

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
            return;
        }

        activeMessages.push(message);
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
                    : chat.needs_staff_reply
                        ? '<span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Reply Needed</span>'
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
                loadChat(button.dataset.chatId);
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
            note.classList.add('hidden');
            note.textContent = '';
            typingNote.classList.add('hidden');
            typingNote.textContent = '';
            return;
        }

        const isOfflineLead = activeChat.status === 'offline';
        const isWaiting = activeChat.status === 'waiting';
        const contactBits = [];

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

        empty.classList.add('hidden');
        conversation.classList.remove('hidden');
        title.textContent = activeChat.visitor_name || activeChat.visitor_email || `Visitor #${activeChat.id}`;
        subtitle.textContent = activeChat.is_new_request
            ? 'Brand new chat request from the customer.'
            : activeChat.needs_staff_reply
                ? 'Customer sent a new message and is waiting for your reply.'
            : isOfflineLead
            ? 'Customer left contact details for follow-up.'
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

        if (isWaiting) {
            claimButton.classList.remove('hidden');
            claimButton.textContent = 'Join Chat';
        } else {
            claimButton.classList.add('hidden');
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
            input.placeholder = isWaiting ? 'Type your reply and send to join this chat...' : 'Type your reply...';
            sendButton.disabled = false;
            sendButton.classList.remove('cursor-not-allowed', 'opacity-60');
            sendButton.title = isWaiting ? 'Join and send reply' : 'Send reply';
            sendButton.setAttribute('aria-label', isWaiting ? 'Join and send reply' : 'Send reply');
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
        try {
            const data = await requestJson(root.dataset.heartbeatUrl, {
                method: 'POST',
                body: JSON.stringify({}),
            });
            isSpecialistAvailable = data.available ?? isSpecialistAvailable;
            renderAvailabilityToggle();
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not confirm your chat availability status.'), 'amber');
        }
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
                heartbeat();
            }
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
            seenActivity.set(chat.id, chat.last_message_at);
        });
    }

    async function openIncomingChat(chatsToCheck) {
        if (!isSpecialistAvailable) {
            recordSeenActivity(chatsToCheck);
            return false;
        }

        const incoming = chatsToCheck.find((chat) => {
            const previousLastMessageAt = seenActivity.get(chat.id);
            const hasFreshActivity = previousLastMessageAt && previousLastMessageAt !== chat.last_message_at;

            return hasFreshActivity && (chat.is_new_request || chat.needs_staff_reply);
        });

        recordSeenActivity(chatsToCheck);

        if (!incoming) {
            return false;
        }

        panel.classList.remove('hidden');
        persistState({ open: true, chatId: incoming.id });
        await loadChat(incoming.id);
        flashTitle(incoming.is_new_request ? 'New Chat Request' : 'New Customer Reply');
        return true;
    }

    async function loadChats() {
        try {
            const data = await requestJson(root.dataset.listUrl, { method: 'GET' });
            const incomingChats = Array.isArray(data.chats) ? data.chats : [];
            isSpecialistAvailable = data.current_user?.chat_available ?? isSpecialistAvailable;
            quickReplies = Array.isArray(data.quick_replies) ? data.quick_replies : quickReplies;
            renderQuickReplies();
            renderAvailabilityToggle();
            setStaffAlert();

            if (seenActivity.size > 0) {
                const openedIncoming = await openIncomingChat(incomingChats);
                chats = Array.isArray(data.chats) ? data.chats : [];
                sortChats();
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
            updateBadge();
            renderList();

            const persistedChat = restoredSelection
                ? chats.find((chat) => chat.id === restoredSelection)
                : null;

            if (persistedChat) {
                if (isSpecialistAvailable) {
                    panel.classList.remove('hidden');
                }
                await loadChat(persistedChat.id);
                return;
            }

            if (activeChat) {
                const refreshedActiveChat = chats.find((chat) => chat.id === activeChat.id);

                if (refreshedActiveChat) {
                    await loadChat(refreshedActiveChat.id);
                    return;
                }
            }

            if (!activeChat && chats.length > 0 && shouldRestoreOpen && isSpecialistAvailable) {
                panel.classList.remove('hidden');
                await loadChat(chats[0].id);
            }
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not load customer chats.'));
        }
    }

    async function loadChat(chatId) {
        try {
            const data = await requestJson(templateUrl(root.dataset.showUrlTemplate, chatId), {
                method: 'GET',
            });

            activeChat = data.chat;
            activeMessages = Array.isArray(data.messages) ? data.messages : [];
            activeTyping = data.typing ?? activeTyping;
            upsertChat(activeChat);
            persistState({ open: true, chatId: activeChat.id });
            setStaffAlert();
            renderActiveChat({ forceScroll: true });
        } catch (error) {
            setStaffAlert(errorMessage(error, 'Could not open this conversation.'));
        }
    }

    async function claimActiveChat() {
        if (!activeChat) {
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
            setStaffAlert(errorMessage(error, 'Could not join this chat.'));
        }
    }

    function handleStaffEvent(payload) {
        if (payload.chat) {
            upsertChat(payload.chat);
        }

        if (payload.typing && activeChat?.id === payload.chat?.id) {
            activeTyping = payload.typing;
        }

        if (!activeChat || activeChat?.id === payload.chat?.id) {
            if (Array.isArray(payload.messages)) {
                payload.messages.forEach((message) => mergeStaffMessage(message));
            }
            if (payload.message && activeChat?.id === payload.chat?.id) {
                mergeStaffMessage(payload.message);
            }

            if (activeChat?.id === payload.chat?.id) {
                activeChat = { ...activeChat, ...payload.chat };
            }
            renderActiveChat();
        } else {
            renderList();
        }

        if (
            isSpecialistAvailable
            && payload.chat
            && (payload.type === 'chat.created' || payload.type === 'chat.waiting.message' || payload.type === 'chat.offline')
        ) {
            panel.classList.remove('hidden');
            persistState({ open: true, chatId: payload.chat.id });
            if (!activeChat) {
                loadChat(payload.chat.id);
            }
            flashTitle(payload.type === 'chat.offline' ? 'New Email Lead' : 'New Chat Request');
        }

        if (isSpecialistAvailable && payload.message && payload.chat?.assigned_user?.id === currentUserId) {
            panel.classList.remove('hidden');
            persistState({ open: true, chatId: payload.chat.id });
            if (payload.chat.id !== activeChat?.id) {
                loadChat(payload.chat.id);
            }
            flashTitle('New Customer Reply');
        }
    }

    function subscribeToStaffChannels() {
        if (!window.Echo) {
            return;
        }

        window.Echo.private('staff-chat.available')
            .listen('.customer-chat.updated', handleStaffEvent);

        window.Echo.private(`staff-chat.user.${currentUserId}`)
            .listen('.customer-chat.updated', handleStaffEvent);
    }

    root.addEventListener('staff-chat:opened', async () => {
        persistState({ open: true });
        await loadChats();
    });

    root.addEventListener('staff-chat:closed', () => {
        persistState({ open: false });
    });

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

    subscribeToStaffChannels();
    renderAvailabilityToggle();
    heartbeat();
    loadChats();
    window.setInterval(heartbeat, 30000);
    window.setInterval(() => {
        if (!document.hidden && !isRealtimeConnected()) {
            loadChats();
        }
    }, 5000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            heartbeat();
            if (!isRealtimeConnected()) {
                loadChats();
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-customer-chat-widget]').forEach((root) => initCustomerWidget(root));
    document.querySelectorAll('[data-staff-chat-widget]').forEach((root) => initStaffWidget(root));
});
