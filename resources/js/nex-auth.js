document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Password Toggle
    |--------------------------------------------------------------------------
    */

    const toggleButtons = document.querySelectorAll(
        '[data-password-toggle]'
    );

    toggleButtons.forEach(button => {

        button.addEventListener('click', () => {

            const inputId = button.dataset.passwordToggle;
            const input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            const icon = button.querySelector('i');

            if (input.type === 'password') {

                input.type = 'text';

                button.setAttribute(
                    'aria-label',
                    'Hide password'
                );

                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }

            } else {

                input.type = 'password';

                button.setAttribute(
                    'aria-label',
                    'Show password'
                );

                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Input Focus Animation
    |--------------------------------------------------------------------------
    */

    const inputs = document.querySelectorAll(
        '.nex-input-wrapper input'
    );

    inputs.forEach(input => {

        input.addEventListener('focus', () => {

            const wrapper = input.closest(
                '.nex-input-wrapper'
            );

            wrapper?.classList.add('is-focused');

        });

        input.addEventListener('blur', () => {

            const wrapper = input.closest(
                '.nex-input-wrapper'
            );

            wrapper?.classList.remove('is-focused');

        });
    });


    /*
    |--------------------------------------------------------------------------
    | Submit Loading State
    |--------------------------------------------------------------------------
    */

    const forms = document.querySelectorAll('.nex-form');

    forms.forEach(form => {

        form.addEventListener('submit', () => {

            const button = form.querySelector('.nex-submit');

            if (!button) {
                return;
            }

            button.classList.add('is-loading');

            button.disabled = true;

            const text = button.querySelector('span');
            const icon = button.querySelector('i');

            if (text) {
                text.textContent = 'Please wait...';
            }

            if (icon) {
                icon.className = 'fa-solid fa-spinner fa-spin';
            }
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Small Card Entrance Animation
    |--------------------------------------------------------------------------
    */

    const card = document.querySelector('.nex-auth-card');

    if (card) {

        requestAnimationFrame(() => {
            card.classList.add('nex-auth-card-visible');
        });
    }

});
