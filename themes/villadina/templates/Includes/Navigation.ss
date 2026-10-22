<ul class="vd-nav">
    <% loop $Menu(1) %>
        <% if $ShowSubmenu %>
            <li class="dropdown">
                <a href="$Link" class="vd-nav__link is-$LinkingMode dropdown-toggle"
                   data-bs-toggle="dropdown" aria-expanded="false" role="button">$MenuTitle</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="$Link">$MenuTitle</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <% loop $Children %>
                        <li><a class="dropdown-item<% if $isCurrent %> active<% end_if %>" href="$Link"<% if $isCurrent %> aria-current="page"<% end_if %>>$MenuTitle</a></li>
                    <% end_loop %>
                </ul>
            </li>
        <% else %>
            <li>
                <a href="$Link" class="vd-nav__link is-$LinkingMode"<% if $isCurrent %> aria-current="page"<% end_if %>>$MenuTitle</a>
            </li>
        <% end_if %>
    <% end_loop %>
</ul>
