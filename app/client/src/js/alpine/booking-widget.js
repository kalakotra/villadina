import { fetchBlockedNights, mountRangePicker } from '../booking/picker';

/**
 * Compact availability widget (hero, booking element).
 * Forwards dates + guests to the booking page.
 *
 * data-endpoint   /booking/availability
 * data-action     booking page URL
 * data-locale     hr|de|en
 * data-max-guests fallback max guests
 */
export default () => ({
    arrival: '',
    departure: '',
    guests: 2,
    maxGuests: 8,
    loading: true,
    error: '',
    picker: null,

    get nights() {
        if (!this.arrival || !this.departure) return 0;
        return Math.round((new Date(this.departure) - new Date(this.arrival)) / 86400000);
    },

    async init() {
        const ds = this.$el.dataset;
        this.maxGuests = parseInt(ds.maxGuests || this.maxGuests, 10);

        const { blocked, maxGuests } = await fetchBlockedNights(ds.endpoint);
        if (maxGuests) this.maxGuests = maxGuests;

        this.picker = await mountRangePicker(this.$refs.range, {
            blocked,
            locale: ds.locale,
            onChange: (a, d) => {
                this.error = '';
                this.arrival = a;
                this.departure = d;
            },
        });
        this.loading = false;
    },

    submit() {
        if (!this.arrival || !this.departure) {
            this.error = this.$el.dataset.errorText || '';
            this.picker?.open();
            return;
        }
        const url = new URL(this.$el.dataset.action, window.location.origin);
        url.searchParams.set('arrival', this.arrival);
        url.searchParams.set('departure', this.departure);
        url.searchParams.set('adults', String(this.guests));
        window.location.href = url.toString();
    },

    destroy() {
        this.picker?.destroy();
    },
});
