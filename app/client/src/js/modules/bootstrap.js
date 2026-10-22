import Collapse from 'bootstrap/js/dist/collapse';
import Dropdown from 'bootstrap/js/dist/dropdown';
import Offcanvas from 'bootstrap/js/dist/offcanvas';
import Modal from 'bootstrap/js/dist/modal';

export function initBootstrap() {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((el) => Dropdown.getOrCreateInstance(el));
    document.querySelectorAll('.offcanvas').forEach((el) => Offcanvas.getOrCreateInstance(el));
    document.querySelectorAll('.collapse').forEach((el) => Collapse.getOrCreateInstance(el, { toggle: false }));
    document.querySelectorAll('.modal').forEach((el) => Modal.getOrCreateInstance(el));

    // Close mobile offcanvas when an in-page anchor is clicked
    document.querySelectorAll('.offcanvas a[href^="#"]').forEach((link) => {
        link.addEventListener('click', () => {
            const oc = link.closest('.offcanvas');
            if (oc) Offcanvas.getInstance(oc)?.hide();
        });
    });
}
