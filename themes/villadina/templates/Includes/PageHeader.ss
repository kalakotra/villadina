<% if $HeaderImage.exists %>
    <header class="vd-page-header vd-page-header--image">
        <div class="vd-ratio-cover">
            <img src="$HeaderImage.FocusFill(2400,1100).URL"
                 srcset="$HeaderImage.FocusFill(1200,800).URL 1200w, $HeaderImage.FocusFill(1800,900).URL 1800w, $HeaderImage.FocusFill(2400,1100).URL 2400w"
                 sizes="100vw" alt="" fetchpriority="high">
        </div>
        <div class="container pb-5">
            <% include Breadcrumbs %>
            <h1>$Title</h1>
        </div>
    </header>
<% else %>
    <header class="vd-page-header">
        <div class="container">
            <% include Breadcrumbs %>
            <h1 data-reveal>$Title</h1>
        </div>
    </header>
<% end_if %>
