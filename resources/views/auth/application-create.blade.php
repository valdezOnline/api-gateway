<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#003da5">

    <title>Create Application | {{ config('app.name', 'UCR Library API') }}</title>

    @vite(['resources/css/application-create.css', 'resources/js/application-create.js'])
</head>

<body>
    <main class="create-shell">
        <header class="create-header">
            <a href="{{ route('applications.access') }}" class="back-link">← Back to applications</a>
            <button id="signout-button" type="button" class="ghost-button">Sign out</button>
        </header>

        <section class="hero">
            <p class="eyebrow">UCR Library API</p>
            <h1>Create an application</h1>
            <p>Generate a secure API key and choose the API services this application can access.</p>
        </section>

        <section class="panel" aria-labelledby="create-application-heading">
            <h2 id="create-application-heading">Application details</h2>

            <form id="create-application-form" class="create-form" novalidate>
                <div id="form-status" class="status-banner" role="alert" aria-live="polite" hidden></div>

                <div class="field-grid">
                    <div class="field">
                        <label for="app-name">Application name</label>
                        <input id="app-name" name="name" type="text" maxlength="255" required
                            placeholder="Library Discovery App">
                        <p id="name-error" class="field-error" hidden></p>
                    </div>

                    <div class="field">
                        <label for="created-by">Created by</label>
                        <input id="created-by" name="createdBy" type="text" maxlength="255" required
                            placeholder="jvaldez">
                        <p id="createdby-error" class="field-error" hidden></p>
                    </div>
                </div>

                <div class="field">
                    <label for="app-description">Description</label>
                    <textarea id="app-description" name="description" rows="3" maxlength="1000" required
                        placeholder="Describe this application's purpose."></textarea>
                    <p id="description-error" class="field-error" hidden></p>
                </div>

                <div class="field">
                    <div class="field-row">
                        <label for="api-key">API key</label>
                        <button id="generate-key-button" type="button" class="ghost-button">Generate key</button>
                    </div>
                    <input id="api-key" name="apikey" type="text" required minlength="32" readonly
                        placeholder="Generate an API key">
                    <p class="helper-text">The generated key is shown once. Save it securely before leaving this page.</p>
                    <p id="apikey-error" class="field-error" hidden></p>
                </div>

                <fieldset class="service-fieldset">
                    <legend>API services access</legend>
                    <p class="helper-text">Select one or more services. Allowed abilities are derived automatically.</p>
                    <div id="service-options" class="service-options" aria-live="polite"></div>

                    <p id="services-error" class="field-error" hidden></p>
                </fieldset>

                <div class="field">
                    <span class="label">Computed allowed abilities</span>
                    <div id="abilities-preview" class="abilities-preview">
                        <span class="muted">No services selected.</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button id="create-button" type="submit" class="primary-button">Create application</button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
