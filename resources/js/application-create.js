const token = sessionStorage.getItem('api-gateway-token');

if (!token) {
    window.location.replace('/login');
}

const form = document.querySelector('#create-application-form');
const statusBanner = document.querySelector('#form-status');
const createButton = document.querySelector('#create-button');
const generateKeyButton = document.querySelector('#generate-key-button');
const signoutButton = document.querySelector('#signout-button');
const abilitiesPreview = document.querySelector('#abilities-preview');
const serviceOptions = document.querySelector('#service-options');
const nameInput = document.querySelector('#app-name');
const descriptionInput = document.querySelector('#app-description');
const createdByInput = document.querySelector('#created-by');
const apiKeyInput = document.querySelector('#api-key');

if (token && form && statusBanner && createButton && generateKeyButton && signoutButton && abilitiesPreview && serviceOptions && nameInput && descriptionInput && createdByInput && apiKeyInput) {
    let serviceCatalog = [];

    const fieldErrors = {
        name: document.querySelector('#name-error'),
        description: document.querySelector('#description-error'),
        createdBy: document.querySelector('#createdby-error'),
        apikey: document.querySelector('#apikey-error'),
        services: document.querySelector('#services-error'),
    };

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
        setFieldError('services');
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

    const renderServiceOptions = () => {
        if (!Array.isArray(serviceCatalog) || serviceCatalog.length === 0) {
            serviceOptions.innerHTML = '<p class="helper-text">No service folders were found under app/Services.</p>';
            return;
        }

        serviceOptions.innerHTML = serviceCatalog.map((service) => {
            const abilityLabel = service.requiredAbilities.length === 1 ? 'Requires ability' : 'Requires abilities';
            return `
                <label class="service-option">
                    <input type="checkbox" name="services" value="${service.key}">
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
        renderServiceOptions();
        renderAbilitiesPreview();
    };

    const generateApiKey = () => {
        const bytes = new Uint8Array(48);
        crypto.getRandomValues(bytes);
        const tokenHex = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
        apiKeyInput.value = tokenHex;
        setFieldError('apikey');
    };

    const setBusy = (busy) => {
        createButton.disabled = busy;
        createButton.textContent = busy ? 'Creating…' : 'Create application';
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

        if (!apiKeyInput.value || apiKeyInput.value.length < 32) {
            setFieldError('apikey', 'Generate a valid API key (minimum 32 characters).');
            valid = false;
        }

        if (selectedServiceKeys().length === 0) {
            setFieldError('services', 'Select at least one API service.');
            valid = false;
        }

        return valid;
    };

    const createApplication = async () => {
        if (!validateForm()) {
            return;
        }

        setBusy(true);
        setStatus('');

        try {
            const response = await fetch('/api/v1/applications', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    name: nameInput.value.trim(),
                    description: descriptionInput.value.trim(),
                    createdBy: createdByInput.value.trim(),
                    apikey: apiKeyInput.value,
                    status: true,
                    selectedServices: selectedServiceKeys(),
                    allowedAbilities: selectedAbilities(),
                }),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                if (response.status === 401) {
                    sessionStorage.removeItem('api-gateway-token');
                    window.location.replace('/login');
                    return;
                }

                throw new Error(payload.message || 'Unable to create application.');
            }

            const appName = payload.data?.attributes?.name ?? nameInput.value.trim();
            setStatus(`Application "${appName}" created successfully.`, true);
            form.reset();
            generateApiKey();
            renderAbilitiesPreview();
        } catch (error) {
            setStatus(error instanceof Error ? error.message : 'Unable to create application.');
        } finally {
            setBusy(false);
        }
    };

    generateKeyButton.addEventListener('click', generateApiKey);
    signoutButton.addEventListener('click', () => {
        sessionStorage.removeItem('api-gateway-token');
        window.location.assign('/login');
    });
    nameInput.addEventListener('input', () => setFieldError('name'));
    descriptionInput.addEventListener('input', () => setFieldError('description'));
    createdByInput.addEventListener('input', () => setFieldError('createdBy'));
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        await createApplication();
    });

    generateApiKey();
    loadServiceCatalog().catch((error) => {
        setStatus(error instanceof Error ? error.message : 'Unable to load service catalog.');
    });
}
