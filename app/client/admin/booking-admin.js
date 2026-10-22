/* Confirm destructive reservation actions in the CMS (Decline / Cancel). */
document.addEventListener(
    'click',
    (event) => {
        const button = event.target.closest('[data-vd-confirm]');
        if (button && !window.confirm(button.getAttribute('data-vd-confirm'))) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    },
    true,
);
