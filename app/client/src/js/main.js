// Self-hosted fonts (no Google Fonts request → GDPR-safe for DACH)
import '@fontsource-variable/manrope';
import '@fontsource/instrument-serif/400.css';
import '@fontsource/instrument-serif/400-italic.css';

import '../scss/main.scss';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import { initBootstrap } from './modules/bootstrap';
import { initSliders } from './modules/sliders';
import { initLightbox } from './modules/lightbox';
import { initHeader } from './modules/header';
import { initReveal } from './modules/reveal';
import { initMaps } from './modules/map';
import { initPrefill } from './modules/prefill';

import bookingWidget from './alpine/booking-widget';
import bookingForm from './alpine/booking-form';
import faqAccordion from './alpine/faq';

Alpine.plugin(collapse);
Alpine.data('bookingWidget', bookingWidget);
Alpine.data('bookingForm', bookingForm);
Alpine.data('faqAccordion', faqAccordion);

window.Alpine = Alpine;
Alpine.start();

const boot = () => {
    initBootstrap();
    initHeader();
    initSliders();
    initLightbox();
    initReveal();
    initMaps();
    initPrefill();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
