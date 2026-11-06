<!doctype html>
<html lang="$ContentLocale">
<head>
    <% base_tag %>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>$DocumentTitle</title>
    $MetaTags(false)
    <link rel="canonical" href="$AbsoluteLink.ATT">
    <meta name="theme-color" content="#123a48">
    <meta property="og:site_name" content="$SiteConfig.Title.ATT">
    <meta property="og:title" content="$DocumentTitle.ATT">
    <% if $MetaDescription %><meta property="og:description" content="$MetaDescription.ATT"><% end_if %>
    <meta property="og:url" content="$AbsoluteLink.ATT">
    <meta property="og:type" content="$OGType">
    <meta property="og:locale" content="$OGLocale.ATT">
    <% if $OGImageURL %>
        <meta property="og:image" content="$OGImageURL.ATT">
        <meta name="twitter:card" content="summary_large_image">
    <% end_if %>
    <% if $IsHome && $SiteConfig.Latitude %>
        <meta name="geo.region" content="HR-18">
        <meta name="geo.placename" content="Šumber, Labin, Istra">
        <meta name="geo.position" content="$SiteConfig.Latitude;$SiteConfig.Longitude">
        <meta name="ICBM" content="$SiteConfig.Latitude, $SiteConfig.Longitude">
    <% end_if %>
    $StructuredData
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
