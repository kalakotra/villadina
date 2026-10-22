<%-- Generic layout. Pages with Elemental blocks render the area; others render $Content. --%>
<% if not $HasHero %>
    <% include PageHeader %>
<% end_if %>

<% if $HasElements %>
    $ElementalArea
<% else %>
    <section class="vd-section pt-0">
        <div class="container">
            <div class="vd-prose">
                $Content
            </div>
            <% if $Form %>
                <div class="vd-form mt-5" style="max-width: 42rem;">$Form</div>
            <% end_if %>
        </div>
    </section>
<% end_if %>
