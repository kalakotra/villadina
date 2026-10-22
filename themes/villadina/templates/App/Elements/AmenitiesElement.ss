<div class="container">
    <div class="row vd-el__head">
        <div class="col-lg-5">
            <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h2 data-reveal>$TitleHTML</h2><% end_if %>
        </div>
        <% if $Content %>
            <div class="col-lg-6 offset-lg-1 vd-lead" data-reveal>$Content</div>
        <% end_if %>
    </div>

    <ul class="vd-amenities">
        <% loop $SortedAmenities %>
            <li class="vd-amenities__item" data-reveal style="--reveal-delay: {$Modulus(4)}00ms">
                <span class="vd-amenities__icon">
                    <% if $CustomIcon.exists %>
                        <img src="$CustomIcon.URL" alt="" width="24" height="24">
                    <% else %>
                        $IconHTML
                    <% end_if %>
                </span>
                <div>
                    <h3 class="vd-amenities__title">$Title</h3>
                    <% if $Description %><p class="vd-amenities__text">$Description</p><% end_if %>
                </div>
            </li>
        <% end_loop %>
    </ul>
</div>
