<div class="vd-hero<% if $Size == 'medium' %> vd-hero--medium<% end_if %>">
    <div class="vd-hero__media">
        <% if $HasVideo %>
            <video autoplay muted loop playsinline preload="metadata"
                   <% if $PosterImage %>poster="$PosterImage.FocusFill(1920,1080).URL"<% end_if %>>
                <source src="$Video.URL" type="video/<% if $Video.Extension == 'webm' %>webm<% else %>mp4<% end_if %>">
            </video>
        <% else_if $HasSlideshow %>
            <div class="swiper" data-swiper="hero">
                <div class="swiper-wrapper">
                    <% loop $SortedSlides %>
                        <div class="swiper-slide">
                            <img src="$FocusFill(2400,1400).URL"
                                 srcset="$FocusFill(900,1200).URL 900w, $FocusFill(1600,1000).URL 1600w, $FocusFill(2400,1400).URL 2400w"
                                 sizes="100vw" alt="$Title.ATT"
                                 <% if $IsFirst %>fetchpriority="high"<% else %>loading="lazy"<% end_if %>>
                        </div>
                    <% end_loop %>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        <% else_if $PosterImage %>
            <% with $PosterImage %>
                <img src="$FocusFill(2400,1400).URL"
                     srcset="$FocusFill(900,1200).URL 900w, $FocusFill(1600,1000).URL 1600w, $FocusFill(2400,1400).URL 2400w"
                     sizes="100vw" alt="$Title.ATT" fetchpriority="high">
            <% end_with %>
        <% end_if %>
    </div>

    <div class="vd-hero__content">
        <div class="container">
            <% if $Eyebrow %><div class="vd-eyebrow vd-hero__eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h1 class="vd-display vd-hero__title">$TitleHTML</h1><% end_if %>
            <% if $Lead %><p class="vd-hero__text">$Lead</p><% end_if %>

            <% if $PrimaryLink.exists || $SecondaryLink.exists %>
                <div class="d-flex flex-wrap gap-3">
                    <% with $PrimaryLink %><% if $exists %><a href="$URL" class="btn btn-light"<% if $OpenInNew %> target="_blank" rel="noopener"<% end_if %>>$Title</a><% end_if %><% end_with %>
                    <% with $SecondaryLink %><% if $exists %><a href="$URL" class="btn btn-outline-light"<% if $OpenInNew %> target="_blank" rel="noopener"<% end_if %>>$Title</a><% end_if %><% end_with %>
                </div>
            <% end_if %>

            <% if $ShowBooking && $SiteConfig.InquiryLink %>
                <div class="vd-hero__booking d-none d-md-block">
                    <% include BookingWidget %>
                </div>
            <% end_if %>
        </div>
    </div>

    <span class="vd-hero__scroll" aria-hidden="true"><%t VillaDina.Scroll 'Scroll' %></span>
</div>
