<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>$Subject</title>
</head>
<body style="margin:0;padding:0;background:#f1ece3;font-family:Arial,Helvetica,sans-serif;color:#1c2226;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1ece3;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="background:#123a48;padding:28px 36px;color:#ffffff;">
                        <div style="font-family:Georgia,'Times New Roman',serif;font-size:28px;line-height:1;">$SiteConfig.Title</div>
                        <% if $SiteConfig.VillaSubtitle %><div style="margin-top:6px;font-size:11px;letter-spacing:2px;text-transform:uppercase;opacity:.75;">$SiteConfig.VillaSubtitle</div><% end_if %>
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px;">
                        <h1 style="margin:0 0 16px;font-family:Georgia,'Times New Roman',serif;font-weight:normal;font-size:26px;line-height:1.25;color:#1c2226;">$Heading</h1>
                        <% if not $IsOwner %>
                            <p style="margin:0 0 8px;font-size:15px;line-height:1.6;"><%t VDBooking.MailHello 'Dear {name},' name=$Reservation.FirstName %></p>
                        <% end_if %>
                        <p style="margin:0 0 24px;font-size:15px;line-height:1.6;">$Intro</p>

                        <% if $ButtonLink %>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
                                <tr>
                                    <td style="background:#1d566a;border-radius:999px;">
                                        <a href="$ButtonLink" style="display:inline-block;padding:14px 28px;font-size:13px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#ffffff;text-decoration:none;">$ButtonText</a>
                                    </td>
                                </tr>
                            </table>
                        <% end_if %>

                        <% if $ShowSummary %>
                            <% with $Reservation %>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.5;border-top:1px solid #ddd5c7;">
                                    <tr><td style="padding:10px 0;color:#686f73;"><%t VDBooking.Reference 'Reference' %></td><td align="right" style="padding:10px 0;"><strong>$Reference</strong></td></tr>
                                    <tr><td style="padding:6px 0;color:#686f73;"><%t VDBooking.Arrival 'Arrival' %></td><td align="right" style="padding:6px 0;">$Arrival.Format('dd.MM.y')</td></tr>
                                    <tr><td style="padding:6px 0;color:#686f73;"><%t VDBooking.Departure 'Departure' %></td><td align="right" style="padding:6px 0;">$Departure.Format('dd.MM.y') ($Nights <%t VillaDina.Nights 'nights' %>)</td></tr>
                                    <tr><td style="padding:6px 0 12px;color:#686f73;"><%t VillaDina.Guests 'Guests' %></td><td align="right" style="padding:6px 0 12px;">$GuestCount</td></tr>
                                    <% loop $BreakdownLines %>
                                        <tr><td style="padding:4px 0;border-top:1px solid #f1ece3;">$Label</td><td align="right" style="padding:4px 0;border-top:1px solid #f1ece3;">$Formatted</td></tr>
                                    <% end_loop %>
                                    <tr><td style="padding:12px 0;border-top:1px solid #1c2226;"><strong><%t VDBooking.Total 'Total' %></strong></td><td align="right" style="padding:12px 0;border-top:1px solid #1c2226;"><strong>$TotalFormatted</strong></td></tr>
                                    <% if $DepositDueDate %>
                                        <% if $IsFullPaymentUpfront %>
                                            <tr><td style="padding:4px 0;color:#686f73;"><%t VDBooking.FullBy 'Full amount by' %> $DepositDueDate.Format('dd.MM.y')</td><td align="right" style="padding:4px 0;">$TotalFormatted</td></tr>
                                        <% else %>
                                            <tr><td style="padding:4px 0;color:#686f73;"><%t VDBooking.DepositBy 'Deposit by' %> $DepositDueDate.Format('dd.MM.y')</td><td align="right" style="padding:4px 0;">$DepositFormatted</td></tr>
                                            <tr><td style="padding:4px 0;color:#686f73;"><%t VDBooking.BalanceBy 'Balance by' %> $BalanceDueDate.Format('dd.MM.y')</td><td align="right" style="padding:4px 0;">$BalanceFormatted</td></tr>
                                        <% end_if %>
                                        <tr><td style="padding:4px 0;color:#686f73;"><%t VDBooking.Paid 'Paid' %></td><td align="right" style="padding:4px 0;">$PaidFormatted</td></tr>
                                    <% end_if %>
                                </table>
                                <% if $Up.IsOwner %>
                                    <p style="margin:20px 0 0;font-size:13px;line-height:1.6;color:#686f73;">
                                        $GuestName · <a href="mailto:$Email" style="color:#1d566a;">$Email</a> · $Phone<br>
                                        $Street, $PostalCode $City, $Country
                                    </p>
                                <% end_if %>
                            <% end_with %>
                        <% end_if %>

                        <% if $Outro %><p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#686f73;">$Outro</p><% end_if %>
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 36px;background:#fbfaf7;font-size:12px;line-height:1.6;color:#686f73;">
                        $SiteConfig.Title<% if $SiteConfig.Address %> · $SiteConfig.Address<% end_if %><br>
                        <% if $SiteConfig.Phone %>$SiteConfig.Phone · <% end_if %><% if $SiteConfig.Email %><a href="mailto:$SiteConfig.Email" style="color:#1d566a;">$SiteConfig.Email</a><% end_if %>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
