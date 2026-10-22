/**
 * Header state:
 *  - .is-scrolled after 24px (solid background)
 *  - .is-hidden when scrolling down past hero, shown again on scroll up
 *  - .has-hero on <body> keeps the header transparent over the hero image
 */
export function initHeader() {
    const header = document.querySelector('.vd-header');
    if (!header) return;

    let lastY = window.scrollY;
    let ticking = false;

    const update = () => {
        const y = window.scrollY;
        header.classList.toggle('is-scrolled', y > 24);
        header.classList.toggle('is-hidden', y > 400 && y > lastY + 4);
        if (y < lastY - 4 || y <= 400) header.classList.remove('is-hidden');
        lastY = y;
        ticking = false;
    };

    window.addEventListener(
        'scroll',
        () => {
            if (!ticking) {
                window.requestAnimationFrame(update);
                ticking = true;
            }
        },
        { passive: true },
    );

    update();
}
