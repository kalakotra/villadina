<%-- Scope: Reservation --%>
<div class="vd-summary">
    <h2 class="vd-summary__title"><%t VDBooking.YourStay 'Your stay' %></h2>
    <div class="vd-summary__dates">
        <div><small><%t VDBooking.Arrival 'Arrival' %></small><strong>$Arrival.Format('dd.MM.y')</strong></div>
        <div><small><%t VDBooking.Departure 'Departure' %></small><strong>$Departure.Format('dd.MM.y')</strong></div>
    </div>
    <p class="small text-body-secondary">
        $Nights <%t VillaDina.Nights 'nights' %> ·
        $Adults <%t VDBooking.Adults 'Adults' %><% if $Children %>, $Children <%t VDBooking.Children 'Children 12–17' %><% end_if %><% if $Infants %>, $Infants <%t VDBooking.Infants 'Children 0–11' %><% end_if %>
    </p>
    <ul class="vd-summary__lines">
        <% loop $BreakdownLines %>
            <li><span>$Label</span><span>$Formatted</span></li>
        <% end_loop %>
    </ul>
    <div class="vd-summary__total">
        <span><%t VDBooking.Total 'Total' %></span>
        <strong>$TotalFormatted</strong>
    </div>
    <% if $DepositDueDate %>
        <div class="vd-summary__schedule">
            <% if $IsFullPaymentUpfront %>
                <div class="vd-summary__row"><span><%t VDBooking.FullBy 'Full amount by' %> $DepositDueDate.Format('dd.MM.y')</span><strong>$TotalFormatted</strong></div>
            <% else %>
                <div class="vd-summary__row"><span><%t VDBooking.DepositBy 'Deposit by' %> $DepositDueDate.Format('dd.MM.y')</span><strong>$DepositFormatted</strong></div>
                <div class="vd-summary__row"><span><%t VDBooking.BalanceBy 'Balance by' %> $BalanceDueDate.Format('dd.MM.y')</span><strong>$BalanceFormatted</strong></div>
            <% end_if %>
            <div class="vd-summary__row"><span><%t VDBooking.Paid 'Paid' %></span><strong>$PaidFormatted</strong></div>
        </div>
    <% end_if %>
</div>
