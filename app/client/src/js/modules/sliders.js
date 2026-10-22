import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade, A11y, Keyboard } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const presets = {
    hero: (el) => ({
        modules: [Autoplay, EffectFade, Pagination, A11y],
        effect: 'fade',
        fadeEffect: { crossFade: true },
        loop: true,
        speed: 1400,
        autoplay: reduceMotion ? false : { delay: 6000, disableOnInteraction: false },
        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
    }),
    gallery: (el) => ({
        modules: [Navigation, Pagination, A11y, Keyboard],
        slidesPerView: 1.15,
        spaceBetween: 16,
        keyboard: { enabled: true },
        navigation: {
            nextEl: el.closest('.vd-slider')?.querySelector('.vd-slider__next'),
            prevEl: el.closest('.vd-slider')?.querySelector('.vd-slider__prev'),
        },
        pagination: { el: el.querySelector('.swiper-pagination'), type: 'fraction' },
        breakpoints: {
            768: { slidesPerView: 2.2, spaceBetween: 24 },
            1200: { slidesPerView: 2.6, spaceBetween: 32 },
        },
    }),
    reviews: (el) => ({
        modules: [Navigation, Pagination, Autoplay, A11y],
        slidesPerView: 1,
        spaceBetween: 24,
        loop: el.querySelectorAll('.swiper-slide').length > 3,
        autoplay: reduceMotion ? false : { delay: 8000, pauseOnMouseEnter: true },
        navigation: {
            nextEl: el.closest('.vd-slider')?.querySelector('.vd-slider__next'),
            prevEl: el.closest('.vd-slider')?.querySelector('.vd-slider__prev'),
        },
        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
        breakpoints: {
            992: { slidesPerView: 2, spaceBetween: 32 },
        },
    }),
};

export function initSliders() {
    document.querySelectorAll('[data-swiper]').forEach((el) => {
        const preset = presets[el.dataset.swiper];
        if (!preset || el.swiper) return;
        // eslint-disable-next-line no-new
        new Swiper(el, preset(el));
    });
}
