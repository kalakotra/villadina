<% if $Level(2) %>
    <nav aria-label="<%t VillaDina.Breadcrumb 'Breadcrumb' %>">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="$BaseHref"><%t VillaDina.Home 'Home' %></a></li>
            <% loop $getBreadcrumbItems %>
                <% if $IsLast %>
                    <li class="breadcrumb-item active" aria-current="page">$MenuTitle</li>
                <% else %>
                    <li class="breadcrumb-item"><a href="$Link">$MenuTitle</a></li>
                <% end_if %>
            <% end_loop %>
        </ol>
    </nav>
<% end_if %>
