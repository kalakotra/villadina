/**
 * Shared date-range picker with occupancy rules.
 *
 * blockedNights = Set of 'Y-m-d' nights that are taken.
 *  - arrival:   the arrival night must be free
 *  - departure: every night between arrival and departure-1 must be free
 *               (the checkout day itself may be someone else's arrival)
 */
export const iso = (d) => {
    const z = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${z(d.getMonth() + 1)}-${z(d.getDate())}`;
};

export const parseIso = (s) => {
    if (!s || !/^\d{4}-\d{2}-\d{2}$/.test(s)) return null;
    const [y, m, d] = s.split('-').map(Number);
    return new Date(y, m - 1, d);
};

const loadLocale = async (code) => {
    if (code === 'de') return (await import('flatpickr/dist/l10n/de.js')).German;
    if (code === 'hr') return (await import('flatpickr/dist/l10n/hr.js')).Croatian;
    return null;
};

export async function fetchBlockedNights(endpoint) {
    if (!endpoint) return { blocked: new Set(), maxGuests: null };
    try {
        const res = await fetch(endpoint, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error(String(res.status));
        const data = await res.json();
        return { blocked: new Set(data.blocked || []), maxGuests: data.maxGuests || null };
    } catch {
        return { blocked: new Set(), maxGuests: null };
    }
}

/**
 * @param {HTMLInputElement} input
 * @param {{ blocked: Set<string>, locale?: string, defaultDates?: string[], inline?: boolean, onChange: (arrival: string, departure: string) => void }} opts
 */
export async function mountRangePicker(input, opts) {
    const [{ default: flatpickr }, locale] = await Promise.all([
        import('flatpickr'),
        loadLocale(opts.locale),
        import('flatpickr/dist/flatpickr.min.css'),
    ]);

    const blocked = opts.blocked;
    const defaults = (opts.defaultDates || []).filter(Boolean);
    let fp = null;

    // Own selection state: flatpickr validates defaultDate before fp.selectedDates exists
    const sel = {
        arrival: parseIso(defaults[0]),
        departure: parseIso(defaults[1]),
        complete: false,
    };

    const nightsFree = (from, to) => {
        for (let d = new Date(from); d < to; d.setDate(d.getDate() + 1)) {
            if (blocked.has(iso(d))) return false;
        }
        return true;
    };

    const isDisabled = (date) => {
        const key = iso(date);

        // Choosing the departure
        if (sel.arrival && !sel.complete) {
            if (date <= sel.arrival) return blocked.has(key); // restarts selection as new arrival
            return !nightsFree(sel.arrival, date);
        }

        // Keep the current departure selectable (it may be the next guest's arrival day)
        if (sel.departure && key === iso(sel.departure)) return false;

        // Choosing the arrival: that night must be free
        return blocked.has(key);
    };

    fp = flatpickr(input, {
        mode: 'range',
        minDate: 'today',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd.m.Y',
        inline: !!opts.inline,
        showMonths: window.innerWidth >= 768 ? 2 : 1,
        defaultDate: defaults,
        disable: [isDisabled],
        locale: locale ? { ...locale, rangeSeparator: ' – ' } : { rangeSeparator: ' – ' },
        onDayCreate: (_d, _s, _fp, dayElem) => {
            if (blocked.has(iso(dayElem.dateObj))) dayElem.classList.add('is-booked');
        },
        onChange: (dates, _str, instance) => {
            sel.arrival = dates[0] || null;
            sel.departure = dates[1] || null;
            sel.complete = dates.length === 2;
            instance.redraw(); // re-evaluate departure rules after picking arrival
            opts.onChange(dates[0] ? iso(dates[0]) : '', dates[1] ? iso(dates[1]) : '');
        },
    });

    sel.complete = !!(sel.arrival && sel.departure && fp.selectedDates.length === 2);

    return fp;
}
