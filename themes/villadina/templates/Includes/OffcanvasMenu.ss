<div class="offcanvas offcanvas-end vd-offcanvas" tabindex="-1" id="vd-menu" aria-labelledby="vd-menu-title">
    <div class="offcanvas-header">
        <span class="h4 mb-0" id="vd-menu-title">$SiteConfig.Title</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="<%t VillaDina.Close 'Close' %>"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <ul class="vd-offcanvas__nav">
            <% loop $Menu(1) %>
                <li>
                    <a href="$Link" class="is-$LinkingMode"<% if $isCurrent %> aria-current="page"<% end_if %>>$MenuTitle</a>
                    <% if $ShowSubmenu %>
                        <ul>
                            <% loop $Children %>
                                <li><a href="$Link" class="is-$LinkingMode">$MenuTitle</a></li>
                            <% end_loop %>
                        </ul>
                    <% end_if %>
                </li>
            <% end_loop %>
        </ul>

        <div class="vd-offcanvas__footer">
            <% include LanguageSwitcher %>
            <% if $SiteConfig.InquiryLink %>
                <a href="$SiteConfig.InquiryLink" class="btn btn-primary w-100 my-3">
                    <%t VillaDina.CheckAvailability 'Check availability' %>
                </a>
            <% end_if %>
            <% if $SiteConfig.Phone %><div><a href="$SiteConfig.PhoneHref">$SiteConfig.Phone</a></div><% end_if %>
            <% if $SiteConfig.Email %><div><a href="mailto:$SiteConfig.Email">$SiteConfig.Email</a></div><% end_if %>
        </div>
    </div>
</div>
