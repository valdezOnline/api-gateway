<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#003da5">

    <title>Applications Access | {{ config('app.name', 'UCR Library API') }}</title>

    @vite(['resources/css/applications.css', 'resources/js/applications.js'])
</head>

<body>
    <main class="access-shell">
        <header class="access-header">
            <div class="access-brand">
                <span class="access-brand__image" aria-hidden="true"></span>
            </div>
            <button id="logout-button" type="button" class="ghost-button">Sign out</button>
        </header>

        <section class="hero">
            <p class="eyebrow">UCR Library API</p>
            <h1>Application access dashboard</h1>
            <p>Review registered applications and the API services each application can use.</p>
        </section>

        <section class="panel" aria-labelledby="applications-heading">
            <div class="panel__header">
                <h2 id="applications-heading">Applications</h2>
                <div class="panel__actions">
                    <a href="{{ route('applications.create') }}" class="ghost-link">Create application</a>
                    <button id="refresh-button" type="button" class="ghost-button">Refresh</button>
                </div>
            </div>

            <div id="status-banner" class="status-banner" role="status" aria-live="polite" hidden></div>

            <div class="table-wrap" role="region" aria-labelledby="applications-heading">
                <table class="applications-table">
                    <caption class="sr-only">Application access list</caption>
                    <thead>
                        <tr>
                            <th scope="col">Application</th>
                            <th scope="col">Description</th>
                            <th scope="col">Status</th>
                            <th scope="col">Allowed abilities</th>
                            <th scope="col">Accessible API services</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="applications-body"></tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>
