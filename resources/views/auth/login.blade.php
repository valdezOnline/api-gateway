<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#003da5">

    <title>Sign in | {{ config('app.name', 'UCR Library API') }}</title>

    @vite(['resources/css/login.css', 'resources/js/login.js'])
</head>

<body>
    <main class="login-shell">
        <section class="brand-panel" aria-labelledby="brand-heading">
            <a class="brand-logo" href="https://library.ucr.edu/" aria-label="UCR Library home">
                <span class="brand-logo__image" aria-hidden="true"></span>
            </a>

            <div class="brand-copy">
                <p class="eyebrow">UCR Library</p>
                <h1 id="brand-heading">One secure gateway to library services.</h1>
                <p>
                    Connect to trusted campus data and library integrations through a
                    single, managed API.
                </p>
            </div>

            <div class="service-status">
                <span class="service-status__dot" aria-hidden="true"></span>
                <span>API services operational</span>
            </div>
        </section>

        <section class="form-panel" aria-labelledby="login-heading">
            <div class="form-panel__inner">
                <div class="mobile-brand" aria-hidden="true">
                    <span class="mobile-brand__mark">UCR</span>
                    <span>Library API</span>
                </div>

                <div class="welcome-copy">
                    <p class="eyebrow">Welcome back</p>
                    <h2 id="login-heading">Sign in to your account</h2>
                    <p>Use your library API credentials to continue.</p>
                </div>

                <form id="login-form" class="login-form" action="{{ url('/api/v1/login') }}" method="post"
                    novalidate>
                    <div id="form-status" class="form-status" role="alert" aria-live="polite" hidden></div>

                    <div class="field">
                        <label for="user_name">Username</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0" />
                            </svg>
                            <input id="user_name" name="user_name" type="text" autocomplete="username"
                                autocapitalize="none" spellcheck="false" placeholder="Enter your username" required
                                aria-describedby="username-error">
                        </div>
                        <p id="username-error" class="field-error" hidden></p>
                    </div>

                    <div class="field">
                        <div class="field-label-row">
                            <label for="password">Password</label>
                            <button id="toggle-password" class="text-button" type="button"
                                aria-controls="password" aria-label="Show password">Show</button>
                        </div>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="5" y="10" width="14" height="11" rx="2" />
                                <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2" />
                            </svg>
                            <input id="password" name="password" type="password"
                                autocomplete="current-password" placeholder="Enter your password" required
                                aria-describedby="password-error">
                        </div>
                        <p id="password-error" class="field-error" hidden></p>
                    </div>

                    <button id="submit-button" class="submit-button" type="submit">
                        <span class="submit-button__label">Sign in</span>
                        <span class="submit-button__spinner" aria-hidden="true"></span>
                        <svg class="submit-button__arrow" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </form>

                <div class="help-box">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M9.8 9a2.3 2.3 0 1 1 3.4 2c-.8.5-1.2 1-1.2 2m0 3h.01" />
                    </svg>
                    <p><strong>Need access?</strong> Contact the Library application support team to
                        request or restore API credentials.</p>
                </div>

                <footer class="form-footer">
                    <span>© {{ now()->year }} The Regents of the University of California</span>
                    <nav aria-label="Legal">
                        <a href="https://www.ucr.edu/privacy.html">Privacy</a>
                        <a href="https://www.ucr.edu/terms.html">Terms</a>
                    </nav>
                </footer>
            </div>
        </section>
    </main>
</body>

</html>
