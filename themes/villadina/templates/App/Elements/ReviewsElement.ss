<div class="container vd-slider">
    <div class="vd-el__head vd-el__head--split">
        <div>
            <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h2 data-reveal>$TitleHTML</h2><% end_if %>
            <% if $Lead %><p class="vd-lead mt-3 mb-0">$Lead</p><% end_if %>
        </div>
        <div class="d-flex align-items-center gap-4">
            <% if $HasScore %>
                <div class="vd-rating">
                    <span class="vd-rating__score">$Score</span>
                    <% if $ScoreLabel %><span class="vd-rating__label">$ScoreLabel</span><% end_if %>
                </div>
            <% end_if %>
            <% if $SortedReviews.Count %>
                <div class="vd-slider__controls d-none d-md-flex">
                    <button class="vd-slider__prev" type="button" aria-label="<%t VillaDina.Previous 'Previous' %>">$SvgIcon('arrow-left')</button>
                    <button class="vd-slider__next" type="button" aria-label="<%t VillaDina.Next 'Next' %>">$SvgIcon('arrow-right')</button>
                </div>
            <% end_if %>
        </div>
    </div>

    <div class="swiper" data-swiper="reviews">
        <div class="swiper-wrapper">
            <% loop $SortedReviews %>
                <div class="swiper-slide h-auto">
                    <figure class="vd-review">
                        <div class="vd-review__stars" aria-label="$Rating / 5">
                            <% loop $Stars %>$SvgIcon('star')<% end_loop %>
                        </div>
                        <blockquote class="vd-review__quote">“{$Quote}”</blockquote>
                        <figcaption class="vd-review__author">
                            <div>
                                <strong>$Author</strong>
                                <% if $Origin %><br><span>$Origin</span><% end_if %>
                            </div>
                            <div class="text-end">
                                <% if $Source != 'direct' %><span class="vd-review__source">$SourceLabel</span><% end_if %>
                                <% if $StayDate %><br><span>$StayDate.Format('MMMM y')</span><% end_if %>
                            </div>
                        </figcaption>
                    </figure>
                </div>
            <% end_loop %>
        </div>
        <div class="swiper-pagination position-static mt-4 d-md-none"></div>
    </div>
</div>
