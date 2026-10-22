<div class="container">
    <div class="row gy-5">
        <div class="col-lg-4">
            <% if $Eyebrow %><div class="vd-eyebrow">$Eyebrow</div><% end_if %>
            <% if $ShowTitle %><h2 data-reveal>$TitleHTML</h2><% end_if %>
            <% if $Content %><div class="vd-lead mt-3">$Content</div><% end_if %>
        </div>

        <div class="col-lg-7 offset-lg-1">
            <div class="vd-faq" x-data="faqAccordion">
                <% loop $SortedItems %>
                    <div class="vd-faq__item">
                        <h3 class="m-0">
                            <button type="button" class="vd-faq__q"
                                    id="faq-q-$ID" aria-controls="faq-a-$ID"
                                    x-on:click="toggle($ID)" x-bind:aria-expanded="isOpen($ID).toString()" aria-expanded="false">
                                <span>$Question</span>
                                <span class="vd-faq__icon" aria-hidden="true"></span>
                            </button>
                        </h3>
                        <div class="vd-faq__a" id="faq-a-$ID" role="region" aria-labelledby="faq-q-$ID"
                             x-show="isOpen($ID)" x-collapse.duration.300ms x-cloak>
                            $Answer
                        </div>
                    </div>
                <% end_loop %>
            </div>
        </div>
    </div>
</div>
$SchemaJSON
