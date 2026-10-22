<%--
    Existing App\Elements\GalleryElement (many_many Images + SortOrder),
    helpers from App\Extensions\GalleryElementExtension.
--%>
<div class="container vd-gallery vd-slider">
    <% if $ShowTitle %>
        <div class="vd-el__head vd-el__head--split">
            <div>
                <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
                <h2 data-reveal>$Title</h2>
            </div>
            <% if $DisplayMode == 'slider' %>
                <div class="vd-slider__controls">
                    <button class="vd-slider__prev" type="button" aria-label="<%t VillaDina.Previous 'Previous' %>">$SvgIcon('arrow-left')</button>
                    <button class="vd-slider__next" type="button" aria-label="<%t VillaDina.Next 'Next' %>">$SvgIcon('arrow-right')</button>
                </div>
            <% end_if %>
        </div>
    <% end_if %>

    <% if $DisplayMode == 'slider' %>
        <div>
            <div class="vd-gallery__overflow">
                <div class="swiper" data-swiper="gallery">
                    <div class="swiper-wrapper">
                        <% loop $SortedImages %>
                            <div class="swiper-slide">
                                <a href="$FitMax(2400,1600).URL" class="glightbox vd-gallery__slide vd-ratio-cover d-block"
                                   data-gallery="gallery-$Up.ID" data-description="$Title.ATT">
                                    <img src="$FocusFill(1200,900).URL"
                                         srcset="$FocusFill(800,600).URL 800w, $FocusFill(1200,900).URL 1200w"
                                         sizes="(min-width: 1200px) 40vw, (min-width: 768px) 45vw, 90vw"
                                         alt="$Title.ATT" loading="lazy">
                                </a>
                            </div>
                        <% end_loop %>
                    </div>
                    <div class="swiper-pagination mt-4"></div>
                </div>
            </div>
        </div>
    <% else %>
        <div class="vd-gallery__grid">
            <% loop $GridImages %>
                <a href="$FitMax(2400,1600).URL"
                   class="glightbox vd-gallery__item $Up.TileClass($Pos)"
                   data-gallery="gallery-$Up.ID" data-description="$Title.ATT" data-reveal>
                    <img src="$FocusFill(900,900).URL" alt="$Title.ATT" loading="lazy">
                    <% if $IsLast && $Up.RemainingCount %>
                        <span class="vd-gallery__more">+$Up.RemainingCount</span>
                    <% end_if %>
                </a>
            <% end_loop %>
        </div>
        <% loop $HiddenImages %>
            <a href="$FitMax(2400,1600).URL" class="glightbox d-none" data-gallery="gallery-$Up.ID" data-description="$Title.ATT" aria-hidden="true" tabindex="-1"></a>
        <% end_loop %>
    <% end_if %>
</div>
