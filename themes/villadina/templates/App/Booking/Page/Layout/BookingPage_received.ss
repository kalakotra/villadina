<section class="vd-section">
    <div class="container" style="max-width: 760px;">
        <div class="text-center" data-reveal>
            <div class="vd-eyebrow justify-content-center"><%t VDBooking.RequestSent 'Request sent' %></div>
            <h1 class="vd-display mb-4"><%t VDBooking.ThankYou 'Thank you!' %></h1>
            <p class="vd-lead mx-auto">
                <%t VDBooking.ReceivedText 'We have received your request and will check availability. You will receive an e-mail with a payment link within 24 hours.' %>
            </p>
            <% if $Reference %>
                <p class="mt-4"><span class="vd-pay__status"><%t VDBooking.Reference 'Reference' %>: $Reference</span></p>
            <% end_if %>
            <a href="$BaseHref" class="vd-link-arrow mt-4">$Icon('arrow-left') <%t VillaDina.Home 'Home' %></a>
        </div>
    </div>
</section>
