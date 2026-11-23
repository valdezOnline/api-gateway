<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#003da5">

    <title>Edit Application | {{ config('app.name', 'UCR Library API') }}</title>

    @vite(['resources/css/application-create.css', 'resources/js/application-edit.js'])
</head>

<body>
    <main class="create-shell">
        <header class="create-header">
            <a href="{{ route('applications.access') }}" class="back-link">← Back to applications</a>
            <button id="signout-button" type="button" class="ghost-button">Sign out</button>
        </header>

        <section class="hero">
            <p class="eyebrow">UCR Library API</p>
            <h1>Edit application</h1>
            <p>Assign or revoke services, renew/regenerate keys, and manage API key expiration.</p>
        </section>

        <section class="panel" aria-labelledby="edit-application-heading">
            <h2 id="edit-application-heading">Application details</h2>

            <form id="edit-application-form" class="create-form" novalidate>
                <div id="form-status" class="status-banner" role="alert" aria-live="polite" hidden></div>

                <div class="field-grid">
                    <div class="field">
                        <label for="app-name">Application name</label>
                        <input id="app-name" name="name" type="text" maxlength="255" required>
                        <p id="name-error" class="field-error" hidden></p>
                    </div>

                    <div class="field">
                        <label for="created-by">Created by</label>
                        <input id="created-by" name="createdBy" type="text" maxlength="255" required>
                        <p id="createdby-error" class="field-error" hidden></p>
                    </div>
                </div>

                <div class="field">
                    <label for="app-description">Description</label>
                    <textarea id="app-description" name="description" rows="3" maxlength="1000" required></textarea>
                    <p id="description-error" class="field-error" hidden></p>
                </div>

                <div class="field">
                    <label for="api-expires-at">API key expiration date</label>
                    <input id="api-expires-at" name="apiKeyExpiresAt" type="date" required>
                    <p class="helper-text">Default is 1 year from now. Maximum validity is 365 days (industry-standard rotation window).</p>
                    <p id="expires-error" class="field-error" hidden></p>
                </div>

                <div class="field">
                    <label for="api-key">Regenerate API key (optional)</label>
                    <div class="key-action-row">
                        <input id="api-key" name="apikey" type="text" minlength="32" readonly
                            placeholder="Leave unchanged unless you click Regenerate key">
                        <button id="regenerate-key-button" type="button" class="ghost-button">Regenerate key</button>
                    </div>
                    <p class="helper-text">Regenerating creates a new key and updates key rotation timestamp.</p>
                    <p id="apikey-error" class="field-error" hidden></p>
                </div>

                <fieldset class="service-fieldset">
                    <legend>API services access</legend>
                    <p class="helper-text">Select services to assign access, or uncheck to revoke access.</p>
                    <div id="service-options" class="service-options" aria-live="polite"></div>
                    <p id="services-error" class="field-error" hidden></p>
                </fieldset>

                <div class="field">
                    <span class="label">Computed allowed abilities</span>
                    <div id="abilities-preview" class="abilities-preview">
                        <span class="muted">No services selected.</span>
                    </div>
                </div>

                <div class="field">
                    <span class="label">Current key lifecycle</span>
                    <ul id="key-metadata" class="metadata-list"></ul>
                </div>

                <div class="form-actions">
                    <button id="renew-button" type="button" class="secondary-button">Renew key for 1 year</button>
                    <button id="save-button" type="submit" class="primary-button">Save changes</button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
