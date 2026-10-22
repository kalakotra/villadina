<div class="container vd-booking-section">
    <div class="row gy-5 align-items-end">
        <div class="col-lg-5">
            <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h2 data-reveal>$TitleHTML</h2><% end_if %>
            <% if $Content %><div class="vd-prose mt-3">$Content</div><% end_if %>
        </div>

        <div class="col-lg-7" data-reveal>
            <% if $SiteConfig.InquiryLink %>
                <% include BookingWidget Variant='inline' %>
            <% end_if %>

            <% if $Note %><p class="vd-booking-section__note">$Note</p><% end_if %>

            <% if $ShowChannels %>
                <% if $SiteConfig.BookingComURL || $SiteConfig.AirbnbURL %>
                    <div class="vd-booking-section__channels">
                        <% if $SiteConfig.BookingComURL %>
                            <a href="$SiteConfig.BookingComURL" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener">Booking.com</a>
                        <% end_if %>
                        <% if $SiteConfig.AirbnbURL %>
                            <a href="$SiteConfig.AirbnbURL" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener">Airbnb</a>
                        <% end_if %>
                    </div>
                <% end_if %>
            <% end_if %>
        </div>
    </div>
</div>
