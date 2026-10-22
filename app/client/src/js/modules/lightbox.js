export async function initLightbox() {
    if (!document.querySelector('.glightbox')) return;

    // Loaded on demand — only pages with a gallery pay for it
    const [{ default: GLightbox }] = await Promise.all([
        import('glightbox'),
        import('glightbox/dist/css/glightbox.min.css'),
    ]);

    GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        openEffect: 'fade',
        closeEffect: 'fade',
        autoplayVideos: true,
    });
}
