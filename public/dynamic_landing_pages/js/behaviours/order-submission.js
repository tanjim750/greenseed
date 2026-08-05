function getFieldValue(form, name) {
    const field = form.elements.namedItem(name);

    return field ? field.value : null;
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

    root.addEventListener('submit', submitHandler);

    return () => {
        root.removeEventListener('submit', submitHandler);
    };
}
