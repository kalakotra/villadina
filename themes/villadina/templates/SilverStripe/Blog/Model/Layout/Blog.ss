<% include PageHeader %>

<section class="vd-section pt-0 vd-blog">
    <div class="container">
        <% if $Content %><div class="vd-lead mb-5">$Content</div><% end_if %>

        <% if $Categories.Count %>
            <nav class="vd-blog__filters" aria-label="<%t VillaDina.Categories 'Categories' %>">
                <a href="$Link" class="<% if not $CurrentCategory && not $CurrentTag %>is-current<% end_if %>"><%t VillaDina.All 'All' %></a>
                <% loop $Categories %>
                    <a href="$Link" class="<% if $Top.CurrentCategory.ID == $ID %>is-current<% end_if %>">$Title</a>
                <% end_loop %>
            </nav>
        <% end_if %>

        <% if $CurrentTag %>
            <p class="text-body-secondary mb-4">
                <%t VillaDina.TaggedWith 'Posts tagged with “{tag}”' tag=$CurrentTag.Title %> · <a href="$Link"><%t VillaDina.ShowAll 'Show all' %></a>
            </p>
        <% end_if %>

        <% if $PaginatedList.Exists %>
            <div class="row vd-blog__grid">
                <% loop $PaginatedList %>
                    <% if $IsFirst && $Top.PaginatedList.CurrentPage == 1 && not $Top.CurrentCategory %>
                        <div class="col-12 vd-blog__featured">
                            <% include PostCard %>
                        </div>
                    <% else %>
                        <div class="col-md-6 col-lg-4">
                            <% include PostCard %>
                        </div>
                    <% end_if %>
                <% end_loop %>
            </div>

            <% with $PaginatedList %>
                <% include Pagination %>
            <% end_with %>
        <% else %>
            <p class="text-body-secondary"><%t VillaDina.NoPosts 'No posts yet.' %></p>
        <% end_if %>
    </div>
</section>
