<div class="container mt-3 mainmenu">
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 gy-3 gx-3">    
    {menus}    
    <div class="col">
      <div class="menu-item shadow-sm p-3">
        <div class="d-flex align-items-center">
          <!-- Icon bên trái -->
          <i class="fa-2x fs-2 text-primary me-3 {icon_class}"></i>
          <!-- Phần nội dung -->
          <div class="flex-grow-1">
            <h5 class="mb-0 text-primary">{title}</h5>
            <p class="text-end text-dark small mb-0">{desc}.</p>
          </div>
        </div>
      </div>
    </div>
    {menus}
  </div>
</div>
