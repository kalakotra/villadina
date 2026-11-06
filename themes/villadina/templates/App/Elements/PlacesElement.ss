<div class="container">
    <div class="row vd-el__head">
        <div class="col-lg-5">
            <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h2 data-reveal>$TitleHTML</h2><% end_if %>
        </div>
        <% if $Content %>
            <div class="col-lg-6 offset-lg-1 vd-lead" data-reveal>$Content</div>
        <% end_if %>
    </div>

    <ol class="vd-places">
        <% loop $SortedPlaces %>
            <li class="vd-place<% if $Image.exists %> vd-place--media<% end_if %>" id="$Anchor" data-reveal>
                <% if $Image.exists %>
                    <div class="vd-place__media vd-ratio-cover">
                        <img src="$Image.FocusFill(720,540).URL"
                             srcset="$Image.FocusFill(480,360).URL 480w, $Image.FocusFill(720,540).URL 720w, $Image.FocusFill(1080,810).URL 1080w"
                             sizes="(min-width: 992px) 30vw, 100vw"
                             alt="$Title.ATT" loading="lazy">
                    </div>
                <% end_if %>
                <div class="vd-place__body">
                    <div class="vd-place__meta">
                        <span>$KindLabel</span>
                        <% if $Surface %><span>$Surface</span><% end_if %>
                        <% if $BlueFlag %><span class="vd-place__flag"><%t VillaDina.BlueFlag 'Blue Flag' %></span><% end_if %>
                    </div>
                    <h3 class="vd-place__title">
                        <% if $Article.exists %><a href="$Article.Link">$Title</a><% else %>$Title<% end_if %>
                    </h3>
                    <% if $Summary %><p class="vd-place__text">$Summary</p><% end_if %>
                    <% if $FeatureList.Count %>
                        <ul class="vd-place__tags">
                            <% loop $FeatureList %><li>$Title</li><% end_loop %>
                        </ul>
                    <% end_if %>
                    <div class="vd-place__foot">
                        <% if $DriveLabel %>
                            <span class="vd-place__distance" title="<%t VillaDina.ByCarFromVilla 'by car from Villa Dina' %>">
                                $SvgIcon('car')
                                <span><strong>$DriveLabel</strong> <small><%t VillaDina.FromVilla 'from the villa' %></small></span>
                            </span>
                        <% end_if %>
                        <span class="vd-place__links">
                            <% if $Article.exists %>
                                <a href="$Article.Link" class="vd-link-arrow"><%t VillaDina.ReadGuide 'Read the guide' %> $SvgIcon('arrow-right')</a>
                            <% end_if %>
                            <% if $HasGeo %>
                                <a href="$DirectionsFromVillaURL.ATT" class="vd-place__route" target="_blank" rel="noopener nofollow">$SvgIcon('pin') <%t VillaDina.Route 'Route' %></a>
                            <% end_if %>
                        </span>
                    </div>
                </div>
            </li>
        <% end_loop %>
    </ol>
</div>
$SchemaJSON
