<% include PageHeader %>

<section class="vd-section pt-0">
    <div class="container">
        <div class="row gy-5 gx-lg-5">
            <div class="col-lg-7 order-lg-2">
                <% if $Form %>
                    <% if $Content %><div class="vd-prose mb-4">$Content</div><% end_if %>
                    <div class="vd-form-card vd-form">
                        $Form
                    </div>
                <% else %>
                    <%-- "finished" action: $Content holds the on-complete message --%>
                    <div class="vd-form-success vd-prose">$Content</div>
                <% end_if %>
            </div>

            <aside class="col-lg-4 order-lg-1 vd-contact-aside">
                <dl>
                    <% if $SiteConfig.Phone %>
                        <dt><%t VillaDina.Phone 'Phone' %></dt>
                        <dd><a href="$SiteConfig.PhoneHref">$SiteConfig.Phone</a></dd>
                    <% end_if %>
                    <% if $SiteConfig.Email %>
                        <dt><%t VillaDina.Email 'E-mail' %></dt>
                        <dd><a href="mailto:$SiteConfig.Email">$SiteConfig.Email</a></dd>
                    <% end_if %>
                    <% if $SiteConfig.WhatsAppHref %>
                        <dt>WhatsApp</dt>
                        <dd><a href="$SiteConfig.WhatsAppHref" target="_blank" rel="noopener"><%t VillaDina.WriteUs 'Write to us' %></a></dd>
                    <% end_if %>
                    <% if $SiteConfig.Address %>
                        <dt><%t VillaDina.Address 'Address' %></dt>
                        <dd>$SiteConfig.Address</dd>
                    <% end_if %>
                </dl>

                <% if $SiteConfig.BookingComURL || $SiteConfig.AirbnbURL %>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <% if $SiteConfig.BookingComURL %><a href="$SiteConfig.BookingComURL" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener">Booking.com</a><% end_if %>
                        <% if $SiteConfig.AirbnbURL %><a href="$SiteConfig.AirbnbURL" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener">Airbnb</a><% end_if %>
                    </div>
                <% end_if %>
            </aside>
        </div>
    </div>
</section>
