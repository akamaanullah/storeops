<script>
(function() {
    const MIN_LEN = <?= (int)\App\Core\Validator::MIN_USERNAME_LENGTH ?>;
    const MAX_LEN = <?= (int)\App\Core\Validator::MAX_USERNAME_LENGTH ?>;
    const VALID_PATTERN = /^[a-zA-Z0-9][a-zA-Z0-9_-]*[a-zA-Z0-9]$|^[a-zA-Z0-9]$/;
    const DUPLICATE_MSG = 'This login name is already in use.';

    function validateUsername(value) {
        const username = String(value ?? '').trim();

        if (username === '') {
            return 'Login name is required.';
        }
        if (username.length < MIN_LEN) {
            return 'Login name must be at least ' + MIN_LEN + ' characters long.';
        }
        if (username.length > MAX_LEN) {
            return 'Login name cannot exceed ' + MAX_LEN + ' characters.';
        }
        if (!VALID_PATTERN.test(username)) {
            return 'Use only letters, numbers, underscores, and hyphens.';
        }
        return '';
    }

    function bindLoginNameField(wrapper) {
        const input = wrapper.querySelector('[data-login-name-input]');
        const errorEl = wrapper.querySelector('[data-login-name-error]');
        if (!input || !errorEl) {
            return;
        }

        const checkUrl = wrapper.dataset.loginNameCheckUrl || '';
        const excludeId = wrapper.dataset.loginNameExcludeId || '0';
        const initialName = (wrapper.dataset.loginNameInitial || '').trim();

        let availabilityError = '';
        let checkTimer = null;
        let checkController = null;
        let checkSeq = 0;
        let checkInFlight = false;

        function showError(message) {
            if (!message) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
                input.classList.remove('border-rose-300', 'focus:border-rose-400', 'focus:ring-rose-200');
                input.setCustomValidity('');
                return true;
            }

            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            input.classList.add('border-rose-300', 'focus:border-rose-400', 'focus:ring-rose-200');
            input.setCustomValidity(message);
            return false;
        }

        function currentError() {
            const localError = validateUsername(input.value);
            if (localError) {
                return localError;
            }
            return availabilityError;
        }

        function renderError() {
            return showError(currentError());
        }

        function shouldSkipAvailability(username) {
            return username === '' || username === initialName || validateUsername(username) !== '';
        }

        async function fetchAvailability(username) {
            if (!checkUrl || shouldSkipAvailability(username)) {
                availabilityError = '';
                return '';
            }

            if (checkController) {
                checkController.abort();
            }

            checkController = new AbortController();
            const seq = ++checkSeq;
            checkInFlight = true;

            try {
                const params = new URLSearchParams({ username });
                if (excludeId !== '0') {
                    params.set('exclude_id', excludeId);
                }

                const response = await fetch(checkUrl + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                    signal: checkController.signal,
                });

                if (!response.ok || seq !== checkSeq) {
                    return availabilityError;
                }

                const data = await response.json();
                if (seq !== checkSeq) {
                    return availabilityError;
                }

                availabilityError = data.available ? '' : (data.error || DUPLICATE_MSG);
                return availabilityError;
            } catch (err) {
                if (err.name === 'AbortError') {
                    return availabilityError;
                }
                return availabilityError;
            } finally {
                if (seq === checkSeq) {
                    checkInFlight = false;
                }
            }
        }

        function scheduleAvailabilityCheck() {
            const username = input.value.trim();

            if (checkTimer) {
                clearTimeout(checkTimer);
            }

            if (shouldSkipAvailability(username)) {
                availabilityError = '';
                renderError();
                return;
            }

            checkTimer = setTimeout(async function() {
                await fetchAvailability(username);
                renderError();
            }, 350);
        }

        input.addEventListener('input', function() {
            const localError = validateUsername(input.value);
            if (localError) {
                availabilityError = '';
                if (checkTimer) {
                    clearTimeout(checkTimer);
                }
                if (checkController) {
                    checkController.abort();
                }
                renderError();
                return;
            }
            scheduleAvailabilityCheck();
        });

        input.addEventListener('blur', async function() {
            if (checkTimer) {
                clearTimeout(checkTimer);
                checkTimer = null;
            }

            const localError = validateUsername(input.value);
            if (localError) {
                availabilityError = '';
                renderError();
                return;
            }

            await fetchAvailability(input.value.trim());
            renderError();
        });

        if (errorEl.textContent.trim() !== '') {
            input.classList.add('border-rose-300', 'focus:border-rose-400', 'focus:ring-rose-200');
            input.setCustomValidity(errorEl.textContent.trim());
        }

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                if (checkTimer) {
                    clearTimeout(checkTimer);
                    checkTimer = null;
                }

                const localError = validateUsername(input.value);
                if (localError) {
                    e.preventDefault();
                    availabilityError = '';
                    showError(localError);
                    input.focus();
                    return;
                }

                if (!shouldSkipAvailability(input.value.trim())) {
                    await fetchAvailability(input.value.trim());
                }

                if (checkInFlight) {
                    await fetchAvailability(input.value.trim());
                }

                if (!renderError()) {
                    e.preventDefault();
                    input.focus();
                }
            });
        }
    }

    document.querySelectorAll('[data-login-name-field]').forEach(bindLoginNameField);
})();
</script>
