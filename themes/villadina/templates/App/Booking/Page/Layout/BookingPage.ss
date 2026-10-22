<% include PageHeader %>

<section class="vd-section pt-0">
    <div class="container"
         x-data="bookingForm"
         data-state="$InitialStateJSON"
         data-availability="$BookingEndpoint"
         data-quote="{$BaseHref}booking/quote"
         data-locale="$LocaleCode"
         data-error-text="<%t VDBooking.ErrGeneric 'Something went wrong. Please try again or contact us directly.' %>">

        <% if $Content %><div class="vd-lead mb-5">$Content</div><% end_if %>

        <div class="row gy-5 gx-lg-5">
            <div class="col-lg-7">
                <%-- 1. Dates & guests --%>
                <h2 class="vd-step"><span class="vd-step__num">1</span> <%t VDBooking.StepDates 'Dates & guests' %></h2>

                <label class="visually-hidden" for="vd-booking-range"><%t VillaDina.ArrivalDeparture 'Arrival – Departure' %></label>
                <input type="text" id="vd-booking-range" class="form-control" x-ref="range"
                       placeholder="<%t VillaDina.SelectDates 'Select dates' %>" readonly>

                <div class="vd-guests">
                    <div>
                        <label for="vd-adults"><%t VDBooking.Adults 'Adults' %></label>
                        <select id="vd-adults" class="form-select" x-model.number="adults">
                            <template x-for="n in range(1, maxGuests)" :key="'a' + n">
                                <option :value="n" x-text="n" :selected="n === adults" :disabled="n + children + infants > maxGuests"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label for="vd-children"><%t VDBooking.Children 'Children 12–17' %></label>
                        <select id="vd-children" class="form-select" x-model.number="children">
                            <template x-for="n in range(0, maxGuests - 1)" :key="'c' + n">
                                <option :value="n" x-text="n" :selected="n === children" :disabled="adults + n + infants > maxGuests"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label for="vd-infants"><%t VDBooking.Infants 'Children 0–11' %></label>
                        <select id="vd-infants" class="form-select" x-model.number="infants">
                            <template x-for="n in range(0, maxGuests - 1)" :key="'i' + n">
                                <option :value="n" x-text="n" :selected="n === infants" :disabled="adults + children + n > maxGuests"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <%-- 2. Guest details --%>
                <h2 class="vd-step mt-5"><span class="vd-step__num">2</span> <%t VDBooking.StepDetails 'Your details' %></h2>
                $RequestForm

                <p class="small text-body-secondary mt-3">
                    <%t VDBooking.RequestNote 'This is a non-binding request. We check availability and send you a payment link. Your booking is confirmed once the deposit has been received.' %>
                </p>
            </div>

            <%-- Summary --%>
            <aside class="col-lg-5">
                <div class="vd-summary" :class="{ 'is-loading': loading }" aria-live="polite">
                    <h2 class="vd-summary__title"><%t VDBooking.YourStay 'Your stay' %></h2>

                    <div class="vd-summary__dates">
                        <div><small><%t VDBooking.Arrival 'Arrival' %></small><strong x-text="formatDate(arrival) || '—'">—</strong></div>
                        <div><small><%t VDBooking.Departure 'Departure' %></small><strong x-text="formatDate(departure) || '—'">—</strong></div>
                    </div>

                    <template x-if="!quote">
                        <p class="vd-summary__empty mb-0"><%t VDBooking.SelectToSeePrice 'Select your dates to see the price.' %></p>
                    </template>

                    <template x-if="quote && !quote.valid">
                        <ul class="vd-summary__errors">
                            <template x-for="err in quote.errors" :key="err"><li x-text="err"></li></template>
                        </ul>
                    </template>

                    <template x-if="quote && quote.valid">
                        <div>
                            <ul class="vd-summary__lines">
                                <template x-for="line in quote.lines" :key="line.label">
                                    <li><span x-text="line.label"></span><span x-text="line.amount"></span></li>
                                </template>
                                <li x-show="quote.touristTax.cents > 0" :class="{ 'is-muted': !quote.touristTax.included }">
                                    <span x-text="quote.touristTax.label"></span><span x-text="quote.touristTax.amount"></span>
                                </li>
                            </ul>

                            <div class="vd-summary__total">
                                <span><%t VDBooking.Total 'Total' %></span>
                                <strong x-text="quote.total"></strong>
                            </div>

                            <div class="vd-summary__schedule">
                                <template x-if="!quote.fullUpfront">
                                    <div>
                                        <div class="vd-summary__row"><span><%t VDBooking.DepositAfterApproval 'Deposit after confirmation' %></span><strong x-text="quote.deposit"></strong></div>
                                        <div class="vd-summary__row"><span><%t VDBooking.BalanceBy 'Balance by' %> <span x-text="formatDate(quote.balanceDue)"></span></span><strong x-text="quote.balance"></strong></div>
                                    </div>
                                </template>
                                <template x-if="quote.fullUpfront">
                                    <div class="vd-summary__row"><span><%t VDBooking.FullAfterApproval 'Full amount after confirmation' %></span><strong x-text="quote.total"></strong></div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <% if $SiteConfig.CancellationPolicy %>
                        <details class="mt-4 small">
                            <summary><%t VDBooking.CancellationPolicy 'Cancellation policy' %></summary>
                            <div class="mt-2 text-body-secondary">$SiteConfig.CancellationPolicy</div>
                        </details>
                    <% end_if %>
                </div>
            </aside>
        </div>
    </div>
</section>
