<section class="vd-section">
    <div class="container">
        <% with $Reservation %>
            <div class="row gy-5 gx-lg-5">
                <div class="col-lg-7">
                    <div class="vd-eyebrow"><%t VDBooking.Booking 'Booking' %> $Reference</div>
                    <% if $Status == 'confirmed' || $Status == 'paid' %>
                        <h1 class="vd-display mb-4"><%t VDBooking.SuccessTitle 'See you in Istria!' %></h1>
                        <p class="vd-lead"><%t VDBooking.SuccessText 'Your payment has been received and your booking is confirmed. A confirmation has been sent to {email}.' email=$Email %></p>
                        <p><span class="vd-pay__status vd-pay__status--ok">$SvgIcon('check') $StatusLabel</span></p>
                    <% else %>
                        <h1 class="mb-4"><%t VDBooking.ProcessingTitle 'Thank you – your payment is being processed' %></h1>
                        <p class="vd-lead"><%t VDBooking.ProcessingText 'Card payments are confirmed within a few minutes, SEPA direct debits can take a few business days. You will receive an e-mail as soon as the payment arrives.' %></p>
                    <% end_if %>
                </div>
                <aside class="col-lg-5">
                    <% include BookingSummary %>
                </aside>
            </div>
        <% end_with %>
    </div>
</section>
