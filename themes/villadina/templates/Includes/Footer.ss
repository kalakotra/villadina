<footer class="vd-footer" role="contentinfo">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-5">
                <div class="vd-footer__brand mb-3">$SiteConfig.Title</div>
                <% if $SiteConfig.VillaSubtitle %><p class="mb-4">$SiteConfig.VillaSubtitle</p><% end_if %>
                <% if $SiteConfig.InquiryLink %>
                    <a href="$SiteConfig.InquiryLink" class="btn btn-light"><%t VillaDina.CheckAvailability 'Check availability' %></a>
                <% end_if %>
            </div>

            <div class="col-sm-6 col-lg-3 offset-lg-1">
                <div class="vd-footer__title"><%t VillaDina.Contact 'Contact' %></div>
                <ul class="vd-footer__list">
                    <% if $SiteConfig.Address %><li>$SiteConfig.Address</li><% end_if %>
                    <% if $SiteConfig.Phone %><li><a href="$SiteConfig.PhoneHref">$SiteConfig.Phone</a></li><% end_if %>
                    <% if $SiteConfig.Email %><li><a href="mailto:$SiteConfig.Email">$SiteConfig.Email</a></li><% end_if %>
                    <% if $SiteConfig.WhatsAppHref %><li><a href="$SiteConfig.WhatsAppHref" target="_blank" rel="noopener">WhatsApp</a></li><% end_if %>
                </ul>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="vd-footer__title"><%t VillaDina.Explore 'Explore' %></div>
                <ul class="vd-footer__list">
                    <% loop $Menu(1) %>
                        <li><a href="$Link">$MenuTitle</a></li>
                    <% end_loop %>
                </ul>
                <% if $SiteConfig.InstagramURL || $SiteConfig.FacebookURL || $SiteConfig.BookingComURL || $SiteConfig.AirbnbURL %>
                    <ul class="vd-footer__social mt-4">
                        <% if $SiteConfig.InstagramURL %><li><a href="$SiteConfig.InstagramURL" target="_blank" rel="noopener" aria-label="Instagram">$SvgIcon('instagram')</a></li><% end_if %>
                        <% if $SiteConfig.FacebookURL %><li><a href="$SiteConfig.FacebookURL" target="_blank" rel="noopener" aria-label="Facebook">$SvgIcon('facebook')</a></li><% end_if %>
                    </ul>
                <% end_if %>
            </div>
        </div>

        <div class="vd-footer__bottom">
            <span>&copy; $Now.Year $SiteConfig.Title</span>
            <ul>
                <% if $SiteConfig.ImprintPage.exists %><li><a href="$SiteConfig.ImprintPage.Link">$SiteConfig.ImprintPage.MenuTitle</a></li><% end_if %>
                <% if $SiteConfig.PrivacyPage.exists %><li><a href="$SiteConfig.PrivacyPage.Link">$SiteConfig.PrivacyPage.MenuTitle</a></li><% end_if %>
            </ul>
        </div>
    </div>
</footer>
