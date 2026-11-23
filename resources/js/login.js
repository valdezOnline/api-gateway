const form = document.querySelector('#login-form');

if (form) {
    const submitButton = form.querySelector('#submit-button');
    const buttonLabel = form.querySelector('.submit-button__label');
    const status = form.querySelector('#form-status');
    const username = form.querySelector('#user_name');
    const password = form.querySelector('#password');
    const togglePassword = form.querySelector('#toggle-password');

    const fieldErrors = {
        user_name: form.querySelector('#username-error'),
        password: form.querySelector('#password-error'),
    };

    const setFieldError = (field, message = '') => {
        const error = fieldErrors[field.name];

        field.setAttribute('aria-invalid', message ? 'true' : 'false');
        error.textContent = message;
        error.hidden = !message;
    };

    const clearErrors = () => {
        setFieldError(username);
        setFieldError(password);
        status.hidden = true;
        status.textContent = '';
        status.classList.remove('form-status--success');
    };

    const setLoading = (loading) => {
        submitButton.disabled = loading;
        submitButton.classList.toggle('is-loading', loading);
        buttonLabel.textContent = loading ? 'Signing in…' : 'Sign in';
        form.setAttribute('aria-busy', loading ? 'true' : 'false');
    };

    togglePassword.addEventListener('click', () => {
        const isVisible = password.type === 'text';

        password.type = isVisible ? 'password' : 'text';
        togglePassword.textContent = isVisible ? 'Show' : 'Hide';
        togglePassword.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
        password.focus();
    });

    [username, password].forEach((field) => {
        field.addEventListener('input', () => setFieldError(field));
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        if (!username.value.trim()) {
            setFieldError(username, 'Enter your username.');
            username.focus();
            return;
        }

        if (!password.value) {
            setFieldError(password, 'Enter your password.');
            password.focus();
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    user_name: username.value.trim(),
                    password: password.value,
                }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (payload.errors) {
                    setFieldError(username, payload.errors.user_name?.[0] ?? '');
                    setFieldError(password, payload.errors.password?.[0] ?? '');
                }

                throw new Error(payload.message || 'Unable to sign in. Please try again.');
            }

            const token = payload.data?.token;

            if (!token) {
                throw new Error('The sign-in response did not include an access token.');
            }

            sessionStorage.setItem('api-gateway-token', token);
            status.textContent = 'Signed in successfully. Your secure session is ready.';
            status.classList.add('form-status--success');
            status.hidden = false;
            buttonLabel.textContent = 'Signed in';
            form.dispatchEvent(new CustomEvent('login:success', { detail: { token } }));
            setTimeout(() => {
                window.location.assign('/applications-access');
            }, 250);
        } catch (error) {
            status.textContent = error instanceof Error
                ? error.message
                : 'Unable to sign in. Please try again.';
            status.hidden = false;
            buttonLabel.textContent = 'Sign in';
        } finally {
            submitButton.disabled = false;
            submitButton.classList.remove('is-loading');
            form.setAttribute('aria-busy', 'false');
        }
    });
}
