function getFieldValue(form, name) {
    const field = form.elements.namedItem(name);

    return field ? field.value : null;
}

function parseMoney(value) {
    const normalized = String(value ?? '')
        .replace(/[০-৯]/g, (digit) => '০১২৩৪৫৬৭৮৯'.indexOf(digit))
        .replace(/[^0-9.]/g, '');

    const amount = Number.parseFloat(normalized);

    return Number.isFinite(amount) ? amount : 0;
}

function ensureIdempotencyKey(form) {
    let key = getFieldValue(form, 'idempotency_key');

    if (key) {
        return key;
    }

    key = window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(16).slice(2)}`;

    let field = form.elements.namedItem('idempotency_key');

    if (!field) {
        field = document.createElement('input');
        field.type = 'hidden';
        field.name = 'idempotency_key';
        form.appendChild(field);
    }

    field.value = key;

    return key;
}

function resetIdempotencyKey(form) {
    const field = form.elements.namedItem('idempotency_key');

    if (field) {
        field.value = '';
    }
}

function collectPayload(root, form) {
    const payload = Object.fromEntries(new FormData(form).entries());

    payload.component_id = root.dataset.componentId;
    payload.idempotency_key = ensureIdempotencyKey(form);

    if (root.dataset.publishedVersionId) {
        payload.published_version_id = root.dataset.publishedVersionId;
    }

    return payload;
}

function selectedPackagePrice(form) {
    const checkedQuantity = form.querySelector('[name="quantity"]:checked');
    const packageCard = checkedQuantity?.closest('[data-package-card], [data-mobile-package-card], [data-bari12-package], [data-sheikh-package]');

    return parseMoney(packageCard?.dataset.price);
}

function collectIncompletePayload(root, form, runtime) {
    const productId = getFieldValue(form, 'product_id');
    const quantity = Math.max(1, Number.parseInt(getFieldValue(form, 'quantity') || '1', 10) || 1);
    const packageTotal = selectedPackagePrice(form);
    const unitPrice = packageTotal > 0 ? packageTotal / quantity : 0;
    const payload = new URLSearchParams();

    payload.set('_token', runtime.config?.csrfToken ?? '');
    payload.set('mobile', getFieldValue(form, 'mobile') ?? '');
    payload.set('name', getFieldValue(form, 'first_name') ?? '');
    payload.set('first_name', getFieldValue(form, 'first_name') ?? '');
    payload.set('address', getFieldValue(form, 'shipping_address') ?? '');
    payload.set('shipping_address', getFieldValue(form, 'shipping_address') ?? '');

    if (productId) {
        payload.append('product_id[]', productId);
        payload.append('quantity[]', String(quantity));
        payload.append('unit_price[]', unitPrice > 0 ? String(unitPrice) : '');
        payload.append('unit_discount[]', '0');

        const variationId = getFieldValue(form, 'variation_id');

        if (variationId) {
            payload.append('variation_id[]', variationId);
        }
    }

    return payload;
}

function setMessage(root, message, type) {
    const output = root.querySelector('[data-order-submission-message]');

    if (!output) {
        return;
    }

    output.textContent = message;
    output.dataset.type = type;
}

export function initOrderSubmission(root, config, runtime) {
    const submitUrl = runtime.config?.actions?.orderSubmission?.url;

    if (!submitUrl) {
        return null;
    }

    const incompleteUrl = runtime.config?.actions?.incompleteOrder?.url ?? '/incomplete/order/store';
    let incompleteTimer = null;
    let lastIncompletePayload = '';

    const saveIncompleteOrder = async (form) => {
        const mobile = (getFieldValue(form, 'mobile') ?? '').replace(/\D/g, '');

        if (mobile.length < 11 || !getFieldValue(form, 'product_id')) {
            return;
        }

        const payload = collectIncompletePayload(root, form, runtime);
        const payloadString = payload.toString();

        if (payloadString === lastIncompletePayload) {
            return;
        }

        lastIncompletePayload = payloadString;

        try {
            await fetch(incompleteUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': runtime.config?.csrfToken ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: payloadString,
            });
        } catch {
            lastIncompletePayload = '';
        }
    };

    const scheduleIncompleteSave = (form) => {
        window.clearTimeout(incompleteTimer);
        incompleteTimer = window.setTimeout(() => saveIncompleteOrder(form), 2000);
    };

    const incompleteHandler = (event) => {
        const form = event.target.closest('[data-landing-order-form]');

        if (!form || !root.contains(form)) {
            return;
        }

        scheduleIncompleteSave(form);
    };

    const submitHandler = async (event) => {
        const form = event.target.closest('[data-landing-order-form]');

        if (!form || !root.contains(form)) {
            return;
        }

        event.preventDefault();

        if (!getFieldValue(form, 'product_id')) {
            setMessage(root, 'Please select a product before submitting.', 'error');

            return;
        }

        const submitButton = form.querySelector('[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': runtime.config?.csrfToken ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(collectPayload(root, form)),
            });
            const data = await response.json();

            if (!response.ok || data.success === false) {
                setMessage(root, data.message ?? 'Unable to submit order.', 'error');
                resetIdempotencyKey(form);

                return;
            }

            setMessage(root, data.message ?? 'Order submitted.', 'success');

            if (data.url) {
                window.location.href = data.url;
            }
        } catch {
            setMessage(root, 'Unable to submit order.', 'error');
            resetIdempotencyKey(form);
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    };

    root.addEventListener('input', incompleteHandler);
    root.addEventListener('change', incompleteHandler);
    root.addEventListener('submit', submitHandler);

    return () => {
        window.clearTimeout(incompleteTimer);
        root.removeEventListener('input', incompleteHandler);
        root.removeEventListener('change', incompleteHandler);
        root.removeEventListener('submit', submitHandler);
    };
}
