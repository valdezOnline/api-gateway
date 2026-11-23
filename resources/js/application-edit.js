const token = sessionStorage.getItem('api-gateway-token');

if (!token) {
    window.location.replace('/login');
}

const form = document.querySelector('#edit-application-form');
const statusBanner = document.querySelector('#form-status');
const saveButton = document.querySelector('#save-button');
const renewButton = document.querySelector('#renew-button');
const regenerateKeyButton = document.querySelector('#regenerate-key-button');
const signoutButton = document.querySelector('#signout-button');
const abilitiesPreview = document.querySelector('#abilities-preview');
const serviceOptions = document.querySelector('#service-options');
const keyMetadata = document.querySelector('#key-metadata');
const nameInput = document.querySelector('#app-name');
const descriptionInput = document.querySelector('#app-description');
const createdByInput = document.querySelector('#created-by');
const apiKeyInput = document.querySelector('#api-key');
const expiresAtInput = document.querySelector('#api-expires-at');

if (token && form && statusBanner && saveButton && renewButton && regenerateKeyButton && signoutButton && abilitiesPreview && serviceOptions && keyMetadata && nameInput && descriptionInput && createdByInput && apiKeyInput && expiresAtInput) {
    let serviceCatalog = [];
    let generatedKeyPendingSave = false;
    const pathnameMatch = window.location.pathname.match(/\/applications-access\/([^/]+)\/edit$/);
    const applicationId = pathnameMatch?.[1] ?? '';

    const fieldErrors = {
        name: document.querySelector('#name-error'),
        description: document.querySelector('#description-error'),
        createdBy: document.querySelector('#createdby-error'),
        apikey: document.querySelector('#apikey-error'),
        expiresAt: document.querySelector('#expires-error'),
        services: document.querySelector('#services-error'),
    };

    const today = new Date();
    const maxExpiryDate = new Date(today);
    maxExpiryDate.setDate(maxExpiryDate.getDate() + 365);
    const minDate = today.toISOString().slice(0, 10);
    const maxDate = maxExpiryDate.toISOString().slice(0, 10);
    expiresAtInput.min = minDate;
    expiresAtInput.max = maxDate;

    const setStatus = (message, isSuccess = false) => {
        statusBanner.textContent = message;
        statusBanner.classList.toggle('status-banner--success', isSuccess);
        statusBanner.hidden = !message;
    };

    const setFieldError = (fieldName, message = '') => {
        const errorNode = fieldErrors[fieldName];
        if (!errorNode) {
            return;
        }

        errorNode.textContent = message;
        errorNode.hidden = !message;
    };

    const clearFieldErrors = () => {
        setFieldError('name');
        setFieldError('description');
        setFieldError('createdBy');
        setFieldError('apikey');
        setFieldError('expiresAt');
        setFieldError('services');
    };

    const formatDateInput = (value) => {
        if (!value) {
            return '';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        return date.toISOString().slice(0, 10);
    };

    const formatDateTime = (value) => {
        if (!value) {
            return 'Not set';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return date.toLocaleString();
    };

    const selectedServiceKeys = () => {
        return Array.from(serviceOptions.querySelectorAll('input[name="services"]:checked'))
            .map((input) => input.value);
    };

    const selectedAbilities = () => {
        const selected = selectedServiceKeys();
        const abilities = new Set();

        for (const service of serviceCatalog) {
            if (!selected.includes(service.key)) {
                continue;
            }

            for (const ability of service.requiredAbilities ?? []) {
                abilities.add(ability);
            }
        }

        return Array.from(abilities);
    };

    const renderAbilitiesPreview = () => {
        const abilities = selectedAbilities();
        if (abilities.length === 0) {
            abilitiesPreview.innerHTML = '<span class="muted">No services selected.</span>';
            return;
        }

        abilitiesPreview.innerHTML = abilities
            .map((ability) => `<span class="chip">${ability}</span>`)
            .join('');
    };

    const renderServiceOptions = (selectedServices = []) => {
        serviceOptions.innerHTML = serviceCatalog.map((service) => {
            const abilityLabel = service.requiredAbilities.length === 1 ? 'Requires ability' : 'Requires abilities';
            const checked = selectedServices.includes(service.key) ? 'checked' : '';
            return `
                <label class="service-option">
                    <input type="checkbox" name="services" value="${service.key}" ${checked}>
                    <span>
                        <strong>${service.name}</strong>
                        <small>${abilityLabel}: ${service.requiredAbilities.join(', ')} · Source folder: ${service.sourceFolder}</small>
                    </span>
                </label>
            `;
        }).join('');

        for (const serviceInput of serviceOptions.querySelectorAll('input[name="services"]')) {
            serviceInput.addEventListener('change', () => {
                renderAbilitiesPreview();
                setFieldError('services');
            });
        }
    };

    const renderMetadata = (attributes) => {
        keyMetadata.innerHTML = `
            <li><strong>Rotated at:</strong> ${formatDateTime(attributes.apiKeyRotatedAt)}</li>
            <li><strong>Expires at:</strong> ${formatDateTime(attributes.apiKeyExpiresAt)}</li>
            <li><strong>Revoked at:</strong> ${formatDateTime(attributes.apiKeyRevokedAt)}</li>
        `;
    };

    const loadServiceCatalog = async () => {
        const response = await fetch('/api/v1/service-catalog', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (response.status === 401) {
                sessionStorage.removeItem('api-gateway-token');
                window.location.replace('/login');
                return;
            }

            throw new Error(payload.message || 'Unable to load service catalog.');
        }

        serviceCatalog = Array.isArray(payload.data) ? payload.data : [];
    };

    const loadApplication = async () => {
        if (!applicationId) {
            throw new Error('Unable to determine application ID from URL.');
        }

        const response = await fetch(`/api/v1/applications/${encodeURIComponent(applicationId)}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (response.status === 401) {
                sessionStorage.removeItem('api-gateway-token');
                window.location.replace('/login');
                return;
            }

            throw new Error(payload.message || 'Unable to load application details.');
        }

        const attributes = payload.data?.attributes ?? {};
        nameInput.value = attributes.name ?? '';
        descriptionInput.value = attributes.description ?? '';
        createdByInput.value = attributes.createdBy ?? '';
        expiresAtInput.value = formatDateInput(attributes.apiKeyExpiresAt) || maxDate;
        renderMetadata(attributes);

        const assignedServices = Array.isArray(attributes.assignedServices) ? attributes.assignedServices : [];
        renderServiceOptions(assignedServices);
        renderAbilitiesPreview();
    };

    const generateApiKey = () => {
        const bytes = new Uint8Array(48);
        crypto.getRandomValues(bytes);
        const tokenHex = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
        apiKeyInput.value = tokenHex;
        generatedKeyPendingSave = true;
        setFieldError('apikey');
    };

    const validateForm = () => {
        clearFieldErrors();

        let valid = true;
        if (!nameInput.value.trim()) {
            setFieldError('name', 'Application name is required.');
            valid = false;
        }

        if (!descriptionInput.value.trim()) {
            setFieldError('description', 'Description is required.');
            valid = false;
        }

        if (!createdByInput.value.trim()) {
            setFieldError('createdBy', 'Created by is required.');
            valid = false;
        }

        if (!expiresAtInput.value) {
            setFieldError('expiresAt', 'Expiration date is required.');
            valid = false;
        } else if (expiresAtInput.value < minDate || expiresAtInput.value > maxDate) {
            setFieldError('expiresAt', 'Expiration date must be between tomorrow and one year from today.');
            valid = false;
        }

        if (selectedServiceKeys().length === 0) {
            setFieldError('services', 'Select at least one API service.');
            valid = false;
        }

        if (generatedKeyPendingSave && apiKeyInput.value.length < 32) {
            setFieldError('apikey', 'Regenerated API key is invalid.');
            valid = false;
        }

        return valid;
    };

    const setBusy = (busy) => {
        saveButton.disabled = busy;
        renewButton.disabled = busy;
        saveButton.textContent = busy ? 'Saving…' : 'Save changes';
        renewButton.textContent = busy ? 'Renewing…' : 'Renew key for 1 year';
    };

    const submitUpdate = async (renewOnly = false) => {
        if (!validateForm()) {
            return;
        }

        setBusy(true);
        setStatus('');

        const requestPayload = {
            data: {
                attributes: {
                    name: nameInput.value.trim(),
                    description: descriptionInput.value.trim(),
                    createdBy: createdByInput.value.trim(),
                    selectedServices: selectedServiceKeys(),
                    allowedAbilities: selectedAbilities(),
                    apiKeyExpiresAt: expiresAtInput.value,
                    renewApiKey: renewOnly,
                    status: true,
                },
            },
        };

        if (generatedKeyPendingSave) {
            requestPayload.data.attributes.apikey = apiKeyInput.value;
        }

        try {
            const response = await fetch(`/api/v1/applications/${encodeURIComponent(applicationId)}`, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(requestPayload),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                if (response.status === 401) {
                    sessionStorage.removeItem('api-gateway-token');
                    window.location.replace('/login');
                    return;
                }

                throw new Error(payload.message || 'Unable to update application.');
            }

            const attributes = payload.data?.attributes ?? {};
            renderMetadata(attributes);
            expiresAtInput.value = formatDateInput(attributes.apiKeyExpiresAt) || expiresAtInput.value;
            generatedKeyPendingSave = false;
            apiKeyInput.value = '';
            setStatus(renewOnly ? 'API key renewed successfully.' : 'Application updated successfully.', true);
        } catch (error) {
            setStatus(error instanceof Error ? error.message : 'Unable to update application.');
        } finally {
            setBusy(false);
        }
    };

    regenerateKeyButton.addEventListener('click', generateApiKey);
    signoutButton.addEventListener('click', () => {
        sessionStorage.removeItem('api-gateway-token');
        window.location.assign('/login');
    });
    renewButton.addEventListener('click', async () => {
        const nextYear = new Date();
        nextYear.setDate(nextYear.getDate() + 365);
        expiresAtInput.value = nextYear.toISOString().slice(0, 10);
        await submitUpdate(true);
    });

    nameInput.addEventListener('input', () => setFieldError('name'));
    descriptionInput.addEventListener('input', () => setFieldError('description'));
    createdByInput.addEventListener('input', () => setFieldError('createdBy'));
    expiresAtInput.addEventListener('input', () => setFieldError('expiresAt'));

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        await submitUpdate(false);
    });

    Promise.all([loadServiceCatalog(), loadApplication()]).catch((error) => {
        setStatus(error instanceof Error ? error.message : 'Unable to load application data.');
    });
}
