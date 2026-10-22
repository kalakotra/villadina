<div class="container vd-textimage">
    <div class="row align-items-center gy-5 gx-lg-5<% if $ImageLeft %> flex-lg-row-reverse<% end_if %>">
        <div class="col-lg-5<% if not $ImageLeft %> me-lg-auto<% else %> ms-lg-auto<% end_if %>" data-reveal>
            <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h2>$TitleHTML</h2><% end_if %>
            <div class="vd-prose mt-4">$Content</div>
            <% with $CTALink %>
                <% if $exists %>
                    <a href="$URL" class="vd-link-arrow mt-4"<% if $OpenInNew %> target="_blank" rel="noopener"<% end_if %>>$Title $SvgIcon('arrow-right')</a>
                <% end_if %>
            <% end_with %>
        </div>

        <% if $Image.exists %>
            <div class="col-lg-6">
                <div class="vd-textimage__wrap<% if $SecondaryImage.exists %> pb-5 pb-lg-0<% end_if %>" data-reveal style="--reveal-delay: 150ms">
                    <div class="vd-textimage__media vd-ratio-cover<% if $ImageFormat == 'landscape' %> vd-textimage__media--landscape<% end_if %>">
                        <% if $ImageFormat == 'landscape' %>
                            <img src="$Image.FocusFill(1200,900).URL"
                                 srcset="$Image.FocusFill(800,600).URL 800w, $Image.FocusFill(1200,900).URL 1200w, $Image.FocusFill(1600,1200).URL 1600w"
                                 sizes="(min-width: 992px) 50vw, 100vw" alt="$Image.Title.ATT" loading="lazy">
                        <% else %>
                            <img src="$Image.FocusFill(960,1200).URL"
                                 srcset="$Image.FocusFill(640,800).URL 640w, $Image.FocusFill(960,1200).URL 960w, $Image.FocusFill(1280,1600).URL 1280w"
                                 sizes="(min-width: 992px) 50vw, 100vw" alt="$Image.Title.ATT" loading="lazy">
                        <% end_if %>
                    </div>
                    <% if $SecondaryImage.exists %>
                        <div class="vd-textimage__media-secondary vd-ratio-cover">
                            <img src="$SecondaryImage.FocusFill(600,600).URL" alt="$SecondaryImage.Title.ATT" loading="lazy">
                        </div>
                    <% end_if %>
                </div>
            </div>
        <% end_if %>
    </div>
</div>
