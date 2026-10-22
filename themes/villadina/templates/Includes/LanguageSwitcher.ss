<%-- Requires tractorcow/silverstripe-fluent. Renders nothing without it.
     $Link already falls back to the locale's home when the page isn't translated. --%>
<% if $Locales %>
    <ul class="vd-lang" aria-label="<%t VillaDina.Language 'Language' %>">
        <% loop $Locales %>
            <li>
                <a href="$Link.ATT"
                   class="<% if $LinkingMode == 'current' %>is-current<% end_if %>"
                   <% if $LinkingMode == 'current' %>aria-current="true"<% else_if $LinkingMode != 'invalid' %>rel="alternate"<% end_if %>
                   hreflang="$HrefLang" lang="$HrefLang" title="$Title.ATT">$URLSegment.UpperCase</a>
            </li>
        <% end_loop %>
    </ul>
<% end_if %>
