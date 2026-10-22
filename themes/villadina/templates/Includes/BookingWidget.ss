<%-- Availability widget. Params: $Variant ('' | 'inline') --%>
<form class="vd-booking<% if $Variant %> vd-booking--$Variant<% end_if %>"
      x-data="bookingWidget"
      x-on:submit.prevent="submit()"
      data-endpoint="$BookingEndpoint"
      data-action="$SiteConfig.InquiryLink.ATT"
      data-locale="$LocaleCode"
      data-max-guests="$SiteConfig.MaxGuests"
      data-error-text="<%t VDBooking.ErrSelectDates 'Please select arrival and departure.' %>"
      novalidate>

    <div class="vd-booking__field">
        <label for="vd-dates-{$ID}">$Icon('calendar', 'me-1') <%t VillaDina.ArrivalDeparture 'Arrival – Departure' %></label>
        <input type="text" id="vd-dates-{$ID}" class="form-control" x-ref="range"
               placeholder="<%t VillaDina.SelectDates 'Select dates' %>" readonly>
    </div>

    <div class="vd-booking__field">
        <label for="vd-guests-{$ID}">$Icon('users', 'me-1') <%t VillaDina.Guests 'Guests' %></label>
        <select id="vd-guests-{$ID}" class="form-select" x-model.number="guests">
            <template x-for="n in maxGuests" :key="n">
                <option :value="n" x-text="n" :selected="n === guests"></option>
            </template>
        </select>
    </div>

    <button type="submit" class="btn btn-primary" :disabled="loading">
        <%t VillaDina.CheckAvailability 'Check availability' %>
    </button>

    <div class="vd-booking__meta" x-cloak x-show="nights > 0 || error">
        <span x-show="nights > 0">
            <span x-text="nights"></span> <%t VillaDina.Nights 'nights' %>
        </span>
        <span class="vd-booking__error" x-show="error" x-text="error" role="alert"></span>
    </div>
</form>
