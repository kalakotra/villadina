<!doctype html>
<html lang="$ContentLocale">
<head>
    <% base_tag %>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><% if $MetaTitle %>$MetaTitle<% else %>$Title<% end_if %> · $SiteConfig.Title</title>
    $MetaTags(false)
    <meta name="theme-color" content="#123a48">
    <meta property="og:site_name" content="$SiteConfig.Title.ATT">
    <meta property="og:title" content="<% if $MetaTitle %>$MetaTitle.ATT<% else %>$Title.ATT<% end_if %>">
    <% if $MetaDescription %><meta property="og:description" content="$MetaDescription.ATT"><% end_if %>
    <meta property="og:url" content="$AbsoluteLink.ATT">
    <meta property="og:type" content="website">
    <% if $HeaderImage.exists %>
        <meta property="og:image" content="$HeaderImage.FocusFill(1200,630).AbsoluteURL.ATT">
    <% end_if %>
    <link rel="icon" href="$resourceURL('themes/villadina/images/favicon.svg')" type="image/svg+xml">
    $ViteTags('js/main.js')
</head>
<body class="$BodyClasses" data-locale="$ContentLocale">
    <a class="vd-skip" href="#main"><%t VillaDina.SkipToContent 'Skip to content' %></a>

    <% include Header %>

    <main id="main" class="vd-main">
        $Layout
    </main>

    <% include Footer %>
    <% include BookingBar %>
</body>
</html>
