<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <h2 class="mb-4">Tổng quan tình hình</h2>
                
                <p><strong>Tồn quỹ:</strong> <span class="text-right">{!balance!} VNĐ</span></p>
                <p><strong>Tổng công nợ chưa trả:</strong> <span class="text-right">{!totalDebt!} VNĐ</span></p>

                <h3>Công nợ chưa trả theo nhóm mục đích:</h3>
                <ul class="list-group">
                    {debtByPurpose}
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{purpose_name}</span>
                        <span class="text-right">{!total_amount!} VNĐ</span>
                    </li>
                    {/debtByPurpose}
                </ul>
            </div>
        </div>
    </div>
</div>
