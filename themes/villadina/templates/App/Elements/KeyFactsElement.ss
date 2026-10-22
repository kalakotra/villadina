<div class="container">
    <% if $ShowTitle || $Content %>
        <div class="row vd-el__head">
            <div class="col-lg-5">
                <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
                <% if $ShowTitle %><h2 data-reveal>$TitleHTML</h2><% end_if %>
            </div>
            <% if $Content %>
                <div class="col-lg-6 offset-lg-1 vd-lead" data-reveal>$Content</div>
            <% end_if %>
        </div>
    <% end_if %>

    <ul class="vd-facts" style="--vd-facts-cols: $SortedFacts.Count;">
        <% loop $SortedFacts %>
            <li data-reveal style="--reveal-delay: {$Pos}00ms">
                <span class="vd-facts__value">$Value</span>
                <span class="vd-facts__label">$Label</span>
            </li>
        <% end_loop %>
    </ul>
</div>
