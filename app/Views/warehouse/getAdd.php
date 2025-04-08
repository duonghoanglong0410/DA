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
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Thêm Kho</button>
                        <button class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>