<div class="vd-cta<% if not $BackgroundImage.exists %> vd-cta--plain<% end_if %>">
    <% if $BackgroundImage.exists %>
        <div class="vd-ratio-cover">
            <img src="$BackgroundImage.FocusFill(2400,1300).URL"
                 srcset="$BackgroundImage.FocusFill(1000,1200).URL 1000w, $BackgroundImage.FocusFill(1800,1000).URL 1800w, $BackgroundImage.FocusFill(2400,1300).URL 2400w"
                 sizes="100vw" alt="" loading="lazy">
        </div>
    <% end_if %>

    <div class="container py-5" data-reveal>
        <% if $Eyebrow %><div class="vd-eyebrow<% if $BackgroundImage.exists %> text-white<% end_if %> justify-content-center">$Eyebrow</div><% end_if %>
        <% if $ShowTitle %><h2>$TitleHTML</h2><% end_if %>
        <% if $Lead %><p>$Lead</p><% end_if %>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <% if $UseInquiryLink && $SiteConfig.InquiryLink %>
                <a href="$SiteConfig.InquiryLink" class="btn <% if $BackgroundImage.exists %>btn-light<% else %>btn-primary<% end_if %>">
                    <%t VillaDina.CheckAvailability 'Check availability' %>
                </a>
            <% end_if %>
            <% with $CTALink %>
                <% if $exists %>
                    <a href="$URL" class="btn <% if $Up.BackgroundImage.exists %>btn-outline-light<% else %>btn-outline-dark<% end_if %>"<% if $OpenInNew %> target="_blank" rel="noopener"<% end_if %>>$Title</a>
                <% end_if %>
            <% end_with %>
        </div>
    </div>
</div>
