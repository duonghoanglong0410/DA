<!-- Pagination and Per Page Selection -->
  <div class="d-flex justify-content-between align-items-center mt-4">
    <!-- Per Page Selection -->
    <div class="d-flex align-items-center">
      <span class="me-2">Hiển thị:&nbsp;</span>
      <div class="btn-group">
        {per_page_options}
        <a class="btn btn-outline-secondary {is_selected}active{/is_selected}" href="{url}">{value}</a>
        {/per_page_options}
      </div>
      &nbsp;<span>bản ghi</span>
    </div>
    
    <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination mb-0">
        {pagination_links}
        <li class="page-item {is_active}active{/is_active} {is_disabled}disabled{/is_disabled}">
          <a class="page-link" href="{url}">{display}</a>
        </li>
        {/pagination_links}
      </ul>
    </nav>
  </div>
