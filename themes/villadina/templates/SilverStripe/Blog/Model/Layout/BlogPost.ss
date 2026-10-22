<article class="vd-post">
    <header class="vd-post__header">
        <div class="container">
            <a href="$Parent.Link" class="vd-link-arrow mb-4">$Icon('arrow-left') $Parent.Title</a>
            <h1>$Title</h1>
            <div class="vd-post__meta">
                <time datetime="$PublishDate.Rfc3339">$PublishDate.Format('d. MMMM y')</time>
                <% if $Categories.Count %>
                    <span><% loop $Categories %><a href="$Link">$Title</a><% if not $IsLast %>, <% end_if %><% end_loop %></span>
                <% end_if %>
                <% if $MinutesToRead %>
                    <span><%t VillaDina.MinRead '{minutes} min read' minutes=$MinutesToRead %></span>
                <% end_if %>
            </div>
        </div>
    </header>

    <div class="container">
        <% if $FeaturedImage.exists %>
            <div class="vd-post__image vd-ratio-cover">
                <img src="$FeaturedImage.FocusFill(2000,1000).URL"
                     srcset="$FeaturedImage.FocusFill(1000,500).URL 1000w, $FeaturedImage.FocusFill(1600,800).URL 1600w, $FeaturedImage.FocusFill(2400,1200).URL 2400w"
                     sizes="(min-width: 1360px) 1300px, 100vw"
                     alt="$FeaturedImage.Title.ATT" fetchpriority="high">
            </div>
        <% end_if %>

        <div class="vd-prose vd-post__body">$Content</div>

        <footer class="vd-post__footer">
            <% if $Tags.Count %>
                <div class="vd-post__tags mb-4">
                    <% loop $Tags %><a href="$Link">#$Title</a><% end_loop %>
                </div>
            <% end_if %>
            <a href="$Parent.Link" class="vd-link-arrow">$Icon('arrow-left') <%t VillaDina.BackToBlog 'Back to all posts' %></a>
        </footer>
    </div>
</article>

<section class="vd-section">
    <div class="container">
        <% with $Parent %>
            <% if $getBlogPosts.Exclude('ID', $Top.ID).Limit(3) %>
                <div class="vd-el__head vd-el__head--split">
                    <h2 class="m-0"><%t VillaDina.MorePosts 'More stories' %></h2>
                    <a href="$Link" class="vd-link-arrow"><%t VillaDina.ShowAll 'Show all' %> $Icon('arrow-right')</a>
                </div>
                <div class="row gy-5">
                    <% loop $getBlogPosts.Exclude('ID', $Top.ID).Limit(3) %>
                        <div class="col-md-6 col-lg-4"><% include PostCard %></div>
                    <% end_loop %>
                </div>
            <% end_if %>
        <% end_with %>
    </div>
</section>
