/*
|--------------------------------------------------------------------------
| NEX HOME PAGE
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Header scroll effect
|--------------------------------------------------------------------------
*/

const header = document.getElementById('header');

if (header) {

    const handleHeaderScroll = () => {

        if (window.scrollY > 20) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

    };

    window.addEventListener(
        'scroll',
        handleHeaderScroll,
        { passive: true }
    );

    handleHeaderScroll();
}


/*
|--------------------------------------------------------------------------
| Mobile navigation
|--------------------------------------------------------------------------
*/

const mobileMenu =
    document.getElementById('mobileMenu');

const navLinks =
    document.getElementById('navLinks');


if (mobileMenu && navLinks) {

    mobileMenu.addEventListener('click', () => {

        navLinks.classList.toggle('active');

        const isOpen =
            navLinks.classList.contains('active');

        mobileMenu.textContent =
            isOpen ? '×' : '☰';

        mobileMenu.setAttribute(
            'aria-label',
            isOpen
                ? 'Close menu'
                : 'Open menu'
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Close menu after navigation
    |--------------------------------------------------------------------------
    */

    navLinks
        .querySelectorAll('a')
        .forEach(link => {

            link.addEventListener('click', () => {

                navLinks.classList.remove(
                    'active'
                );

                mobileMenu.textContent = '☰';

                mobileMenu.setAttribute(
                    'aria-label',
                    'Open menu'
                );

            });

        });

}


/*
|--------------------------------------------------------------------------
| Demo like buttons
|--------------------------------------------------------------------------
|
| These buttons are only for the landing-page preview.
| Real likes will later be handled by Laravel.
|
*/

const likeButtons =
    document.querySelectorAll(
        '.like-button'
    );


likeButtons.forEach(button => {

    button.addEventListener(
        'click',
        () => {

            const count =
                button.querySelector(
                    '.like-count'
                );

            if (!count) {
                return;
            }


            let current =
                parseInt(
                    count.textContent,
                    10
                );


            if (
                button.classList.contains(
                    'liked'
                )
            ) {

                current--;

                button.classList.remove(
                    'liked'
                );

                button.firstChild.textContent =
                    '♡ ';

            } else {

                current++;

                button.classList.add(
                    'liked'
                );

                button.firstChild.textContent =
                    '♥ ';

            }


            count.textContent = current;

        }
    );

});


/*
|--------------------------------------------------------------------------
| Reveal animations
|--------------------------------------------------------------------------
*/

const revealElements =
    document.querySelectorAll(
        '.feature, .stat, .section-heading, .cta-box'
    );


if (
    'IntersectionObserver' in window
) {

    const revealObserver =
        new IntersectionObserver(
            entries => {

                entries.forEach(entry => {

                    if (
                        !entry.isIntersecting
                    ) {
                        return;
                    }


                    entry.target.classList.add(
                        'visible'
                    );


                    revealObserver.unobserve(
                        entry.target
                    );

                });

            },
            {
                threshold: 0.12
            }
        );


    revealElements.forEach(element => {

        element.classList.add(
            'reveal'
        );

        revealObserver.observe(
            element
        );

    });

} else {

    revealElements.forEach(element => {

        element.classList.add(
            'visible'
        );

    });

}


/*
|--------------------------------------------------------------------------
| Smooth anchor navigation
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll(
        'a[href^="#"]'
    )
    .forEach(link => {

        link.addEventListener(
            'click',
            event => {

                const targetId =
                    link.getAttribute(
                        'href'
                    );


                if (
                    !targetId ||
                    targetId === '#'
                ) {
                    return;
                }


                const target =
                    document.querySelector(
                        targetId
                    );


                if (!target) {
                    return;
                }


                event.preventDefault();


                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

            }
        );

    });
