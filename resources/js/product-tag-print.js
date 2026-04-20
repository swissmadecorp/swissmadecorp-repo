const TAG_PRINTER_NAME = 'ZDesigner ZD420-203dpi ZPL';
const QZ_TRAY_SCRIPT_URL = 'https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js';
const FALLBACK_WINDOW_FEATURES = 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=400,height=400';

let qzTrayScriptPromise;
let qzSecurityConfigured = false;

function normalizeProductIds(ids) {
    if (Array.isArray(ids)) {
        return ids.filter(Boolean).join(',');
    }

    return String(ids ?? '').trim();
}

function buildTagPdfUrl(ids) {
    return `/admin/printTag/${encodeURIComponent(ids)}`;
}

function showMessage(message) {
    if (window.Swal?.fire) {
        window.Swal.fire({
            icon: 'warning',
            title: 'Tag Print',
            text: message,
        });

        return;
    }

    window.alert(message);
}

function openFallbackPrintWindow(url) {
    window.open(url, 'product-tag-print', FALLBACK_WINDOW_FEATURES);
}

function loadQzTray() {
    if (window.qz) {
        return Promise.resolve(window.qz);
    }

    if (qzTrayScriptPromise) {
        return qzTrayScriptPromise;
    }

    qzTrayScriptPromise = new Promise((resolve, reject) => {
        const existingScript = document.querySelector(`script[src="${QZ_TRAY_SCRIPT_URL}"]`);

        if (existingScript) {
            existingScript.addEventListener('load', () => resolve(window.qz), { once: true });
            existingScript.addEventListener('error', () => reject(new Error('Unable to load QZ Tray.')), { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = QZ_TRAY_SCRIPT_URL;
        script.async = true;
        script.onload = () => {
            if (window.qz) {
                resolve(window.qz);
                return;
            }

            reject(new Error('QZ Tray loaded, but the print bridge is unavailable.'));
        };
        script.onerror = () => reject(new Error('Unable to load QZ Tray.'));

        document.head.appendChild(script);
    });

    return qzTrayScriptPromise;
}

function configureQzSecurity(qz) {
    if (qzSecurityConfigured) {
        return;
    }

    if (qz.api?.setPromiseType) {
        qz.api.setPromiseType((resolver) => new Promise(resolver));
    }

    // This keeps the first pass simple. QZ Tray may still prompt until a signed
    // certificate flow is added for silent printing in production.
    if (qz.security?.setCertificatePromise) {
        qz.security.setCertificatePromise((resolve) => resolve());
    }

    if (qz.security?.setSignaturePromise) {
        qz.security.setSignaturePromise(() => (resolve) => resolve());
    }

    qzSecurityConfigured = true;
}

async function ensureQzConnection(qz) {
    if (qz.websocket?.isActive()) {
        return;
    }

    await qz.websocket.connect();
}

async function fetchPdfAsBase64(url) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(`Unable to load tag PDF (${response.status}).`);
    }

    const blob = await response.blob();

    return await new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onloadend = () => {
            const result = typeof reader.result === 'string' ? reader.result : '';
            resolve(result.split(',').pop() ?? '');
        };
        reader.onerror = () => reject(new Error('Unable to read tag PDF.'));
        reader.readAsDataURL(blob);
    });
}

async function findTagPrinter(qz) {
    return await qz.printers.find(TAG_PRINTER_NAME);
}

async function sendPdfToPrinter(qz, printerName, pdfBase64) {
    const config = qz.configs.create(printerName, {
        copies: 1,
    });

    await qz.print(config, [{
        type: 'pixel',
        format: 'pdf',
        flavor: 'base64',
        data: pdfBase64,
    }]);
}

async function printProductTagInternal(ids) {
    const normalizedIds = normalizeProductIds(ids);

    if (!normalizedIds) {
        showMessage('No product was selected to print.');
        return;
    }

    const pdfUrl = buildTagPdfUrl(normalizedIds);

    try {
        const qz = await loadQzTray();
        configureQzSecurity(qz);
        await ensureQzConnection(qz);

        const printerName = await findTagPrinter(qz);
        const pdfBase64 = await fetchPdfAsBase64(pdfUrl);

        await sendPdfToPrinter(qz, printerName, pdfBase64);
    } catch (error) {
        console.error('Automatic tag printing failed.', error);
        showMessage(`Automatic tag printing could not use "${TAG_PRINTER_NAME}". The regular print preview will open instead.`);
        openFallbackPrintWindow(pdfUrl);
    }
}

window.SwissmadePrint = window.SwissmadePrint || {};
window.SwissmadePrint.tagPrinterName = TAG_PRINTER_NAME;
window.SwissmadePrint.printProductTag = function printProductTag(ids) {
    void printProductTagInternal(ids);
    return false;
};
