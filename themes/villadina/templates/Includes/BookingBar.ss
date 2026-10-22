<% if $SiteConfig.InquiryLink %>
    <div class="vd-bookbar">
        <div class="vd-bookbar__text">
            <% if $SiteConfig.PriceFrom %>
                <strong>$SiteConfig.PriceFrom</strong>
            <% else %>
                <strong>$SiteConfig.Title</strong>
            <% end_if %>
            <%t VillaDina.UpToGuests 'up to {count} guests' count=$SiteConfig.MaxGuests %>
        </div>
        <a href="$SiteConfig.InquiryLink" class="btn btn-primary"><%t VillaDina.BookNow 'Book now' %></a>
    </div>
<% end_if %>
