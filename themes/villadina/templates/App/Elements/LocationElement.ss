<div class="container">
    <div class="row gy-5 gx-lg-5 align-items-center">
        <div class="col-lg-5" data-reveal>
            <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h2>$TitleHTML</h2><% end_if %>
            <% if $Content %><div class="vd-prose mt-4">$Content</div><% end_if %>

            <% if $SortedDistances.Count %>
                <ul class="vd-distances">
                    <% loop $SortedDistances %>
                        <li>
                            <span class="d-inline-flex align-items-center gap-2">
                                <% if $IconKey %>$Icon($IconKey)<% end_if %>
                                $Title
                            </span>
                            <span class="vd-distances__value">$Value</span>
                        </li>
                    <% end_loop %>
                </ul>
            <% end_if %>

            <% if $DirectionsURL %>
                <a href="$DirectionsURL" class="vd-link-arrow mt-4" target="_blank" rel="noopener">
                    <%t VillaDina.Directions 'Get directions' %> $Icon('arrow-right')
                </a>
            <% end_if %>
        </div>

        <% if $HasCoordinates %>
            <div class="col-lg-7" data-reveal style="--reveal-delay: 150ms">
                <div class="vd-map" data-map-src="$MapEmbedURL.ATT" data-map-title="$TitlePlain.ATT">
                    <div class="vd-map__consent">
                        <div>
                            <span class="vd-map__pin">$Icon('pin')</span>
                            <p>
                                <% if $MapProvider == 'google' %>
                                    <%t VillaDina.MapConsentGoogle 'Loading the map connects to Google Maps and transfers data to Google.' %>
                                <% else %>
                                    <%t VillaDina.MapConsentOSM 'Loading the map connects to OpenStreetMap servers.' %>
                                <% end_if %>
                            </p>
                            <button type="button" class="btn btn-primary btn-sm" data-map-load>
                                <%t VillaDina.LoadMap 'Load map' %>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <% end_if %>
    </div>
</div>
