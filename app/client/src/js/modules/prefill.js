/**
 * Prefill UserForms fields from booking-widget query params.
 * In the CMS, give the UserForms field the "Extra CSS class":
 *   prefill-arrival | prefill-departure | prefill-guests
 */
export function initPrefill() {
    const form = document.querySelector('form.userform');
    if (!form) return;

    const params = new URLSearchParams(window.location.search);
    ['arrival', 'departure', 'guests'].forEach((key) => {
        const value = params.get(key);
        if (!value) return;
        const input = form.querySelector(`.prefill-${key} input, .prefill-${key} select, input.prefill-${key}`);
        if (input && !input.value) {
            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}
