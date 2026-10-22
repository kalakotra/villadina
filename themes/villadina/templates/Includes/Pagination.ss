<%-- Usage: <% with $PaginatedList %><% include Pagination %><% end_with %> --%>
<% if $MoreThanOnePage %>
    <nav class="mt-5" aria-label="<%t VillaDina.Pagination 'Pagination' %>">
        <ul class="pagination justify-content-center">
            <% if $NotFirstPage %>
                <li class="page-item"><a class="page-link" href="$PrevLink" aria-label="<%t VillaDina.Previous 'Previous' %>">$Icon('arrow-left')</a></li>
            <% end_if %>
            <% loop $PaginationSummary(4) %>
                <% if $CurrentBool %>
                    <li class="page-item active" aria-current="page"><span class="page-link">$PageNum</span></li>
                <% else_if $Link %>
                    <li class="page-item"><a class="page-link" href="$Link">$PageNum</a></li>
                <% else %>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <% end_if %>
            <% end_loop %>
            <% if $NotLastPage %>
                <li class="page-item"><a class="page-link" href="$NextLink" aria-label="<%t VillaDina.Next 'Next' %>">$Icon('arrow-right')</a></li>
            <% end_if %>
        </ul>
    </nav>
<% end_if %>
