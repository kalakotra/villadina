<section class="vd-section">
    <div class="container">
        <% with $Reservation %>
            <div class="row gy-5 gx-lg-5">
                <div class="col-lg-7">
                    <div class="vd-eyebrow"><%t VDBooking.Booking 'Booking' %> $Reference</div>

                    <% if $Status == 'requested' %>
                        <h1 class="mb-4"><%t VDBooking.PayWaitingTitle 'Your request is being checked' %></h1>
                        <p class="vd-lead"><%t VDBooking.PayWaitingText 'We will send you the payment link as soon as we have confirmed availability.' %></p>

                    <% else_if $DueNowAmount %>
                        <h1 class="mb-3"><%t VDBooking.PayTitle 'Payment' %></h1>
                        <% if $Up.Cancelled %>
                            <p class="alert alert-warning"><%t VDBooking.PayCancelled 'The payment was not completed. You can try again or choose bank transfer.' %></p>
                        <% end_if %>

                        <p class="mb-1">$DueNowKindLabel</p>
                        <div class="vd-pay__amount mb-2">$DueNowFormatted</div>
                        <p class="text-body-secondary"><%t VDBooking.DueBy 'Due by' %> $DueNowDate.Format('dd.MM.y')</p>

                        <div class="row g-4 mt-2">
                            <% if $Up.StripeEnabled %>
                                <div class="col-md-6">
                                    <div class="vd-pay__option">
                                        <h3><%t VDBooking.PayOnline 'Pay online' %></h3>
                                        <p class="text-body-secondary small"><%t VDBooking.PayOnlineText 'Credit card, SEPA direct debit, Apple Pay or Google Pay – processed securely by Stripe.' %></p>
                                        <form method="post" action="$Up.Link('checkout')">
                                            <input type="hidden" name="token" value="$AccessToken">
                                            <input type="hidden" name="$Up.SecurityTokenName" value="$Up.SecurityTokenValue">
                                            <button type="submit" class="btn btn-primary w-100"><%t VDBooking.PayNowAmount 'Pay {amount} now' amount=$DueNowFormatted %></button>
                                        </form>
                                    </div>
                                </div>
                            <% end_if %>

                            <% if $Up.BankEnabled %>
                                <div class="col-md-6">
                                    <div class="vd-pay__option">
                                        <h3><%t VDBooking.BankTransfer 'Bank transfer' %></h3>
                                        <dl class="vd-pay__bank">
                                            <dt><%t VDBooking.AccountHolder 'Account holder' %></dt><dd>$SiteConfig.BankAccountHolder</dd>
                                            <dt>IBAN</dt><dd>$SiteConfig.IBANFormatted</dd>
                                            <% if $SiteConfig.BIC %><dt>BIC</dt><dd>$SiteConfig.BIC</dd><% end_if %>
                                            <dt><%t VDBooking.Amount 'Amount' %></dt><dd>$DueNowFormatted</dd>
                                            <dt><%t VDBooking.PaymentReference 'Payment reference' %></dt><dd>$Up.PaymentReference</dd>
                                        </dl>
                                        <a href="$ProformaLink" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener"><%t VDBooking.DownloadProforma 'Download payment request (PDF)' %></a>
                                    </div>
                                </div>
                            <% end_if %>
                        </div>

                    <% else_if $Status == 'paid' || $Status == 'completed' %>
                        <h1 class="mb-4"><%t VDBooking.PaidTitle 'Fully paid – thank you!' %></h1>
                        <p><span class="vd-pay__status vd-pay__status--ok">$SvgIcon('check') $StatusLabel</span></p>

                    <% else %>
                        <h1 class="mb-4"><%t VDBooking.InactiveTitle 'This booking is no longer active' %></h1>
                        <p class="vd-lead">$StatusLabel. <%t VDBooking.InactiveText 'Please contact us if you have any questions.' %></p>
                    <% end_if %>
                </div>

                <aside class="col-lg-5">
                    <% include BookingSummary %>
                </aside>
            </div>
        <% end_with %>
    </div>
</section>
