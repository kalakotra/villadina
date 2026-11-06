<%-- Scope: Place. "Answer box" on articles: where, how far, how to get there. --%>
<aside class="vd-place-facts" aria-label="<%t VillaDina.QuickFacts 'Quick facts' %>">
    <div class="vd-place-facts__title"><%t VillaDina.QuickFacts 'Quick facts' %></div>
    <dl>
        <% if $DriveKmLabel %>
            <div><dt><%t VillaDina.DistanceFromVilla 'Distance from Villa Dina' %></dt><dd>$DriveKmLabel</dd></div>
        <% else_if $DriveLabel %>
            <div><dt><%t VillaDina.DistanceFromVilla 'Distance from Villa Dina' %></dt><dd>$DriveLabel</dd></div>
        <% end_if %>
        <% if $DriveTimeLabel %>
            <div><dt><%t VillaDina.DriveTime 'Driving time' %></dt><dd>$DriveTimeLabel</dd></div>
        <% end_if %>
        <div><dt><%t VillaDina.Type 'Type' %></dt><dd>$KindLabel<% if $BlueFlag %> · <%t VillaDina.BlueFlag 'Blue Flag' %><% end_if %></dd></div>
        <% if $HasGeo %>
            <div><dt>GPS</dt><dd><a href="$MapURL.ATT" target="_blank" rel="noopener nofollow">$Latitude, $Longitude</a></dd></div>
        <% end_if %>
    </dl>
    <% if $HasGeo %>
        <a href="$DirectionsFromVillaURL.ATT" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener nofollow">
            <%t VillaDina.RouteFromVilla 'Route from the villa' %>
        </a>
    <% end_if %>
</aside>
