<header class="vd-header" role="banner">
    <div class="container-xxl">
        <div class="vd-header__inner">
            <a href="$BaseHref" class="vd-header__logo" aria-label="$SiteConfig.Title.ATT – <%t VillaDina.Home 'Home' %>">
                <% if $SiteConfig.Logo.exists %>
                    <img src="$SiteConfig.Logo.URL" alt="$SiteConfig.Title.ATT" height="44">
                <% else %>
                    <span>
                        $SiteConfig.Title
                        <% if $SiteConfig.VillaSubtitle %><small>$SiteConfig.VillaSubtitle</small><% end_if %>
                    </span>
                <% end_if %>
            </a>

            <nav class="d-none d-lg-block" aria-label="<%t VillaDina.MainNavigation 'Main navigation' %>">
                <% include Navigation %>
            </nav>

            <div class="vd-header__actions">
                <div class="d-none d-md-block">
                    <% include LanguageSwitcher %>
                </div>
                <% if $SiteConfig.InquiryLink %>
                    <a href="$SiteConfig.InquiryLink" class="btn btn-primary btn-book d-none d-sm-inline-flex">
                        <%t VillaDina.CheckAvailability 'Check availability' %>
                    </a>
                <% end_if %>
                <button class="vd-header__toggle d-lg-none" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#vd-menu" aria-controls="vd-menu"
                        aria-label="<%t VillaDina.OpenMenu 'Open menu' %>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </div>
</header>

<% include OffcanvasMenu %>
