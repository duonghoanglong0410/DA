<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <h2 class="mb-4">Thêm Kho Mới</h2>
                <form action="{site_url}warehouse/add" method="post" class="getDetail">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên kho:</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên kho">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ:</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Nhập địa chỉ kho">
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Thêm Kho</button>
                        <a class="btn btn-secondary" href="{site_url}user-permission/user-list">Trở lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>