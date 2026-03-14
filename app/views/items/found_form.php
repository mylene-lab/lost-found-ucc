<?php
$isEdit = isset($item);
$pageTitle = $isEdit ? 'Edit Found Item' : 'Log Found Item';
require_once __DIR__.'/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="?page=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="?page=found-items">Found Items</a></li>
            <li class="breadcrumb-item active"><?= $pageTitle ?></li>
        </ol>
    </nav>
</div>

<form method="POST" action="?page=found-items&action=<?= $isEdit?'update':'store' ?>" enctype="multipart/form-data">
    <?php if($isEdit): ?><input type="hidden" name="id" value="<?= $item['id'] ?>"><?php endif; ?>
    <div class="row g-3">
        <div class="col-md-8">
            <div class="form-card mb-3">
                <h6 class="fw-600 mb-3 border-bottom pb-2"><i class="fas fa-box-open me-2 text-primary"></i>Item Details</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required value="<?= e($item['item_name']??'') ?>" placeholder="e.g. iPhone 15 Pro, Black Wallet...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select Category</option>
                            <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($item['category_id']??'')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Brand / Make</label>
                        <input type="text" name="brand" class="form-control" value="<?= e($item['brand']??'') ?>" placeholder="e.g. Apple, Samsung...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" value="<?= e($item['color']??'') ?>" placeholder="e.g. Black, Silver...">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description <span class="text-muted small">(be as detailed as possible)</span></label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe the item in detail..." id="descField"><?= e($item['description']??'') ?></textarea>
                        <div class="mt-1 d-flex gap-2 flex-wrap">
                            <small class="text-muted">Quick template:</small>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:.75rem" onclick="insertTemplate()"><i class="fas fa-magic me-1"></i>Use Template</button>
                        </div>
                        <div class="form-text text-muted mt-1">Include: physical condition, distinguishing marks, serial numbers, contents (if bag/wallet), sentimental value indicators, and any unique identifiers.</div>
                    </div>
                </div>
            </div>

            <div class="form-card mb-3">
                <h6 class="fw-600 mb-3 border-bottom pb-2"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Found Information</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Found Date <span class="text-danger">*</span></label>
                        <input type="date" name="found_date" class="form-control" required value="<?= e($item['found_date']??date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Found Location</label>
                        <input type="text" name="found_location" class="form-control" value="<?= e($item['found_location']??'') ?>" placeholder="e.g. Lobby, Parking Lot B...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Storage Location</label>
                        <input type="text" name="storage_location" class="form-control" value="<?= e($item['storage_location']??'') ?>" placeholder="e.g. Cabinet A, Shelf 3...">
                    </div>
                    <?php if($isEdit): ?>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach(['unclaimed','matched','claimed','disposed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $item['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= e($item['notes']??'') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-card mb-3">
                <h6 class="fw-600 mb-3 border-bottom pb-2"><i class="fas fa-building me-2 text-info"></i>Branch</h6>
                <select name="branch_id" class="form-select" required>
                    <option value="">Select Branch</option>
                    <?php foreach($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($item['branch_id']??$_SESSION['branch_id'])==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-card mb-3">
                <h6 class="fw-600 mb-3 border-bottom pb-2"><i class="fas fa-camera me-2 text-success"></i>Photo</h6>
                <?php if($isEdit && $item['photo']): ?>
                <img src="<?= UPLOAD_URL.e($item['photo']) ?>" class="img-fluid rounded mb-2" style="max-height:150px;object-fit:cover;width:100%">
                <?php endif; ?>
                <input type="file" name="photo" class="form-control" accept="image/*" id="photoInput">
                <div class="mt-2" id="photoPreview"></div>
                <small class="text-muted">Max 5MB. JPG, PNG, GIF, WebP</small>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i><?= $isEdit?'Update Item':'Log Found Item' ?>
                </button>
                <a href="?page=found-items" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

<?php $extraJs = '<script>
document.getElementById("photoInput").addEventListener("change", function(){
    const prev = document.getElementById("photoPreview");
    if(this.files && this.files[0]){
        const reader = new FileReader();
        reader.onload = e => prev.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height:120px;width:100%;object-fit:cover">`;
        reader.readAsDataURL(this.files[0]);
    }
});
function insertTemplate() {
    const f = document.getElementById("descField");
    if (!f.value.trim()) {
        f.value = "Condition: [Good / Fair / Damaged]\nColor: [Describe color(s)]\nSize/Shape: [Describe size or shape]\nDistinguishing marks: [Scratches, stickers, engravings, etc.]\nContents (if applicable): [e.g. cards, cash, keys inside]\nSerial/Model No.: [If visible]\nOther notes: [Any other identifying details]";
        f.focus();
    }
}
</script>';
require_once __DIR__.'/../layouts/footer.php'; ?>
