const token = sessionStorage.getItem('api-gateway-token');

if (!token) {
    window.location.replace('/login');
}

const statusBanner = document.querySelector('#status-banner');
const applicationsBody = document.querySelector('#applications-body');
const refreshButton = document.querySelector('#refresh-button');
const logoutButton = document.querySelector('#logout-button');

if (token && statusBanner && applicationsBody && refreshButton && logoutButton) {
    let serviceCatalog = [];

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const setStatus = (message, isSuccess = false) => {
        statusBanner.textContent = message;
        statusBanner.classList.toggle('status-banner--success', isSuccess);
        statusBanner.hidden = !message;
    };

    const resolveServices = (serviceKeys, abilities) => {
        if (Array.isArray(serviceKeys) && serviceKeys.length > 0) {
            return serviceCatalog
                .filter((service) => serviceKeys.includes(service.key))
                .map((service) => service.name);
        }

        return serviceCatalog
            .filter((service) => (service.requiredAbilities ?? []).every((ability) => abilities.includes(ability)))
            .map((service) => service.name);
    };

    const buildChips = (values, className) => {
        return values.map((value) => `<span class="chip ${className}">${escapeHtml(value)}</span>`).join('');
    };

    const renderEmpty = (message) => {
        applicationsBody.innerHTML = `<tr><td colspan="6" class="empty-state">${escapeHtml(message)}</td></tr>`;
    };

    const renderRows = (applications) => {
        if (!Array.isArray(applications) || applications.length === 0) {
            renderEmpty('No applications were found for this account.');
            return;
        }

        applicationsBody.innerHTML = applications.map((app) => {
            const attributes = app.attributes ?? {};
            const name = attributes.name || `Application #${app.id}`;
            const description = attributes.description || 'No description provided.';
            const allowedAbilities = Array.isArray(attributes.allowedAbilities)
                ? attributes.allowedAbilities
                : [];
            const assignedServices = Array.isArray(attributes.assignedServices)
                ? attributes.assignedServices
                : [];
            const services = resolveServices(assignedServices, allowedAbilities);
            const isActive = Boolean(attributes.status);

            const statusChip = isActive
                ? '<span class="chip">Active</span>'
                : '<span class="chip chip--inactive">Inactive</span>';

            return `
            <tr>
                <td>
                    <div class="application-name">${escapeHtml(name)}</div>
                    <div class="muted">ID: ${escapeHtml(app.id)}</div>
                </td>
                <td>${escapeHtml(description)}</td>
                <td>${statusChip}</td>
                <td>
                    <div class="chip-list">
                        ${allowedAbilities.length > 0
                            ? buildChips(allowedAbilities, '')
                            : '<span class="muted">None</span>'}
                    </div>
                </td>
                <td>
                    <div class="chip-list">
                        ${services.length > 0
                            ? buildChips(services, 'chip--service')
                            : '<span class="muted">No API services mapped</span>'}
                    </div>
                </td>
                <td>
                    <a class="row-action-link" href="/applications-access/${encodeURIComponent(app.id)}/edit">Edit</a>
                </td>
            </tr>
        `;
        }).join('');
    };

    const setBusy = (busy) => {
        refreshButton.disabled = busy;
        refreshButton.textContent = busy ? 'Refreshing…' : 'Refresh';
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

    const fetchApplications = async () => {
        setBusy(true);
        setStatus('');

        try {
            if (serviceCatalog.length === 0) {
                await loadServiceCatalog();
            }

            const response = await fetch('/api/v1/applications', {
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

                throw new Error(payload.message || 'Unable to load applications.');
            }

            const records = Array.isArray(payload.data) ? payload.data : [];
            renderRows(records);
            setStatus(`Loaded ${records.length} application${records.length === 1 ? '' : 's'}.`, true);
        } catch (error) {
            renderEmpty('Unable to load application access right now.');
            setStatus(error instanceof Error ? error.message : 'Unable to load applications.');
        } finally {
            setBusy(false);
        }
    };

    refreshButton.addEventListener('click', fetchApplications);

    logoutButton.addEventListener('click', () => {
        sessionStorage.removeItem('api-gateway-token');
        window.location.assign('/login');
    });

    fetchApplications();
}
