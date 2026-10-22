/**
 * GDPR-friendly map: the iframe (OpenStreetMap or Google) is only loaded
 * after the visitor clicks the consent overlay. Nothing is requested before.
 *
 * Markup: <div class="vd-map" data-map-src="https://..."> ... <button data-map-load> </div>
 */
export function initMaps() {
    document.querySelectorAll('.vd-map[data-map-src]').forEach((wrap) => {
        const btn = wrap.querySelector('[data-map-load]');
        if (!btn) return;

        btn.addEventListener('click', () => {
            const iframe = document.createElement('iframe');
            iframe.src = wrap.dataset.mapSrc;
            iframe.title = wrap.dataset.mapTitle || 'Map';
            iframe.loading = 'lazy';
            iframe.referrerPolicy = 'no-referrer-when-downgrade';
            iframe.allowFullscreen = true;
            wrap.querySelector('.vd-map__consent')?.remove();
            wrap.appendChild(iframe);
            wrap.classList.add('is-loaded');
        });
    });
}
