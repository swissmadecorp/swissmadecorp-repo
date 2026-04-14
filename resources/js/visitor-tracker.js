function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function generateUuid() {
    if (window.crypto?.randomUUID) {
        return window.crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (char) => {
        const random = Math.random() * 16 | 0;
        const value = char === 'x' ? random : (random & 0x3 | 0x8);
        return value.toString(16);
    });
}

function safeStorage(storage) {
    try {
        const key = '__visitor_monitor_test__';
        storage.setItem(key, '1');
        storage.removeItem(key);
        return storage;
    } catch (error) {
        return null;
    }
}

function readCookie(name) {
    const prefix = `${name}=`;

    return document.cookie
        .split(';')
        .map((item) => item.trim())
        .find((item) => item.startsWith(prefix))
        ?.slice(prefix.length) ?? null;
}

function readPagePayload() {
    return {
        page_url: window.location.href,
        page_path: `${window.location.pathname}${window.location.search}`,
        page_title: document.title || '',
        referrer_url: document.referrer || '',
        visibility_state: document.visibilityState === 'hidden' ? 'hidden' : 'visible',
    };
}

async function sendJson(url, payload) {
    await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
        keepalive: true,
    });
}

function initVisitorTracker(root) {
    if (!root || root.dataset.bound === '1' || window.location.pathname.startsWith('/admin')) {
        return;
    }

    root.dataset.bound = '1';

    const local = safeStorage(window.localStorage);
    const session = safeStorage(window.sessionStorage);

    if (!local || !session) {
        return;
    }

    const visitorStorageKey = 'swissmade_visitor_key';
    const sessionStorageKey = 'swissmade_visitor_session_token';
    const leaveRecordKey = 'swissmade_visitor_leave_record';
    const heartbeatUrl = root.dataset.heartbeatUrl;
    const leaveUrl = root.dataset.leaveUrl;
    const heartbeatInterval = Number(root.dataset.heartbeatIntervalMs || 15000);
    const cookieDomain = root.dataset.cookieDomain || '';
    let lastPageSignature = `${window.location.href}|${document.title || ''}`;

    let visitorKey = local.getItem(visitorStorageKey) || readCookie(visitorStorageKey);
    if (!visitorKey) {
        visitorKey = generateUuid();
    }
    local.setItem(visitorStorageKey, visitorKey);

    let sessionToken = session.getItem(sessionStorageKey);
    if (!sessionToken) {
        sessionToken = generateUuid();
        session.setItem(sessionStorageKey, sessionToken);
    }

    try {
        const leaveRecord = JSON.parse(local.getItem(leaveRecordKey) || 'null');
        const sameOriginReferrer = document.referrer.startsWith(window.location.origin);
        const isRecentLeave = leaveRecord?.timestamp && (Date.now() - leaveRecord.timestamp) < 5000;

        if (leaveRecord?.sessionToken === sessionToken && (!sameOriginReferrer || !isRecentLeave)) {
            sessionToken = generateUuid();
            session.setItem(sessionStorageKey, sessionToken);
        }

        if (leaveRecord) {
            local.removeItem(leaveRecordKey);
        }
    } catch (error) {
        local.removeItem(leaveRecordKey);
    }

    const cookieParts = [
        `swissmade_visitor_key=${visitorKey}`,
        'path=/',
        'max-age=31536000',
        'SameSite=Lax',
    ];

    if (cookieDomain) {
        cookieParts.push(`domain=${cookieDomain}`);
    }

    if (window.location.protocol === 'https:') {
        cookieParts.push('Secure');
    }

    document.cookie = cookieParts.join('; ');

    let heartbeatInFlight = false;
    let leaveInFlight = false;
    let heartbeatQueued = false;

    function basePayload() {
        return {
            _token: csrfToken(),
            visitor_key: visitorKey,
            session_token: sessionToken,
            ...readPagePayload(),
        };
    }

    function persistVisitorKey(nextVisitorKey) {
        if (!nextVisitorKey || nextVisitorKey === visitorKey) {
            return;
        }

        visitorKey = nextVisitorKey;
        local.setItem(visitorStorageKey, visitorKey);

        const cookieParts = [
            `swissmade_visitor_key=${visitorKey}`,
            'path=/',
            'max-age=31536000',
            'SameSite=Lax',
        ];

        if (cookieDomain) {
            cookieParts.push(`domain=${cookieDomain}`);
        }

        if (window.location.protocol === 'https:') {
            cookieParts.push('Secure');
        }

        document.cookie = cookieParts.join('; ');
    }

    async function heartbeat() {
        if (heartbeatInFlight) {
            heartbeatQueued = true;
            return;
        }

        heartbeatInFlight = true;
        heartbeatQueued = false;

        try {
            const response = await fetch(heartbeatUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(basePayload()),
                keepalive: true,
            });

            if (response.ok) {
                const data = await response.json();

                if (data?.visitor_key) {
                    persistVisitorKey(data.visitor_key);
                }

                if (data?.session_token && data.session_token !== sessionToken) {
                    sessionToken = data.session_token;
                    session.setItem(sessionStorageKey, sessionToken);
                }
            }
        } catch (error) {
            // Keep visitor tracking silent if a heartbeat fails.
        } finally {
            heartbeatInFlight = false;

            if (heartbeatQueued) {
                window.setTimeout(heartbeat, 0);
            }
        }
    }

    function pageSignature() {
        return `${window.location.href}|${document.title || ''}`;
    }

    function heartbeatIfPageChanged() {
        const nextSignature = pageSignature();

        if (nextSignature === lastPageSignature) {
            return;
        }

        lastPageSignature = nextSignature;
        heartbeat();
    }

    function patchHistoryMethod(name) {
        const original = window.history?.[name];

        if (typeof original !== 'function') {
            return;
        }

        window.history[name] = function patchedHistoryMethod(...args) {
            const result = original.apply(this, args);
            window.dispatchEvent(new Event('swissmade:locationchange'));

            return result;
        };
    }

    function leavePage() {
        if (leaveInFlight) {
            return;
        }

        leaveInFlight = true;

        try {
            local.setItem(leaveRecordKey, JSON.stringify({
                sessionToken,
                timestamp: Date.now(),
            }));
        } catch (error) {
            // Ignore storage issues.
        }

        const payload = new URLSearchParams(basePayload());
        const beaconQueued = navigator.sendBeacon
            ? navigator.sendBeacon(leaveUrl, payload)
            : false;

        if (!beaconQueued) {
            sendJson(leaveUrl, basePayload()).catch(() => {
                // Keep visitor tracking silent if the leave fallback fails.
            });
        }
    }

    window.SwissMadeVisitorMonitor = {
        getVisitorKey() {
            return visitorKey;
        },
        getSessionToken() {
            return sessionToken;
        },
    };

    patchHistoryMethod('pushState');
    patchHistoryMethod('replaceState');

    heartbeat();
    window.setInterval(heartbeat, heartbeatInterval);

    document.addEventListener('visibilitychange', heartbeat);

    window.addEventListener('focus', heartbeat);
    window.addEventListener('pageshow', heartbeat);
    window.addEventListener('online', heartbeat);
    window.addEventListener('popstate', heartbeatIfPageChanged);
    window.addEventListener('hashchange', heartbeatIfPageChanged);
    window.addEventListener('swissmade:locationchange', heartbeatIfPageChanged);
    window.addEventListener('beforeunload', leavePage);
    window.addEventListener('pagehide', leavePage);
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-visitor-monitor]');

    if (root) {
        initVisitorTracker(root);
    }
});
