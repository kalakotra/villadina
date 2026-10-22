<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: dejavusans, sans-serif; font-size: 9.5pt; color: #1c2226; }
    h1 { font-family: dejavuserif, serif; font-weight: normal; font-size: 22pt; margin: 0 0 4mm; color: #123a48; }
    .muted { color: #686f73; }
    .small { font-size: 8pt; }
    table { border-collapse: collapse; width: 100%; }
    .head td { vertical-align: top; }
    .lines th { text-align: left; font-size: 8pt; text-transform: uppercase; letter-spacing: 1px; color: #686f73; border-bottom: 0.3mm solid #1c2226; padding: 2mm 0; }
    .lines td { padding: 2mm 0; border-bottom: 0.2mm solid #ddd5c7; }
    .num { text-align: right; }
    .total td { font-weight: bold; border-top: 0.4mm solid #1c2226; border-bottom: none; padding-top: 3mm; }
    .due { margin-top: 8mm; padding: 5mm; background: #f1ece3; }
    .due td { padding: 1.2mm 0; }
    .big { font-size: 15pt; font-weight: bold; color: #1d566a; }
</style>
</head>
<body>

<table class="head">
    <tr>
        <td style="width:60%;">
            <div style="font-family: dejavuserif; font-size: 20pt; color:#123a48;">$SiteConfig.Title</div>
            <div class="muted small" style="margin-top:1mm;">$SiteConfig.VillaSubtitle</div>
        </td>
        <td style="width:40%;" class="small">
            <strong>$SiteConfig.OwnerName</strong><br>
            $SiteConfig.OwnerAddress<br>
            <% if $SiteConfig.OwnerTaxID %>OIB / VAT: $SiteConfig.OwnerTaxID<br><% end_if %>
            <% if $SiteConfig.Email %>$SiteConfig.Email<br><% end_if %>
            <% if $SiteConfig.Phone %>$SiteConfig.Phone<% end_if %>
        </td>
    </tr>
</table>

<table class="head" style="margin-top: 14mm;">
    <tr>
        <td style="width:60%;">
            <div class="muted small"><%t VDBooking.BillTo 'Guest' %></div>
            <strong>$GuestName</strong><br>
            <% if $Street %>$Street<br><% end_if %>
            <% if $City %>$PostalCode $City<br><% end_if %>
            $Country
        </td>
        <td style="width:40%;" class="small">
            <table>
                <tr><td class="muted"><%t VDBooking.Reference 'Reference' %></td><td class="num">$Reference</td></tr>
                <tr><td class="muted"><%t VDBooking.IssueDate 'Date' %></td><td class="num">$IssueDate</td></tr>
                <tr><td class="muted"><%t VDBooking.Arrival 'Arrival' %></td><td class="num">$Arrival.Format('dd.MM.y')</td></tr>
                <tr><td class="muted"><%t VDBooking.Departure 'Departure' %></td><td class="num">$Departure.Format('dd.MM.y')</td></tr>
                <tr><td class="muted"><%t VillaDina.Guests 'Guests' %></td><td class="num">$GuestCount</td></tr>
            </table>
        </td>
    </tr>
</table>

<h1 style="margin-top: 12mm;"><%t VDBooking.ProformaTitle 'Payment request' %></h1>

<table class="lines">
    <thead><tr><th><%t VDBooking.Description 'Description' %></th><th class="num"><%t VDBooking.Amount 'Amount' %></th></tr></thead>
    <tbody>
        <% loop $BreakdownLines %>
            <tr><td>$Label</td><td class="num">$Formatted</td></tr>
        <% end_loop %>
        <tr class="total"><td><%t VDBooking.Total 'Total' %></td><td class="num">$TotalFormatted</td></tr>
        <tr><td class="muted"><%t VDBooking.Paid 'Paid' %></td><td class="num muted">$PaidFormatted</td></tr>
    </tbody>
</table>

<div class="due">
    <table>
        <tr>
            <td>$DueNowKindLabel – <%t VDBooking.DueBy 'Due by' %> $DueNowDate.Format('dd.MM.y')</td>
            <td class="num big">$DueNowFormatted</td>
        </tr>
        <% if not $IsFullPaymentUpfront %>
            <% if $Status == 'approved' %>
                <tr><td class="muted small" colspan="2"><%t VDBooking.BalanceBy 'Balance by' %> $BalanceDueDate.Format('dd.MM.y'): $BalanceFormatted</td></tr>
            <% end_if %>
        <% end_if %>
    </table>
    <table style="margin-top: 4mm;" class="small">
        <tr><td class="muted" style="width:35%;"><%t VDBooking.AccountHolder 'Account holder' %></td><td>$SiteConfig.BankAccountHolder</td></tr>
        <tr><td class="muted"><%t VDBooking.Bank 'Bank' %></td><td>$SiteConfig.BankName</td></tr>
        <tr><td class="muted">IBAN</td><td><strong>$SiteConfig.IBANFormatted</strong></td></tr>
        <% if $SiteConfig.BIC %><tr><td class="muted">BIC</td><td>$SiteConfig.BIC</td></tr><% end_if %>
        <tr><td class="muted"><%t VDBooking.PaymentReference 'Payment reference' %></td><td><strong>$PaymentReference</strong></td></tr>
    </table>
</div>

<% if $SiteConfig.CancellationPolicy %>
    <div class="small muted" style="margin-top: 8mm;">
        <strong><%t VDBooking.CancellationPolicy 'Cancellation policy' %></strong><br>
        $SiteConfig.CancellationPolicy
    </div>
<% end_if %>

<p class="small muted" style="margin-top: 8mm;"><%t VDBooking.ProformaNote 'This is a payment request, not an invoice. The invoice is issued after the stay.' %></p>

</body>
</html>
