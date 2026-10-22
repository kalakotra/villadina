<%-- Scope: BlogPost --%>
<article>
    <a href="$Link" class="vd-card">
        <div class="vd-card__media vd-ratio-cover">
            <% if $FeaturedImage.exists %>
                <img src="$FeaturedImage.FocusFill(900,675).URL"
                     srcset="$FeaturedImage.FocusFill(600,450).URL 600w, $FeaturedImage.FocusFill(900,675).URL 900w, $FeaturedImage.FocusFill(1400,1050).URL 1400w"
                     sizes="(min-width: 992px) 33vw, (min-width: 768px) 50vw, 100vw"
                     alt="$FeaturedImage.Title.ATT" loading="lazy">
            <% end_if %>
        </div>
        <div class="vd-card__meta">
            <time datetime="$PublishDate.Rfc3339">$PublishDate.Format('d. MMMM y')</time>
            <% if $Categories.Count %> · $Categories.First.Title<% end_if %>
        </div>
        <h2 class="vd-card__title">$Title</h2>
        <div class="vd-card__text">
            <% if $Summary %>$Summary.Summary(30)<% else %>$Excerpt(30)<% end_if %>
        </div>
    </a>
</article>
