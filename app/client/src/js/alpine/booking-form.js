import { fetchBlockedNights, mountRangePicker } from '../booking/picker';

/**
 * Booking page: dates (inline calendar), guests, live price from /booking/quote.
 * Hidden fields of the SS form are bound via x-bind:value.
 *
 * data-state            JSON {arrival, departure, adults, children, infants}
 * data-availability     /booking/availability
 * data-quote            /booking/quote
 * data-locale           hr|de|en
 */
export default () => ({
    arrival: '',
    departure: '',
    adults: 2,
    children: 0,
    infants: 0,
    maxGuests: 8,
    quote: null,
    loading: false,
    ready: false,
    timer: null,
    controller: null,

    get totalGuests() {
        return Number(this.adults) + Number(this.children) + Number(this.infants);
    },

    range(from, to) {
        const out = [];
        for (let i = from; i <= to; i++) out.push(i);
        return out;
    },

    async init() {
        const ds = this.$el.dataset;
        try {
            Object.assign(this, JSON.parse(ds.state || '{}'));
        } catch {
            /* ignore */
        }
        this.adults = Number(this.adults) || 2;

        const { blocked, maxGuests } = await fetchBlockedNights(ds.availability);
        if (maxGuests) this.maxGuests = maxGuests;

        await mountRangePicker(this.$refs.range, {
            blocked,
            locale: ds.locale,
            inline: window.innerWidth >= 992,
            defaultDates: [this.arrival, this.departure],
            onChange: (a, d) => {
                this.arrival = a;
                this.departure = d;
            },
        });

        this.$watch('arrival', () => this.refresh());
        this.$watch('departure', () => this.refresh());
        this.$watch('adults', () => this.refresh());
        this.$watch('children', () => this.refresh());
        this.$watch('infants', () => this.refresh());

        this.ready = true;
        this.refresh();
    },

    refresh() {
        clearTimeout(this.timer);
        if (!this.arrival || !this.departure) {
            this.quote = null;
            return;
        }
        this.timer = setTimeout(() => this.fetchQuote(), 200);
    },

    async fetchQuote() {
        this.controller?.abort();
        this.controller = new AbortController();
        this.loading = true;

        const url = new URL(this.$el.dataset.quote, window.location.origin);
        const params = {
            arrival: this.arrival,
            departure: this.departure,
            adults: this.adults,
            children: this.children,
            infants: this.infants,
            locale: this.$el.dataset.locale,
        };
        Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, String(v)));

        try {
            const res = await fetch(url, { headers: { Accept: 'application/json' }, signal: this.controller.signal });
            this.quote = await res.json();
        } catch (e) {
            if (e.name !== 'AbortError') this.quote = { valid: false, errors: [this.$el.dataset.errorText || 'Error'] };
        } finally {
            this.loading = false;
        }
    },

    formatDate(isoDate) {
        if (!isoDate) return '';
        const [y, m, d] = isoDate.split('-');
        return `${d}.${m}.${y}`;
    },
});
