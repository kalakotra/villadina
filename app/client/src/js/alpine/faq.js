/**
 * Lightweight accessible accordion (one open at a time).
 * Usage: <div x-data="faqAccordion"> ... <button @click="toggle(1)" :aria-expanded="isOpen(1)">
 */
export default () => ({
    open: null,
    toggle(id) {
        this.open = this.open === id ? null : id;
    },
    isOpen(id) {
        return this.open === id;
    },
});
